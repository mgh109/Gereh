<?php
ob_start();$_SERVER['CI_ENVIRONMENT']='development';require dirname(__DIR__,2).'/vendor/codeigniter4/framework/system/util_bootstrap.php';
config('Database')->default=['DSN'=>'','database'=>':memory:','DBDriver'=>'SQLite3','DBPrefix'=>'','pConnect'=>false,'DBDebug'=>true,'charset'=>'utf8','DBCollat'=>'','swapPre'=>'','failover'=>[],'foreignKeys'=>true,'busyTimeout'=>1000,'dateFormat'=>['date'=>'Y-m-d','datetime'=>'Y-m-d H:i:s','time'=>'H:i:s']];
$db=\Config\Database::connect();if($db->database!==':memory:')throw new RuntimeException('Memory DB required');
$db->query('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,email TEXT UNIQUE,password_hash TEXT,role TEXT,status TEXT,last_login_at TEXT,created_at TEXT,updated_at TEXT)');
$db->table('admin_users')->insert(['id'=>1,'name'=>'Owner','email'=>'owner@example.test','role'=>'super_admin','status'=>'active','password_hash'=>'old']);
$s=new \App\Services\AdminManagementService($db);$assert=static function($ok,$label){if(!$ok)throw new RuntimeException($label);echo "PASS $label\n";};
$data=['name'=>'Store manager','email'=>'manager@example.test','role'=>'admin','status'=>'active','password'=>'safe-test-password','password_confirmation'=>'safe-test-password'];
$id=$s->save(1,null,$data);$row=$db->table('admin_users')->where('id',$id)->get()->getRowArray();$assert(password_verify($data['password'],$row['password_hash']),'Created password is hashed');
$failed=false;try{$s->save($id,null,$data);}catch(DomainException $e){$failed=true;}$assert($failed,'Operational manager cannot create or elevate accounts');
$failed=false;try{$s->save(1,1,['name'=>'Owner','email'=>'owner@example.test','role'=>'admin','status'=>'active']);}catch(DomainException $e){$failed=true;}$assert($failed,'Owner cannot demote own account');
$failed=false;try{$s->save(1,null,$data);}catch(InvalidArgumentException $e){$failed=true;}$assert($failed,'Duplicate email rejected');
$s->save(1,$id,['name'=>'Edited','email'=>'manager@example.test','role'=>'admin','status'=>'inactive']);$edited=$db->table('admin_users')->where('id',$id)->get()->getRowArray();$assert($edited['password_hash']===$row['password_hash']&&$edited['status']==='inactive','Editing without password preserves hash and disables account');
$s->save(1,$id,['name'=>'Second owner','email'=>'manager@example.test','role'=>'super_admin','status'=>'active']);
$db->table('admin_users')->where('id',1)->update(['role'=>'admin']);$failed=false;try{$s->listing(1);}catch(DomainException $e){$failed=true;}$assert($failed,'Permission checked against current database role');
$assert(!array_key_exists('password_hash',$s->listing($id)[0]),'Admin listing never exposes password hashes');
ob_end_flush();
