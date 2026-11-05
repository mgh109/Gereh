<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'category_id', 'brand_id', 'name', 'slug', 'short_description',
        'description', 'price_toman', 'compare_at_price_toman', 'status',
        'is_featured', 'meta_title', 'meta_description', 'published_at',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
}