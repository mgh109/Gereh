<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Services\ContactSettingsService;

final class CreateContactSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'TINYINT','unsigned'=>true],
            'phone'=>['type'=>'VARCHAR','constraint'=>20],
            'email'=>['type'=>'VARCHAR','constraint'=>254],
            'postal_code'=>['type'=>'VARCHAR','constraint'=>10],
            'address'=>['type'=>'TEXT'],
            'updated_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('contact_settings', true, ['ENGINE'=>'InnoDB']);
        if (!$this->db->table('contact_settings')->where('id', 1)->countAllResults()) {
            $this->db->table('contact_settings')->insert(['id'=>1] + ContactSettingsService::DEFAULTS);
        }
    }

    public function down()
    {
        $this->forge->dropTable('contact_settings', true);
    }
}
