<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductImageBadge extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('image_badge', 'products')) {
            $this->forge->addColumn('products', [
                'image_badge' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('image_badge', 'products')) {
            $this->forge->dropColumn('products', 'image_badge');
        }
    }
}
