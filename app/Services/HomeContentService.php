<?php
namespace App\Services;
use App\Models\HomeBannerModel;
class HomeContentService {
    public function banners(): array {
        $rows=(new HomeBannerModel())->where('is_active',1)->orderBy('sort_order','ASC')->findAll(100);
        $out=[];
        foreach($rows as $row) {
            $slot=(string)$row['slot'];
            if(!in_array($slot,['hero','women','men','promo'],true)||isset($out[$slot]))continue;
            $row['image_url']=empty($row['image_path'])?null:base_url(ltrim($row['image_path'],'/'));
            $out[$slot]=$row;
        }
        return $out;
    }
}