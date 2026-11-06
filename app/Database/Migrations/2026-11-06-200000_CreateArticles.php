<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArticles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'title' => ['type'=>'VARCHAR','constraint'=>240],
            'slug' => ['type'=>'VARCHAR','constraint'=>240],
            'excerpt' => ['type'=>'TEXT','null'=>true],
            'content' => ['type'=>'LONGTEXT'],
            'cover_image' => ['type'=>'VARCHAR','constraint'=>512,'null'=>true],
            'meta_title' => ['type'=>'VARCHAR','constraint'=>240,'null'=>true],
            'meta_description' => ['type'=>'VARCHAR','constraint'=>320,'null'=>true],
            'status' => ['type'=>'VARCHAR','constraint'=>20,'default'=>'draft'],
            'published_at' => ['type'=>'DATETIME','null'=>true],
            'created_at' => ['type'=>'DATETIME','null'=>true],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey(['status','published_at']);
        $this->forge->createTable('articles',true,['ENGINE'=>'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('articles',true);
    }
}