<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateShippingMethods extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'code'=>['type'=>'VARCHAR','constraint'=>30],
            'title'=>['type'=>'VARCHAR','constraint'=>120],
            'description'=>['type'=>'VARCHAR','constraint'=>300,'null'=>true],
            'type'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'flat'],
            'base_fee_toman'=>['type'=>'BIGINT','unsigned'=>true,'default'=>0],
            'free_over_toman'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'province_filter'=>['type'=>'VARCHAR','constraint'=>250,'null'=>true],
            'estimated_days_min'=>['type'=>'TINYINT','unsigned'=>true,'null'=>true],
            'estimated_days_max'=>['type'=>'TINYINT','unsigned'=>true,'null'=>true],
            'is_active'=>['type'=>'TINYINT','unsigned'=>true,'default'=>0],
            'sort_order'=>['type'=>'INT','unsigned'=>true,'default'=>0],
            'created_at'=>['type'=>'DATETIME','null'=>true],
            'updated_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('shipping_methods',true,['ENGINE'=>'InnoDB']);
        $now=date('Y-m-d H:i:s');
        foreach([
            ['standard','ارسال استاندارد','هزینه ثابت؛ مطابق تعرفه تعریف‌شده فروشگاه','flat',25000,null,null,3,7,1,10],
            ['express','ارسال سریع','تعرفه و محدوده تحت پوشش از پنل مدیریت تنظیم می‌شود','flat',55000,null,null,1,3,0,20],
            ['pickup','تحویل حضوری','تنها پس از تأیید نشانی و ساعت تحویل فعال شود','pickup',0,null,null,1,2,0,30],
        ] as $m) {
            $this->db->table('shipping_methods')->insert(array_combine([
                'code','title','description','type','base_fee_toman','free_over_toman',
                'province_filter','estimated_days_min','estimated_days_max','is_active','sort_order'
            ],$m)+['created_at'=>$now,'updated_at'=>$now]);
        }
        $this->forge->addColumn('orders',[
            'shipping_title'=>['type'=>'VARCHAR','constraint'=>120,'null'=>true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders','shipping_title');
        $this->forge->dropTable('shipping_methods',true);
    }
}
