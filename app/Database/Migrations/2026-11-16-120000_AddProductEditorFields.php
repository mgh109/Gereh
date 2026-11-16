<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductEditorFields extends Migration
{
    public function up()
    {
        foreach (['product_code'=>80,'item_condition'=>20] as $field=>$length) {
            if (!$this->db->fieldExists($field,'products')) $this->forge->addColumn('products',[
                $field=>['type'=>'VARCHAR','constraint'=>$length,'null'=>true],
            ]);
        }
    }
    public function down()
    {
        foreach (['product_code','item_condition'] as $field) {
            if ($this->db->fieldExists($field,'products')) $this->forge->dropColumn('products',$field);
        }
    }
}
