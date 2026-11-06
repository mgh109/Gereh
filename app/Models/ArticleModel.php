<?php

namespace App\Models;

use CodeIgniter\Model;

class ArticleModel extends Model
{
    protected $table = 'articles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['title','slug','excerpt','content','cover_image','meta_title','meta_description','status','published_at'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $protectFields = true;
}