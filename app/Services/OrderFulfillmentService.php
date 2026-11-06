<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class OrderFulfillmentService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db=null)
    {
        $this->db=$db??Database::connect();
    }

    /**
     * Fulfillment actions operate ONLY on paid orders.
     * Payment and cancellation/refund states are never modified here.
     */
    public function transition(int $orderId,string $action,?string $trackingCode=null): array
    {
        if($orderId<1||!in_array($action,['ship','deliver'],true))
            throw new InvalidArgumentException('عملیات سفارش نامعتبر است.');
        if($action==='ship' && (!$trackingCode||!preg_match('/^[a-zA-Z0-9_-]{4,100}$/D',$trackingCode)))
            throw new InvalidArgumentException('کد رهگیری مرسوله باید معتبر باشد.');

        $this->db->transBegin();
        try{
            $order=$this->db->query('SELECT id,order_status,payment_status FROM orders WHERE id=? FOR UPDATE',[$orderId])->getRowArray();
            if($order===null)throw new DomainException('سفارش یافت نشد.');
            if($order['payment_status']!=='paid')
                throw new DomainException('سفارش پرداخت‌نشده قابل ارسال نیست.');

            $now=date('Y-m-d H:i:s');
            if($action==='ship') {
                if($order['order_status']!=='processing')
                    throw new DomainException('این سفارش در مرحله آماده‌سازی نیست.');
                $update=['order_status'=>'shipped','tracking_code'=>$trackingCode,'shipped_at'=>$now,'updated_at'=>$now];
                $event='order_shipped';
            }else{
                if($order['order_status']!=='shipped')
                    throw new DomainException('سفارش هنوز در وضعیت ارسال‌شده نیست.');
                $update=['order_status'=>'delivered','delivered_at'=>$now,'updated_at'=>$now];
                $event='order_delivered';
            }
            if(!$this->db->table('orders')->where('id',$orderId)->update($update))
                throw new RuntimeException('بروزرسانی وضعیت سفارش ناموفق بود.');
            (new SmsNotificationService($this->db))->queueForOrder($orderId,$event);
            if($this->db->transStatus()===false)
                throw new RuntimeException('ثبت تراکنش ارسال ناموفق بود.');
            $this->db->transCommit();
            return ['order_id'=>$orderId,'status'=>$update['order_status']];
        }catch(Throwable $exception){
            $this->db->transRollback();
            throw $exception;
        }
    }
}
