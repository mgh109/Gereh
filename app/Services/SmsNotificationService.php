<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Fixed event catalog and ordered provider placeholders.
 * No arbitrary/custom event interpolation is accepted.
 */
final class SmsNotificationService
{
    public const EVENTS=[
        'order_placed'=>['title'=>'ثبت سفارش','variables'=>['order_number']],
        'payment_success'=>['title'=>'پرداخت موفق','variables'=>['order_number','amount']],
        'payment_review'=>['title'=>'نیاز به بررسی پرداخت','variables'=>['order_number']],
        'order_processing'=>['title'=>'آماده‌سازی سفارش','variables'=>['order_number']],
        'order_shipped'=>['title'=>'ارسال مرسوله','variables'=>['order_number','tracking_code']],
        'order_delivered'=>['title'=>'تحویل مرسوله','variables'=>['order_number']],
        'order_cancelled'=>['title'=>'لغو سفارش','variables'=>['order_number']],
    ];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db=null)
    {
        $this->db=$db??Database::connect();
    }

    public function templates(): array
    {
        $rows=$this->db->table('sms_templates')->orderBy('id','ASC')->get()->getResultArray();
        return array_map(static function(array $row):array {
            $row['enabled']=(int)$row['enabled']===1;
            $row['variables']=explode(';',$row['variables']);
            return $row;
        },$rows);
    }

    public function saveTemplates(array $inputs): void
    {
        if (count($inputs)>count(self::EVENTS))throw new InvalidArgumentException('تعداد الگوهای ارسالی معتبر نیست.');
        $this->db->transBegin();
        try {
            foreach($inputs as $event=>$config) {
                if(!isset(self::EVENTS[$event]) || !is_array($config))
                    throw new InvalidArgumentException('رویداد ناشناخته است.');
                $body=$config['body_id']??null;
                $body=$body===''?null:$body;
                if($body!==null && (!ctype_digit((string)$body)||(int)$body<1||(int)$body>2147483647))
                    throw new InvalidArgumentException('شناسه الگو نامعتبر است.');
                $enabled=($config['enabled']??false)===true;
                if($enabled && $body===null)
                    throw new InvalidArgumentException('برای فعال‌سازی هر رویداد شناسه الگو لازم است.');
                if(!$this->db->table('sms_templates')->where('event_key',$event)->update([
                    'body_id'=>$body===null?null:(int)$body,
                    'enabled'=>$enabled?1:0,
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]))throw new RuntimeException('ثبت الگو ناموفق بود.');
            }
            if($this->db->transStatus()===false)throw new RuntimeException('تراکنش ذخیره الگو ناموفق بود.');
            $this->db->transCommit();
        }catch(Throwable $exception){
            $this->db->transRollback();
            throw $exception;
        }
    }

    public static function orderedText(string $event,array $values): string
    {
        if(!isset(self::EVENTS[$event]))throw new InvalidArgumentException('رویداد پیامکی ناشناخته است.');
        $parts=[];
        foreach(self::EVENTS[$event]['variables'] as $field) {
            if(!isset($values[$field]) || !is_scalar($values[$field]))
                throw new InvalidArgumentException('مقدار متغیر الگو موجود نیست.');
            $text=trim((string)$values[$field]);
            if($text==='' || mb_strlen($text)>100 || str_contains($text,';')
                || preg_match('/[\\r\\n]/',$text)===1)
                throw new InvalidArgumentException('مقدار متغیر الگو معتبر نیست.');
            $parts[]=$text;
        }
        return implode(';',$parts);
    }

    /**
     * Insert inside the SAME order/settlement transaction when possible.
     * SMS is never sent within a money/inventory transaction.
     * UNIQUE(order_id,event_key) prevents duplicate notifications.
     */
    public function queueForOrder(int $orderId,string $event,array $extra=[]): bool
    {
        if(!isset(self::EVENTS[$event])||$orderId<1)
            throw new InvalidArgumentException('رویداد یا سفارش معتبر نیست.');
        $template=$this->db->table('sms_templates')
            ->where('event_key',$event)->where('enabled',1)->get()->getRowArray();
        if($template===null || (int)$template['body_id']<1)return false;
        $settings=$this->db->table('sms_settings')->where('id',1)->get()->getRowArray();
        if(!$settings || (int)$settings['enabled']!==1 || $settings['provider']!=='melipayamak')return false;
        $order=$this->db->table('orders')->select('id,order_number,receiver_mobile,total_toman,tracking_code')
            ->where('id',$orderId)->get()->getRowArray();
        if(!$order)throw new RuntimeException('سفارش برای اعلان یافت نشد.');
        $fields=[
            'order_number'=>$order['order_number'],
            'amount'=>(string)$order['total_toman'],
            'tracking_code'=>$order['tracking_code']??'',
        ]+$extra;
        $text=self::orderedText($event,$fields);
        $existing=$this->db->table('sms_outbox')->where('order_id',$orderId)->where('event_key',$event)
            ->get()->getRowArray();
        if($existing!==null)return false;
        $now=date('Y-m-d H:i:s');
        return $this->db->table('sms_outbox')->insert([
            'order_id'=>$orderId,'event_key'=>$event,
            'mobile'=>$order['receiver_mobile'],'body_id'=>(int)$template['body_id'],
            'pattern_text'=>$text,'status'=>'pending','attempts'=>0,
            'created_at'=>$now,'updated_at'=>$now,
        ]);
    }

    public function dispatch(int $limit=20): array
    {
        $limit=max(1,min(100,$limit));
        $counts=['sent'=>0,'review_required'=>0];
        $settings=(new SmsSettingsService())->read();
        if((int)$settings['enabled']!==1 || $settings['provider']!=='melipayamak'
            || !(new SmsSettingsService())->credentialsConfigured())return $counts;
        $jobs=$this->db->table('sms_outbox')->select('id')->where('status','pending')
            ->orderBy('id','ASC')->limit($limit)->get()->getResultArray();
        foreach($jobs as $job) {
            $id=(int)$job['id'];
            // Atomic claim: if another worker took the row, skip it.
            $claimed=$this->db->table('sms_outbox')->where('id',$id)->where('status','pending')
                ->update(['status'=>'sending','attempts'=>1,'updated_at'=>date('Y-m-d H:i:s')]);
            if(!$claimed||$this->db->affectedRows()!==1)continue;
            $row=$this->db->table('sms_outbox')->where('id',$id)->get()->getRowArray();
            try{
                $receipt=(new MelipayamakClient())->sendPatternText(
                    $row['mobile'],$row['pattern_text'],(int)$row['body_id']);
                $this->db->table('sms_outbox')->where('id',$id)->where('status','sending')->update([
                    'status'=>'sent','provider_receipt'=>$receipt,
                    'sent_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'),
                ]);
                $counts['sent']++;
            }catch(Throwable $exception){
                // Ambiguous network failures must not be blindly retried.
                $this->db->table('sms_outbox')->where('id',$id)->where('status','sending')->update([
                    'status'=>'review_required','error_code'=>'provider_unconfirmed',
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]);
                log_message('warning','SMS outbox attempt needs review for row {id}: {type}',[
                    'id'=>$id,'type'=>get_class($exception),
                ]);
                $counts['review_required']++;
            }
        }
        return $counts;
    }

    public function recent(int $limit=50): array
    {
        return $this->db->table('sms_outbox')->select('id,order_id,event_key,mobile,status,attempts,provider_receipt,error_code,created_at,sent_at')
            ->orderBy('id','DESC')->limit(max(1,min(100,$limit)))->get()->getResultArray();
    }
}
