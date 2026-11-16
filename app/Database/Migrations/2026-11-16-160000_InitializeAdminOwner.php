<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class InitializeAdminOwner extends Migration
{
    public function up()
    {
        if($this->db->table('admin_users')->where('role','super_admin')->where('status','active')->countAllResults())return;
        $first=$this->db->table('admin_users')->where('status','active')->orderBy('id','ASC')->get(1)->getRowArray();
        if($first)$this->db->table('admin_users')->where('id',$first['id'])->update(['role'=>'super_admin']);
    }
    public function down(){} // Keep the owner's access when rolling back presentation changes.
}
