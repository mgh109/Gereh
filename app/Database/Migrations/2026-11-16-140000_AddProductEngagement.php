<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddProductEngagement extends Migration
{
    public function up()
    {
        $this->forge->addField(['product_id'=>['type'=>'BIGINT','unsigned'=>true],'day'=>['type'=>'DATE'],'views'=>['type'=>'INT','unsigned'=>true,'default'=>0]]);
        $this->forge->addKey(['product_id','day'],true);
        $this->forge->createTable('product_daily_views',true,$this->db->DBDriver==='MySQLi'?['ENGINE'=>'InnoDB']:[]);
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true], 'product_id'=>['type'=>'BIGINT','unsigned'=>true],
            'author_name'=>['type'=>'VARCHAR','constraint'=>100], 'body'=>['type'=>'TEXT'],'rating'=>['type'=>'TINYINT','unsigned'=>true,'null'=>true],
            'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'pending'],'created_at'=>['type'=>'DATETIME'],'moderated_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);$this->forge->addKey(['product_id','status','created_at']);$this->forge->addKey(['status','created_at']);
        $this->forge->createTable('product_comments',true,$this->db->DBDriver==='MySQLi'?['ENGINE'=>'InnoDB']:[]);
    }
    public function down(){ $this->forge->dropTable('product_comments',true);$this->forge->dropTable('product_daily_views',true); }
}
