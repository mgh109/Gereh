<?php

namespace App\Models;

use CodeIgniter\Model;

class HomeBannerModel extends Model
{
    protected $table = 'home_banners';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'slot','title','subtitle','image_path','link_path',
        'show_text','is_active','sort_order',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
}
