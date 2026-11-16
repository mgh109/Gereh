<?php
namespace App\Services;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

final class SizeFilter
{
    public static function text(mixed $value, int $limit = 60): string
    {
        return is_string($value) ? mb_substr(trim($value), 0, $limit) : '';
    }

    public static function normalize(mixed $value): string
    {
        return strtr(self::text($value), array_combine(
            mb_str_split('۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩٫'),
            str_split('01234567890123456789.')
        ));
    }

    // Column names are internal constants. All user values remain builder bindings.
    public static function column(string $column): string
    {
        $expression = 'TRIM('.$column.')';
        foreach (array_combine(mb_str_split('۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩٫'), str_split('01234567890123456789.')) as $from=>$to) {
            $expression = "REPLACE($expression, '$from', '$to')";
        }
        return $expression;
    }

    public static function variants(BaseConnection $db, bool $purchasable): BaseBuilder
    {
        $query = $db->table('product_variants sv')->select('sv.product_id');
        if ($purchasable) {
            $query->join('inventory_items si', 'si.variant_id=sv.id')->where('sv.is_active', 1)->where('si.quantity >', 0);
        }
        return $query;
    }

    public static function productIds(BaseConnection $db, string $size, bool $purchasable): BaseBuilder
    {
        return self::variants($db, $purchasable)->where(self::column('sv.size_label'), self::normalize($size));
    }

    public static function options(BaseConnection $db, bool $purchasable = false): array
    {
        $query = $db->table('product_variants sv')->select('sv.size_label')->distinct()
            ->join('products sp', 'sp.id=sv.product_id')->where('sp.deleted_at', null)
            ->where('sv.size_label IS NOT NULL', null, false)->where('sv.size_label !=', '');
        if ($purchasable) {
            $query->join('inventory_items si', 'si.variant_id=sv.id')->where('sv.is_active', 1)
                ->where('sp.status', 'active')->where('si.quantity >', 0);
        }
        $sizes = array_values(array_unique(array_filter(array_map(
            static fn($row)=>self::normalize($row['size_label']), $query->get()->getResultArray()
        ), static fn($size)=>$size !== '')));
        natcasesort($sizes);
        return array_values($sizes);
    }
}
