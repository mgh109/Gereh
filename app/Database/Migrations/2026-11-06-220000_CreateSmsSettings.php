<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateSmsSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type'=>'TINYINT','unsigned'=>true],
            'provider' => ['type'=>'VARCHAR','constraint'=>30,'default'=>'melipayamak'],
            'enabled' => ['type'=>'TINYINT','unsigned'=>true,'default'=>0],
            'otp_body_id' => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->createTable('sms_settings',true,['ENGINE'=>'InnoDB']);
        $this->db->table('sms_settings')->insert([
            'id'=>1,'provider'=>'melipayamak','enabled'=>0,'otp_body_id'=>null,
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('sms_settings',true);
    }
}
