<?php
namespace App\Services;

use Config\Database;

class AdminCustomersService
{
    public function paginate(array $filters=[]): array
    {
        $page=max(1,min(100000,(int)($filters['page']??1)));
        $db=Database::connect();
        $query=$db->table('customers')->select('id,mobile,first_name,last_name,status,created_at');
        $query->select('(SELECT COUNT(*) FROM orders o WHERE o.customer_id=customers.id) AS order_count',false)
            ->select("(SELECT COALESCE(SUM(o.total_toman),0) FROM orders o WHERE o.customer_id=customers.id AND o.payment_status='paid') AS total_spent",false);
        if(in_array($filters['status']??'', ['active','blocked'],true))$query->where('status',$filters['status']);
        $term=trim((string)($filters['q']??''));
        if($term!==''){
            $term=mb_substr($term,0,100);
            $query->groupStart()->like('mobile',$term)->orLike('first_name',$term)->orLike('last_name',$term)->groupEnd();
        }
        $total=$query->countAllResults(false);
        $rows=$query->orderBy('id','DESC')->limit(25,($page-1)*25)->get()->getResultArray();
        $average=$db->table('orders')->selectAvg('total_toman','average')->where('payment_status','paid')->get()->getRowArray();
        $returning=$db->query("SELECT COUNT(*) AS total FROM (SELECT customer_id FROM orders WHERE payment_status='paid' GROUP BY customer_id HAVING COUNT(*)>1) returning_customers")->getRowArray();
        return ['data'=>$rows,
            'stats'=>['total'=>$db->table('customers')->countAllResults(),'average_order'=>(int)($average['average']??0),'returning'=>(int)($returning['total']??0)],
            'meta'=>['page'=>$page,'per_page'=>25,'total'=>$total,'last_page'=>(int)ceil($total/25)]];
    }

    public function find(int $id): ?array
    {
        $db=Database::connect();
        $customer=$db->table('customers')->select('id,mobile,first_name,last_name,status,created_at')
            ->where('id',$id)->get()->getRowArray();
        if($customer===null)return null;
        $customer['orders']=$db->table('orders')->select('id,order_number,total_toman,order_status,payment_status')
            ->where('customer_id',$id)->orderBy('id','DESC')->limit(50)->get()->getResultArray();
        return $customer;
    }
}
