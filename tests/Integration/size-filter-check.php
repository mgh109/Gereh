<?php
ob_start();
// No application database or migrations: isolated SQLite memory only.
$_SERVER['CI_ENVIRONMENT']='development';
require dirname(__DIR__,2).'/vendor/codeigniter4/framework/system/util_bootstrap.php';
config('Database')->default=['DSN'=>'','database'=>':memory:','DBDriver'=>'SQLite3','DBPrefix'=>'','pConnect'=>false,'DBDebug'=>true,'charset'=>'utf8','DBCollat'=>'','swapPre'=>'','failover'=>[],'foreignKeys'=>true,'busyTimeout'=>1000,'dateFormat'=>['date'=>'Y-m-d','datetime'=>'Y-m-d H:i:s','time'=>'H:i:s']];
$db=\Config\Database::connect();
if($db->DBDriver!=='SQLite3'||$db->database!==':memory:')throw new RuntimeException('Memory database required.');
foreach([
 'products'=>'id INTEGER PRIMARY KEY,name TEXT,slug TEXT,price_toman INTEGER,status TEXT,brand_id INTEGER,category_id INTEGER,deleted_at TEXT',
 'product_variants'=>'id INTEGER PRIMARY KEY,product_id INTEGER,sku TEXT,color_name TEXT,size_label TEXT,is_active INTEGER',
 'inventory_items'=>'id INTEGER PRIMARY KEY,variant_id INTEGER,quantity INTEGER,low_stock_threshold INTEGER',
 'product_media'=>'id INTEGER PRIMARY KEY,product_id INTEGER,file_path TEXT,alt_text TEXT,is_primary INTEGER,sort_order INTEGER',
 'categories'=>'id INTEGER PRIMARY KEY,name TEXT,slug TEXT,status TEXT,sort_order INTEGER',
 'brands'=>'id INTEGER PRIMARY KEY,name TEXT,slug TEXT,is_active INTEGER',
] as $table=>$columns)$db->query('CREATE TABLE '.$table.' ('.$columns.')');
$db->table('products')->insertBatch([
 ['id'=>1,'name'=>'کتانی سبز','slug'=>'green','price_toman'=>100,'status'=>'active','brand_id'=>1,'category_id'=>1,'deleted_at'=>null],
 ['id'=>2,'name'=>'کتانی کرم','slug'=>'cream','price_toman'=>200,'status'=>'active','brand_id'=>1,'category_id'=>1,'deleted_at'=>null],
 ['id'=>3,'name'=>'بوت','slug'=>'boot','price_toman'=>300,'status'=>'draft','brand_id'=>1,'category_id'=>1,'deleted_at'=>null],
 ['id'=>4,'name'=>'حذف شده','slug'=>'deleted','price_toman'=>400,'status'=>'active','brand_id'=>1,'category_id'=>1,'deleted_at'=>'2026-01-01'],
]);
$db->table('brands')->insert(['id'=>1,'name'=>'GEREH','slug'=>'gereh','is_active'=>1]);
$db->table('categories')->insert(['id'=>1,'name'=>'مردانه','slug'=>'men','status'=>'active','sort_order'=>1]);
foreach([[1,1,'GR-40','۴۰',1,5],[2,1,'GR-40-B','40',1,2],[3,1,'GR-41','41',1,0],[4,2,'CR-40','٤٠',0,8],[5,2,'CR-42','42',1,4],[6,3,'BOOT','40',1,9],[7,4,'DEL','40',1,9],[8,2,'CR-HALF','۴۲٫۵',1,1]] as [$id,$product,$sku,$size,$active,$stock]){
 $db->table('product_variants')->insert(['id'=>$id,'product_id'=>$product,'sku'=>$sku,'color_name'=>'سبز','size_label'=>$size,'is_active'=>$active]);
 $db->table('inventory_items')->insert(['id'=>$id,'variant_id'=>$id,'quantity'=>$stock,'low_stock_threshold'=>2]);
}
$check=static function($condition,$message){if(!$condition)throw new RuntimeException($message);echo 'PASS '.$message.PHP_EOL;};
$catalog=new \App\Services\CatalogReadService($db);
foreach(['40','۴۰','٤٠'] as $size){$r=$catalog->products(['size'=>$size]);$check($r['meta']['total']===1&&(int)$r['data'][0]['id']===1,'normalized exact size '.$size.'; no duplicate products');}
$check($catalog->products(['size'=>'41'])['meta']['total']===0,'Public excludes out of stock');
$check($catalog->products(['size'=>'42.5'])['meta']['total']===1,'Fractional Persian size');
$check($catalog->sizes()===['40','42','42.5'],'Available normalized size suggestions');
$check($catalog->products(['size'=>'40','q'=>'GR-40','category'=>'men','brand'=>'gereh','min_price'=>'90','max_price'=>'110'])['meta']['total']===1,'Combined SKU, size, category, brand and prices');
$check($catalog->products(['size'=>'40','q'=>'کرم'])['meta']['total']===0,'Search AND size');
$check($catalog->products(['q'=>'cream'])['meta']['total']===1,'Slug search');
$check($catalog->products(['size'=>"40' OR 1=1 --"])['meta']['total']===0,'Size SQL injection is escaped');
$check($catalog->products(['q'=>"' OR 1=1 --"])['meta']['total']===0,'Search SQL injection is escaped');
$check($catalog->products(['size'=>'40','per_page'=>1,'page'=>2])['meta']['total']===1 && $catalog->products(['size'=>'40','per_page'=>1,'page'=>2])['data']===[],'Filtered pagination totals');
$admin=$db->table('products p')->where('p.deleted_at',null)->whereIn('p.id',\App\Services\SizeFilter::productIds($db,'40',false))->get()->getResultArray();
$check(count($admin)===3,'Admin includes drafts and inactive variants, excludes deleted products');
$check(in_array('41',\App\Services\SizeFilter::options($db),true),'Admin includes unavailable size options');
config('App')->baseURL='http://localhost/';
$request=new \CodeIgniter\HTTP\IncomingRequest(config('App'),new \CodeIgniter\HTTP\SiteURI(config('App'),'admin/products'),null,new \CodeIgniter\HTTP\UserAgent());
\Config\Services::injectMock('request',$request);
foreach([[\App\Controllers\Admin\ProductsController::class,['size'=>'۴۰','q'=>'CR-40'],'کتانی کرم'],[\App\Controllers\Admin\InventoryController::class,['size'=>'۴۱','stock'=>'out'],'GR-41']] as [$class,$query,$expected]){
 $request->setGlobal('get',$query);$controller=new $class();$controller->initController($request,service('response'),service('logger'));$html=$controller->index();
 $check(str_contains($html,$expected)&&str_contains($html,'name="size"'),'Admin controller filters and renders '.$class);
 $check(!str_contains($html,'دریافت محصولات فعلاً ممکن نیست')&&!str_contains($html,'دریافت موجودی فعلاً ممکن نیست'),'No database error in admin filters');
}
foreach ([['size'=>['40']], ['size'=>str_repeat('4',61)]] as $invalid) {
 $request->setGlobal('get',$invalid);$controller=new \App\Controllers\Api\V1\CatalogController();$controller->initController($request,service('response'),service('logger'));
 $check($controller->products()->getStatusCode()===422,'API rejects invalid size input');
}
$request->setGlobal('get',['size'=>'۴۰','q'=>'GR-40']);
$controller=new \App\Controllers\Api\V1\CatalogController();$controller->initController($request,service('response'),service('logger'));
$response=$controller->products();$result=json_decode($response->getBody(),true);
$check($response->getStatusCode()===200 && $result['meta']['total']===1,'Public API combines SKU search and normalized size');
echo 'PASS all size/search integration checks'.PHP_EOL;
ob_end_flush();
