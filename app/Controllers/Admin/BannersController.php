<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HomeBannerModel;

class BannersController extends BaseController
{
    public function index()
    {
        return view('admin/banners/index',['banners'=>(new HomeBannerModel())->orderBy('sort_order','ASC')->findAll(100)]);
    }
}