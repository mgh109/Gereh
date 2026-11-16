<?php
ob_start();
// Isolated in-memory integration checks; never touches the application database.
$_SERVER['CI_ENVIRONMENT']='development';
require dirname(__DIR__,2).'/vendor/codeigniter4/framework/system/util_bootstrap.php';
config('Database')->default=['DSN'=>'','database'=>':memory:','DBDriver'=>'SQLite3','DBPrefix'=>'','pConnect'=>false,'DBDebug'=>true,'charset'=>'utf8','DBCollat'=>'','swapPre'=>'','failover'=>[],'foreignKeys'=>true,'busyTimeout'=>1000,'dateFormat'=>['date'=>'Y-m-d','datetime'=>'Y-m-d H:i:s','time'=>'H:i:s']];
$db=\Config\Database::connect();
if($db->DBDriver!=='SQLite3'||$db->database!==':memory:')throw new RuntimeException('Memory DB required');
foreach([
'products'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,slug TEXT UNIQUE,price_toman INTEGER,status TEXT,brand_id INTEGER,category_id INTEGER,description TEXT,short_description TEXT,product_code TEXT,item_condition TEXT,created_at TEXT,updated_at TEXT,deleted_at TEXT',
'brands'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,slug TEXT UNIQUE,is_active INTEGER,created_at TEXT,updated_at TEXT',
'categories'=>'id INTEGER PRIMARY KEY,name TEXT,slug TEXT,status TEXT,sort_order INTEGER,created_at TEXT,updated_at TEXT',
'product_variants'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER,sku TEXT UNIQUE,size_label TEXT,color_name TEXT,is_active INTEGER,created_at TEXT,updated_at TEXT',
'inventory_items'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,variant_id INTEGER UNIQUE,quantity INTEGER,low_stock_threshold INTEGER,updated_at TEXT',
'inventory_movements'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,variant_id INTEGER,quantity_delta INTEGER,movement_type TEXT,reference_type TEXT,reference_id INTEGER,note TEXT,created_at TEXT',
'product_media'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER,file_path TEXT,alt_text TEXT,media_type TEXT,sort_order INTEGER,is_primary INTEGER',
] as $table=>$columns)$db->query('CREATE TABLE '.$table.' ('.$columns.')');
$check=static function($condition,$message){if(!$condition)throw new RuntimeException($message);echo 'PASS '.$message.PHP_EOL;};
$base=['name'=>'Test shoe','slug'=>'','price_toman'=>100,'status'=>'draft','category_id'=>null,'brand_id'=>null,'product_code'=>'138','item_condition'=>'new'];
$editor=new \App\Services\ProductEditorService($db);
$id=$editor->save(null,$base,[['size_label'=>'41.5','color_name'=>'Black','quantity'=>5]],'ASICS',[
['file_path'=>'uploads/products/a.jpg','alt_text'=>'A'],['file_path'=>'uploads/products/b.jpg','alt_text'=>'B'],
]);
$product=$db->table('products')->where('id',$id)->get()->getRowArray();
$v=$db->table('product_variants')->where('product_id',$id)->get()->getRowArray();
$check($product['brand_id']&&$product['slug']&&$v['sku'],'Single save generates brand, URL and SKU');
$check($db->table('inventory_items')->where('variant_id',$v['id'])->get()->getRowArray()['quantity']===5,'Initial stock is stored');
$check($db->table('inventory_movements')->countAllResults()===1,'Initial stock is audited');
$check($db->table('product_media')->where('product_id',$id)->countAllResults()===2 && $db->table('product_media')->where('is_primary',1)->countAllResults()===1,'Gallery and one primary image saved');
$existing=['id'=>$v['id'],'sku'=>$v['sku'],'size_label'=>'42','color_name'=>'Black','quantity'=>5,'original_quantity'=>5];
$editor->save($id,$base+[],[$existing]);
$check($db->table('product_variants')->where('id',$v['id'])->get()->getRowArray()['size_label']==='42','Existing variation details can be edited');
$db->table('inventory_items')->where('variant_id',$v['id'])->update(['quantity'=>3]);
$editor->save($id,$base,[$existing]);
$check($db->table('inventory_items')->where('variant_id',$v['id'])->get()->getRowArray()['quantity']===3,'Unchanged stock does not restore stock after concurrent sale');
$expectFailure=static function(callable $call,string $message) use($check){$failed=false;try{$call();}catch(InvalidArgumentException $e){$failed=true;}$check($failed,$message);};
$before=$db->table('products')->countAllResults();
$expectFailure(fn()=>$editor->save(null,$base,[['sku'=>$v['sku'],'size_label'=>'40','quantity'=>1]],'Rollback brand'),'Duplicate SKU rejected');
$check($db->table('products')->countAllResults()===$before && $db->table('brands')->where('name','Rollback brand')->countAllResults()===0,'Product and inline brand roll back together');
$expectFailure(fn()=>$editor->save($id,array_replace($base,['name'=>'Should roll back']),[array_replace($existing,['quantity'=>4])]),'Stale stock edit rejected');
$check($db->table('products')->where('id',$id)->get()->getRowArray()['name']==='Test shoe','Product edits roll back after inventory conflict');
$other=$editor->save(null,$base,[]);
$expectFailure(fn()=>$editor->save($other,$base,[$existing]),'Cannot edit another product variant');
$expectFailure(fn()=>$editor->save(null,$base,[['size_label'=>'40','quantity'=>-1]]),'Negative stock rejected');
$check($db->table('products')->countAllResults()===$before+1,'No orphan products left after rejected saves');
$editor->save($id,$base,[array_replace($existing,['quantity'=>6,'original_quantity'=>3])]);
$check($db->table('inventory_items')->where('variant_id',$v['id'])->get()->getRowArray()['quantity']===6 && $db->table('inventory_movements')->countAllResults()===2,'Accepted stock correction is audited');
config('App')->baseURL='http://localhost/';
$request=new \CodeIgniter\HTTP\IncomingRequest(config('App'),new \CodeIgniter\HTTP\SiteURI(config('App'),'admin/products'),null,new \CodeIgniter\HTTP\UserAgent());
\Config\Services::injectMock('request',$request);
$controller=new \App\Controllers\Admin\CatalogEditorController();
$controller->initController($request,service('response'),service('logger'));
$html=$controller->edit((string)$id);
$check(str_contains($html,'id="product-editor"') && str_contains($html,'enctype="multipart/form-data"') && str_contains($html,'name="images[]"'),'Actual controller renders unified upload form');
$check(str_contains($html,'name="product_code"') && str_contains($html,'name="item_condition"'),'Optional code and condition fields render after schema update');
$html=$controller->edit();
$check(str_contains($html,'name="images[]"') && str_contains($html,'name="variants[0][quantity]"'),'Images and variations available before first save');
echo 'PASS all product editor integration checks'.PHP_EOL;

ob_end_flush();
