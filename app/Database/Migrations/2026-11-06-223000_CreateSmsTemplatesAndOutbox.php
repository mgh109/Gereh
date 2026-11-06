<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateSmsTemplatesAndOutbox extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'event_key' => ['type'=>'VARCHAR','constraint'=>60],
            'title' => ['type'=>'VARCHAR','constraint'=>120],
            'body_id' => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'variables' => ['type'=>'VARCHAR','constraint'=>255],
            'enabled' => ['type'=>'TINYINT','unsigned'=>true,'default'=>0],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addUniqueKey('event_key');
        $this->forge->createTable('sms_templates',true,['ENGINE'=>'InnoDB']);

        foreach ([
            ['order_placed','ثبت سفارش','order_number'],
            ['payment_success','پرداخت موفق','order_number;amount'],
            ['payment_review','پرداخت نیازمند بررسی','order_number'],
            ['order_processing','آماده‌سازی سفارش','order_number'],
            ['order_shipped','ارسال سفارش','order_number;tracking_code'],
            ['order_delivered','تحویل سفارش','order_number'],
            ['order_cancelled','لغو سفارش','order_number'],
        ] as [$key,$title,$vars]) {
            $this->db->table('sms_templates')->insert([
                'event_key'=>$key,'title'=>$title,'variables'=>$vars,
                'body_id'=>null,'enabled'=>0,
            ]);
        }

        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'order_id'=>['type'=>'BIGINT','unsigned'=>true],
            'event_key'=>['type'=>'VARCHAR','constraint'=>60],
            'mobile'=>['type'=>'VARCHAR','constraint'=>16],
            'body_id'=>['type'=>'INT','unsigned'=>true],
            'pattern_text'=>['type'=>'VARCHAR','constraint'=>500],
            'status'=>['type'=>'VARCHAR','constraint'=>24,'default'=>'pending'],
            'attempts'=>['type'=>'TINYINT','unsigned'=>true,'default'=>0],
            'provider_receipt'=>['type'=>'VARCHAR','constraint'=>120,'null'=>true],
            'error_code'=>['type'=>'VARCHAR','constraint'=>60,'null'=>true],
            'created_at'=>['type'=>'DATETIME'],
            'updated_at'=>['type'=>'DATETIME','null'=>true],
            'sent_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addUniqueKey(['order_id','event_key']);
        $this->forge->addKey(['status','created_at']);
        $this->forge->addForeignKey('order_id','orders','id','CASCADE','CASCADE');
        $this->forge->createTable('sms_outbox',true,['ENGINE'=>'InnoDB']);

        $this->forge->addColumn('orders',[
            'tracking_code'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],
            'shipped_at'=>['type'=>'DATETIME','null'=>true],
            'delivered_at'=>['type'=>'DATETIME','null'=>true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders','tracking_code');
        $this->forge->dropColumn('orders','shipped_at');
        $this->forge->dropColumn('orders','delivered_at');
        $this->forge->dropTable('sms_outbox',true);
        $this->forge->dropTable('sms_templates',true);
    }
}
