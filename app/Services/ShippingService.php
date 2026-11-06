<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use InvalidArgumentException;

final class ShippingService
{
    private BaseConnection $db;
    public function __construct(?BaseConnection $db=null){$this->db=$db??Database::connect();}

    /** Fees are authoritative only when calculated server-side. */
    public static function calculate(array $method,int $subtotal,string $province=''): int
    {
        if($subtotal<0)throw new InvalidArgumentException('جمع سبد خرید نامعتبر است.');
        if((int)($method['is_active']??0)!==1)
            throw new DomainException('روش ارسال انتخابی فعال نیست.');
        $allowed=trim((string)($method['province_filter']??''));
        if($allowed!==''){
            $list=array_filter(array_map('trim',explode(',',$allowed)));
            if($province===''||!in_array(trim($province),$list,true))
                throw new DomainException('این روش ارسال در استان انتخابی ارائه نمی‌شود.');
        }
        $type=(string)$method['type'];
        if(!in_array($type,['flat','pickup'],true))throw new DomainException('نوع روش ارسال نامعتبر است.');
        if($type==='pickup')return 0;
        $base=(int)$method['base_fee_toman'];
        $free=$method['free_over_toman'];
        if($base<0 || $base>1000000000)throw new DomainException('هزینه ارسال تنظیم نشده است.');
        if($free!==null && (int)$free>0 && $subtotal>=(int)$free)return 0;
        return $base;
    }

    public function all(): array
    {
        return $this->db->table('shipping_methods')->orderBy('sort_order','ASC')->orderBy('id','ASC')->get()->getResultArray();
    }

    public function quotes(int $subtotal,string $province=''): array
    {
        if($subtotal<0||$subtotal>intdiv(PHP_INT_MAX,10))throw new InvalidArgumentException('مبلغ سبد معتبر نیست.');
        $rows=$this->db->table('shipping_methods')->where('is_active',1)
            ->orderBy('sort_order','ASC')->get()->getResultArray();
        $result=[];
        foreach($rows as $method){
            try{$fee=self::calculate($method,$subtotal,$province);}
            catch(DomainException){continue;}
            $result[]=[
                'code'=>$method['code'],'title'=>$method['title'],'description'=>$method['description'],
                'type'=>$method['type'],'shipping_toman'=>$fee,
                'total_toman'=>$subtotal+$fee,
                'estimated_days_min'=>$method['estimated_days_min'],
                'estimated_days_max'=>$method['estimated_days_max'],
                'free_over_toman'=>$method['free_over_toman'],
            ];
        }
        return $result;
    }

    public function quote(string $code,int $subtotal,string $province=''): array
    {
        if(!preg_match('/^[a-z][a-z0-9_-]{1,29}$/D',$code))
            throw new InvalidArgumentException('شناسه روش ارسال نامعتبر است.');
        $method=$this->db->table('shipping_methods')->where('code',$code)->get()->getRowArray();
        if($method===null)throw new DomainException('روش ارسال پیدا نشد.');
        return [
            'code'=>$method['code'],'title'=>$method['title'],
            'shipping_toman'=>self::calculate($method,$subtotal,$province),
        ];
    }

    public function create(string $code,array $fields): void
    {
        if(!preg_match('/^[a-z][a-z0-9_-]{1,29}$/D',$code)) {
            throw new InvalidArgumentException('شناسه روش ارسال باید انگلیسی و یکتا باشد.');
        }
        if($this->db->table('shipping_methods')->countAllResults()>=25) {
            throw new InvalidArgumentException('حداکثر ۲۵ روش ارسال قابل تعریف است.');
        }
        $this->db->transBegin();
        try {
            if($this->db->table('shipping_methods')->where('code',$code)->countAllResults()>0) {
                throw new DomainException('این شناسه قبلاً ثبت شده است.');
            }
            $now=date('Y-m-d H:i:s');
            if(!$this->db->table('shipping_methods')->insert([
                'code'=>$code,'title'=>'در حال تنظیم','type'=>'flat',
                'base_fee_toman'=>0,'is_active'=>0,'sort_order'=>100,
                'created_at'=>$now,'updated_at'=>$now,
            ]))throw new \RuntimeException('ساخت روش ارسال ناموفق بود.');
            $id=(int)$this->db->insertID();
            $this->save($id,$fields);
            if($this->db->transStatus()===false)throw new \RuntimeException('ذخیره روش ارسال ناموفق بود.');
            $this->db->transCommit();
        }catch(\Throwable $exception){
            $this->db->transRollback();
            throw $exception;
        }
    }

    public function save(int $id,array $fields): void
    {
        $allowed=['title','description','type','base_fee_toman','free_over_toman','province_filter',
            'estimated_days_min','estimated_days_max','is_active','sort_order'];
        foreach($fields as $key=>$value)if(!in_array($key,$allowed,true))
            throw new InvalidArgumentException('پارامتر تنظیمات ارسال مجاز نیست.');
        $method=$this->db->table('shipping_methods')->where('id',$id)->get()->getRowArray();
        if(!$method)throw new DomainException('روش ارسال پیدا نشد.');
        $next=array_merge($method,$fields);
        if(!in_array($next['type'],['flat','pickup'],true))throw new InvalidArgumentException('نوع ارسال نامعتبر است.');
        if(!is_string($next['title'])||trim($next['title'])===''||mb_strlen($next['title'])>120)
            throw new InvalidArgumentException('عنوان روش ارسال نامعتبر است.');
        foreach(['base_fee_toman'=>1000000000,'free_over_toman'=>100000000000,'estimated_days_min'=>90,
                 'estimated_days_max'=>90,'sort_order'=>10000] as $key=>$max){
            if($next[$key]===null && in_array($key,['free_over_toman','estimated_days_min','estimated_days_max'],true))continue;
            if(!ctype_digit((string)$next[$key])||(float)$next[$key]>$max)
                throw new InvalidArgumentException('مقدار عددی تنظیمات ارسال نامعتبر است: '.$key);
        }
        if($next['estimated_days_min']!==null&&$next['estimated_days_max']!==null&&
            (int)$next['estimated_days_min']>(int)$next['estimated_days_max'])
            throw new InvalidArgumentException('بازه تحویل نامعتبر است.');
        if(mb_strlen((string)$next['description'])>300||
            mb_strlen((string)$next['province_filter'])>250)
            throw new InvalidArgumentException('توضیح یا محدوده استان طولانی است.');
        if(!in_array((string)$next['is_active'],['0','1'],true))
            throw new InvalidArgumentException('وضعیت روش ارسال نامعتبر است.');
        $update=array_intersect_key($next,array_flip($allowed));
        $update['updated_at']=date('Y-m-d H:i:s');
        if(!$this->db->table('shipping_methods')->where('id',$id)->update($update))
            throw new \RuntimeException('تنظیمات ارسال ذخیره نشد.');
    }
}
