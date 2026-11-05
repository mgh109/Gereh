<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductMediaModel extends Model
{
    protected $table = 'product_media';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'product_id', 'file_path', 'alt_text', 'media_type', 'sort_order', 'is_primary',
    ];
    protected $useTimestamps = false;
}