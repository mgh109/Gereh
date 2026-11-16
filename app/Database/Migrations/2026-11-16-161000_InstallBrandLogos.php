<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class InstallBrandLogos extends Migration {
 public function up(){
  $this->db->table('brands')->where('slug','brooks')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/brooks.svg']);
  $this->db->table('brands')->where('slug','salomon')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/salomon.svg']);
  $this->db->table('brands')->where('slug','rocket-dog')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/rocketdog.jpg']);
  $this->db->table('brands')->where('slug','saucony')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/saucony.svg']);
  $this->db->table('brands')->where('slug','nike')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/nike.svg']);
  $this->db->table('brands')->where('slug','hoka')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/hoka.svg']);
  $this->db->table('brands')->where('slug','mizuno')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/mizuno.png']);
  $this->db->table('brands')->where('slug','reebok')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/reebok.svg']);
  $this->db->table('brands')->where('slug','ryka')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/ryka.svg']);
  $this->db->table('brands')->where('slug','adidas')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/adidas.svg']);
  $this->db->table('brands')->where('slug','the-north-face')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/the-north-face.svg']);
  $this->db->table('brands')->where('slug','new-balance')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/new-balance.svg']);
  $this->db->table('brands')->where('slug','lowa')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/lowa.svg']);
  $this->db->table('brands')->where('slug','rbx')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/rbx.png']);
  $this->db->table('brands')->where('slug','puma')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/puma.svg']);
  $this->db->table('brands')->where('slug','hi-tec')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/hitec.png']);
  $this->db->table('brands')->where('slug','skechers')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/skechers.svg']);
  $this->db->table('brands')->where('slug','fila')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/fila.svg']);
  $this->db->table('brands')->where('slug','gereh')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/figma/b7853.png']);
  $this->db->table('brands')->where('slug','asics')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/asics.svg']);
  $this->db->table('brands')->where('slug','under-armour')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/underarmour.svg']);
  $this->db->table('brands')->where('slug','prince')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/prince.png']);
  $this->db->table('brands')->where('slug','altra')->groupStart()->where('logo_path',null)->orWhere('logo_path','')->groupEnd()->update(['logo_path'=>'assets/brands/altra.svg']);
 }
 public function down(){}
}
