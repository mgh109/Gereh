<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOtpChallenges extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'mobile' => ['type' => 'VARCHAR', 'constraint' => 16],
            'code_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'attempts' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'expires_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME'],
            'used_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['mobile', 'created_at']);
        $this->forge->addKey(['status', 'expires_at']);
        $this->forge->createTable('otp_challenges', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('otp_challenges', true);
    }
}
