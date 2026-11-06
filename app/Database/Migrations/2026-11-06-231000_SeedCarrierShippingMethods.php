<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class SeedCarrierShippingMethods extends Migration
{
    public function up()
    {
        $now=date('Y-m-d H:i:s');
        foreach([
            ['post','پست پیشتاز','تعرفه دستی؛ قبل از فعال‌سازی هزینه واقعی را بررسی کنید.',40],
            ['tipax','تیپاکس','تعرفه دستی؛ محاسبه آنلاین شرکت حمل هنوز متصل نیست.',50],
            ['courier','پیک شهری','فقط برای استان‌هایی که در پنل تعیین می‌کنید.',60],
        ] as [$code,$title,$description,$sort]) {
            if($this->db->table('shipping_methods')->where('code',$code)->countAllResults()>0)continue;
            $this->db->table('shipping_methods')->insert([
                'code'=>$code,'title'=>$title,'description'=>$description,
                'type'=>'flat','base_fee_toman'=>0,
                'free_over_toman'=>null,'province_filter'=>null,
                'estimated_days_min'=>null,'estimated_days_max'=>null,
                'is_active'=>0,'sort_order'=>$sort,
                'created_at'=>$now,'updated_at'=>$now,
            ]);
        }
    }

    public function down()
    {
        $this->db->table('shipping_methods')->whereIn('code',['post','tipax','courier'])->delete();
    }
}
