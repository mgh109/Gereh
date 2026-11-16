<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddEnamadSettings extends Migration
{
    public function up()
    {
        foreach (['enamad_link','enamad_image_url'] as $field) {
            if (!$this->db->fieldExists($field, 'contact_settings')) {
                $this->forge->addColumn('contact_settings', [$field=>['type'=>'VARCHAR','constraint'=>2000,'default'=>'']]);
            }
        }
    }

    public function down()
    {
        foreach (['enamad_link','enamad_image_url'] as $field) {
            if ($this->db->fieldExists($field, 'contact_settings')) $this->forge->dropColumn('contact_settings', $field);
        }
    }
}
