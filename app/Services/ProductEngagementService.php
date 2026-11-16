<?php
namespace App\Services;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;

final class ProductEngagementService
{
    public function __construct(private ?BaseConnection $db=null){$this->db??=Database::connect();}
    public function ready():bool{return $this->db->tableExists('product_comments')&&$this->db->tableExists('product_daily_views');}
    public function recordView(int $productId,string $agent):void
    {
        if(!$this->ready()||preg_match('/bot|spider|crawler|preview|slurp/i',$agent))return;
        $key='product_view_'.$productId;$now=time();
        if((int)session()->get($key)>$now-1800)return;
        $table=$this->db->prefixTable('product_daily_views');
        $sql=$this->db->DBDriver==='SQLite3' ? "INSERT INTO $table (product_id,day,views) VALUES (?,?,1) ON CONFLICT(product_id,day) DO UPDATE SET views=views+1" : "INSERT INTO $table (product_id,day,views) VALUES (?,?,1) ON DUPLICATE KEY UPDATE views=views+1";
        if($this->db->query($sql,[$productId,date('Y-m-d')]))session()->set($key,$now);
    }
    public function comments(int $productId):array
    {
        return $this->db->table('product_comments')->select('author_name,body,rating,created_at')->where('product_id',$productId)->where('status','approved')->orderBy('id','DESC')->limit(100)->get()->getResultArray();
    }
    public function submit(int $productId,string $name,string $body,?int $rating):void
    {
        $name=trim($name);$body=trim($body);
        if($name===''||mb_strlen($name)>100||mb_strlen($body)<3||mb_strlen($body)>2000||($rating!==null&&($rating<1||$rating>5)))throw new InvalidArgumentException('نام، متن دیدگاه یا امتیاز معتبر نیست.');
        if(!$this->db->table('products')->where('id',$productId)->where('status','active')->where('deleted_at',null)->countAllResults())throw new InvalidArgumentException('محصول منتشرشده پیدا نشد.');
        if(!$this->db->table('product_comments')->insert(['product_id'=>$productId,'author_name'=>$name,'body'=>$body,'rating'=>$rating,'status'=>'pending','created_at'=>date('Y-m-d H:i:s')]))throw new \RuntimeException('ثبت دیدگاه انجام نشد.');
    }
    public function moderate(int $id,string $status):void
    {
        if(!in_array($status,['approved','rejected','pending'],true))throw new InvalidArgumentException('وضعیت دیدگاه نامعتبر است.');
        if(!$this->db->table('product_comments')->where('id',$id)->countAllResults())throw new InvalidArgumentException('دیدگاه پیدا نشد.');
        $this->db->table('product_comments')->where('id',$id)->update(['status'=>$status,'moderated_at'=>date('Y-m-d H:i:s')]);
    }
    public function stats(int $productId):array
    {
        $views=$this->db->table('product_daily_views')->selectSum('views')->where('product_id',$productId)->get()->getRowArray();
        $today=$this->db->table('product_daily_views')->where('product_id',$productId)->where('day',date('Y-m-d'))->get()->getRowArray();
        $ratings=$this->db->table('product_comments')->select('AVG(rating) AS rating, COUNT(rating) AS rating_count',false)->where('product_id',$productId)->where('status','approved')->get()->getRowArray();
        return ['views'=>(int)($views['views']??0),'today_views'=>(int)($today['views']??0),'comments'=>(int)$this->db->table('product_comments')->where('product_id',$productId)->where('status','approved')->countAllResults(),'rating'=>$ratings['rating']!==null?round((float)$ratings['rating'],1):null,'rating_count'=>(int)$ratings['rating_count']];
    }
}
