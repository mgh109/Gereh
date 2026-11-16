<?php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;
use RuntimeException;

/** One transaction for product, inline brand, variants, stock audit and media. */
final class ProductEditorService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    public function save(?int $id, array $data, array $rows, string $newBrand = '', array $media = []): int
    {
        if (($data['status']??'')==='active' && (int)($data['price_toman']??0)<=0) throw new InvalidArgumentException('برای انتشار، قیمت فروش باید بیشتر از صفر باشد.');
        $db = $this->db;
        $db->transBegin();
        try {
            if ($id && !$db->query('SELECT id FROM products WHERE id = ? AND deleted_at IS NULL'.($db->DBDriver==='MySQLi'?' FOR UPDATE':''),[$id])->getRowArray()) {
                throw new InvalidArgumentException('محصول پیدا نشد.');
            }
            if ($newBrand !== '') {
                $brand = $db->table('brands')->where('name', $newBrand)->get()->getRowArray();
                $data['brand_id'] = $brand['id'] ?? (new CatalogService($db))->createBrand([
                    'name' => $newBrand, 'slug' => 'brand-'.bin2hex(random_bytes(6)), 'is_active' => 1,
                ]);
            }
            if ($data['slug'] === '') {
                $data['slug'] = 'shoe-'.bin2hex(random_bytes(8));
            }
            $slugQuery=$db->table('products')->where('slug',$data['slug']);
            if ($id) $slugQuery->where('id !=',$id);
            if ($slugQuery->countAllResults()>0) throw new InvalidArgumentException('این نشانی محصول قبلاً استفاده شده است؛ نشانی دیگری انتخاب کنید.');
            $catalog = new CatalogService($db);
            if ($id) $catalog->updateProduct($id, $data);
            else $id = $catalog->createProduct($data);

            $seen = [];
            foreach ($rows as $row) {
                if (!is_array($row)) throw new InvalidArgumentException('ساختار تنوع نامعتبر است.');
                $variantId = filter_var($row['id'] ?? 0, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]]);
                $size = trim((string)($row['size_label'] ?? ''));
                $color = trim((string)($row['color_name'] ?? ''));
                $sku = trim((string)($row['sku'] ?? ''));
                if ($variantId === false || mb_strlen($size)>60 || mb_strlen($color)>80 || mb_strlen($sku)>80) throw new InvalidArgumentException('اطلاعات سایز، رنگ یا SKU نامعتبر است.');
                $quantity = filter_var($row['quantity'] ?? 0, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>100000000]]);
                if (!$variantId && $size === '' && $color === '' && $sku === '' && $quantity === 0) continue;
                if ($quantity === false) throw new InvalidArgumentException('موجودی باید عدد صحیح صفر یا بیشتر باشد.');
                $variantModel = new \App\Models\ProductVariantModel($db);
                if ($variantId) {
                    if (isset($seen[$variantId])) throw new InvalidArgumentException('تنوع تکراری در فرم وجود دارد.');
                    $seen[$variantId] = true;
                    $existing = $db->query('SELECT id, product_id FROM product_variants WHERE id = ?'.($db->DBDriver==='MySQLi'?' FOR UPDATE':''), [$variantId])->getRowArray();
                    if (!$existing || (int)$existing['product_id'] !== $id) throw new InvalidArgumentException('تنوع متعلق به این محصول نیست.');
                }
                if ($sku === '') $sku = 'GR-'.$id.'-'.bin2hex(random_bytes(5));
                $duplicate = $db->table('product_variants')->where('sku',$sku);
                if ($variantId) $duplicate->where('id !=',$variantId);
                if ($duplicate->countAllResults()>0) throw new InvalidArgumentException('SKU تکراری است: '.$sku);
                $variantData=['sku'=>$sku,'size_label'=>$size ?: null,'color_name'=>$color ?: null];
                if ($variantId) {
                    if (!$variantModel->update($variantId,$variantData)) throw new RuntimeException('ویرایش تنوع انجام نشد.');
                } else {
                    $variantId = (int)$variantModel->insert($variantData+['product_id'=>$id,'is_active'=>1],true);
                    if (!$variantId) throw new RuntimeException('ثبت تنوع انجام نشد.');
                }
                $inventory = $db->query('SELECT id, quantity FROM inventory_items WHERE variant_id = ?'.($db->DBDriver==='MySQLi'?' FOR UPDATE':''),[$variantId])->getRowArray();
                $previous = (int)($inventory['quantity'] ?? 0);
                // A checkout may have changed stock while the admin was editing.
                if ($inventory && isset($row['original_quantity'])) {
                    $original = filter_var($row['original_quantity'],FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);
                    if ($original === false) throw new InvalidArgumentException('موجودی قبلی نامعتبر است.');
                    if ($quantity === $original) continue; // Stock was not edited; preserve concurrent sales.
                    if ($previous !== $original) throw new InvalidArgumentException('موجودی در این فاصله تغییر کرده است؛ صفحه را تازه کنید و دوباره موجودی را وارد کنید.');
                }
                $now = date('Y-m-d H:i:s');
                if ($inventory) $db->table('inventory_items')->where('id',$inventory['id'])->update(['quantity'=>$quantity,'updated_at'=>$now]);
                else $db->table('inventory_items')->insert(['variant_id'=>$variantId,'quantity'=>$quantity,'low_stock_threshold'=>2,'updated_at'=>$now]);
                if ($quantity !== $previous) $db->table('inventory_movements')->insert([
                    'variant_id'=>$variantId,'quantity_delta'=>$quantity-$previous,'movement_type'=>'correction',
                    'reference_type'=>'admin','reference_id'=>null,'note'=>'ذخیره فرم محصول','created_at'=>$now,
                ]);
            }
            $hasPrimary = $db->table('product_media')->where('product_id',$id)->where('is_primary',1)->countAllResults()>0;
            $last = $db->table('product_media')->selectMax('sort_order')->where('product_id',$id)->get()->getRowArray();
            $sort = (int)($last['sort_order'] ?? -1)+1;
            foreach ($media as $image) {
                if (!(new \App\Models\ProductMediaModel($db))->insert($image+['product_id'=>$id,'media_type'=>'image','sort_order'=>$sort++,'is_primary'=>$hasPrimary?0:1])) throw new RuntimeException('ثبت تصاویر انجام نشد.');
                $hasPrimary = true;
            }
            if ($db->transStatus() === false) throw new RuntimeException('ذخیره کامل محصول انجام نشد.');
            $db->transCommit();
            return $id;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
