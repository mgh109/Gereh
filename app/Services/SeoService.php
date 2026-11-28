<?php
namespace App\Services;

final class SeoService
{
    public function page(string $path, array $query = [], array $input = []): array
    {
        $path=trim($path,'/');
        $page=max(1,(int)(is_scalar($query['page']??1)?($query['page']??1):1));
        $canonical=[];$noindex=false;
        if (in_array($path,['shop','blog'],true)) {
            foreach (['category','brand'] as $key) if ($path==='shop' && is_string($query[$key]??null) && preg_match('/^[a-z0-9-]+$/',$query[$key])) $canonical[$key]=$query[$key];
            if ($page>1) $canonical['page']=$page;
            foreach(['q','size','sort','min_price','max_price'] as $key) if (isset($query[$key]) && $query[$key]!=='') $noindex=true;
            if(isset($canonical['category'],$canonical['brand']))$noindex=true;
        }
        if (preg_match('#^(admin|api|cart|checkout|login|account|favorites|payment)(/|$)#i',$path)) $noindex=true;
        $title=$input['title']??'گره | فروشگاه کفش';
        if($page>1 && in_array($path,['shop','blog'],true))$title.=' | صفحه '.$page;
        return [
            'title'=>$title,
            'description'=>mb_substr(trim(preg_replace('/\s+/u',' ',strip_tags((string)($input['description']??'خرید کفش مردانه، زنانه و یونیسکس از فروشگاه گره؛ بررسی مشخصات، سایز و قیمت مدل‌های مختلف.')))),0,320),
            'canonical'=>$input['canonical']??rtrim(config('App')->baseURL,'/').'/'.ltrim($path,'/').($canonical?'?'.http_build_query($canonical):''),
            'robots'=>($noindex||!empty($input['noindex']))?'noindex, follow':'index, follow, max-image-preview:large',
            'image'=>$input['image']??null,'type'=>$input['type']??'website',
        ];
    }

    public function product(array $product,array $variants,array $media): array
    {
        $url=site_url('product/'.rawurlencode($product['slug']));
        $schema=['@context'=>'https://schema.org','@type'=>'Product','@id'=>$url.'#product','url'=>$url,'name'=>$product['name'],
            'description'=>strip_tags((string)($product['short_description']?:$product['description']??''))];
        if(!empty($product['brand_name']))$schema['brand']=['@type'=>'Brand','name'=>$product['brand_name']];
        if(!empty($product['product_code']))$schema['sku']=$product['product_code'];
        $images=[];foreach($media as $image)if(($image['media_type']??'image')==='image'&&!empty($image['file_path']))$images[]=base_url(ltrim($image['file_path'],'/'));
        if($images)$schema['image']=$images;
        $lowestPrice = null;
        $lowestInStockPrice = null;
        foreach ($variants as $variant) {
            $price = (int) ($variant['price_toman'] ?? $product['price_toman']);
            if ($price <= 0) {
                continue;
            }
            $lowestPrice = min($lowestPrice ?? $price, $price);
            if ((int) ($variant['stock'] ?? 0) > 0) {
                $lowestInStockPrice = min($lowestInStockPrice ?? $price, $price);
            }
        }

        if ($lowestPrice !== null) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'url' => $url,
                'priceCurrency' => 'IRR',
                'price' => ($lowestInStockPrice ?? $lowestPrice) * 10,
                'availability' => $lowestInStockPrice !== null
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ];
            $condition = ['new' => 'NewCondition', 'used' => 'UsedCondition'];
            if (isset($condition[$product['item_condition'] ?? ''])) {
                $schema['offers']['itemCondition'] = 'https://schema.org/' . $condition[$product['item_condition']];
            }
        }
        if(!empty($product['engagement']['rating_count']))$schema['aggregateRating']=['@type'=>'AggregateRating','ratingValue'=>$product['engagement']['rating'],'ratingCount'=>$product['engagement']['rating_count']];
        return $schema;
    }

    public function breadcrumb(array $items): array
    {
        $list=[];foreach($items as $name=>$url)$list[]=['@type'=>'ListItem','position'=>count($list)+1,'name'=>$name,'item'=>$url];
        return ['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>$list];
    }
}
