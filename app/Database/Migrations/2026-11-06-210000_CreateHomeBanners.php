<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHomeBanners extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'slot'=>['type'=>'VARCHAR','constraint'=>30],
            'title'=>['type'=>'VARCHAR','constraint'=>220],
            'subtitle'=>['type'=>'VARCHAR','constraint'=>500,'null'=>true],
            'image_path'=>['type'=>'VARCHAR','constraint'=>500,'null'=>true],
            'link_path'=>['type'=>'VARCHAR','constraint'=>240,'default'=>'/shop'],
            'show_text'=>['type'=>'TINYINT','unsigned'=>true,'default'=>1],
            'is_active'=>['type'=>'TINYINT','unsigned'=>true,'default'=>1],
            'sort_order'=>['type'=>'INT','unsigned'=>true,'default'=>0],
            'created_at'=>['type'=>'DATETIME','null'=>true],
            'updated_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addKey(['slot','is_active','sort_order']);
        $this->forge->createTable('home_banners',true,['ENGINE'=>'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('home_banners',true);
    }
}