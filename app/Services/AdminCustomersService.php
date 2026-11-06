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
        $term=trim((string)($filters['q']??''));
        if($term!==''){
            $term=mb_substr($term,0,100);
            $query->groupStart()->like('mobile',$term)->orLike('first_name',$term)->orLike('last_name',$term)->groupEnd();
        }
        $total=$query->countAllResults(false);
        return ['data'=>$query->orderBy('id','DESC')->limit(25,($page-1)*25)->get()->getResultArray(),
            'meta'=>['page'=>$page,'total'=>$total,'last_page'=>(int)ceil($total/25)]];
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