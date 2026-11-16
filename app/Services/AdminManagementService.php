<?php
namespace App\Services;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use InvalidArgumentException;

final class AdminManagementService
{
    public function __construct(private ?BaseConnection $db=null){$this->db??=Database::connect();}
    public function authorize(int $actorId): void
    {
        $actor=$this->db->table('admin_users')->where('id',$actorId)->get()->getRowArray();
        if(!$actor||$actor['status']!=='active'||$actor['role']!=='super_admin')throw new DomainException('مدیریت مدیران فقط برای مدیر اصلی مجاز است.');
    }
    public function listing(int $actorId): array
    {
        $this->authorize($actorId);
        return $this->db->table('admin_users')->select('id,name,email,role,status,last_login_at')->orderBy('id','ASC')->get()->getResultArray();
    }
    public function save(int $actorId,?int $id,array $input): int
    {
        $this->authorize($actorId);
        $name=trim((string)($input['name']??''));$email=strtolower(trim((string)($input['email']??'')));
        $role=(string)($input['role']??'admin');$status=(string)($input['status']??'active');$password=(string)($input['password']??'');
        if($name===''||mb_strlen($name)>160||strlen($email)>190||!filter_var($email,FILTER_VALIDATE_EMAIL)||!in_array($role,['admin','super_admin'],true)||!in_array($status,['active','inactive'],true))throw new InvalidArgumentException('نام، ایمیل، نقش یا وضعیت معتبر نیست.');
        if((!$id||$password!=='')&&(strlen($password)<12||strlen($password)>72||$password!==(string)($input['password_confirmation']??'')))throw new InvalidArgumentException('رمز و تکرار آن یکسان و بین ۱۲ تا ۷۲ بایت باشند.');
        $this->db->transBegin();
        try {
            // Serialize owner changes so concurrent requests cannot remove the last owner.
            $sql='SELECT id FROM '.$this->db->prefixTable('admin_users')." WHERE role='super_admin' AND status='active' ORDER BY id";
            $owners=$this->db->query($sql.($this->db->DBDriver==='SQLite3'?'':' FOR UPDATE'))->getResultArray();
            $this->authorize($actorId);
            $current=$id?$this->db->table('admin_users')->where('id',$id)->get()->getRowArray():null;
            if($id&&!$current)throw new InvalidArgumentException('مدیر پیدا نشد.');
            if($id===$actorId&&($status!=='active'||$role!=='super_admin'))throw new DomainException('نمی‌توانی حساب خودت را غیرفعال کنی یا دسترسی مدیریت مدیران را از خودت بگیری.');
            if($current&&$current['role']==='super_admin'&&$current['status']==='active'&&($role!=='super_admin'||$status!=='active')&&count($owners)<=1)throw new DomainException('حداقل یک مدیر اصلی فعال باید باقی بماند.');
            $duplicate=$this->db->table('admin_users')->where('email',$email);
            if($id)$duplicate->where('id !=',$id);
            if($duplicate->countAllResults())throw new InvalidArgumentException('این ایمیل قبلاً ثبت شده است.');
            $data=['name'=>$name,'email'=>$email,'role'=>$role,'status'=>$status,'updated_at'=>date('Y-m-d H:i:s')];
            if($password!=='')$data['password_hash']=password_hash($password,PASSWORD_DEFAULT);
            if($id){$ok=$this->db->table('admin_users')->where('id',$id)->update($data);}else{$data['created_at']=$data['updated_at'];$ok=$this->db->table('admin_users')->insert($data);$id=(int)$this->db->insertID();}
            if(!$ok||$this->db->transStatus()===false)throw new \RuntimeException('ذخیره مدیر انجام نشد.');
            $this->db->transCommit();
            return $id;
        }catch(\Throwable $e){$this->db->transRollback();throw $e;}
    }
}
