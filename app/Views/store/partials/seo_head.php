<?php
$seoInput=['title'=>$title??'گره | فروشگاه کفش','description'=>$description??'خرید کفش مردانه، زنانه و یونیسکس از فروشگاه گره؛ بررسی مشخصات، سایز و قیمت مدل‌های مختلف.'];
foreach(['canonical','image','type','noindex'] as $key)if(isset($$key))$seoInput[$key]=$$key;
$basePath=trim((string)parse_url(config('App')->baseURL,PHP_URL_PATH),'/');
$seoPath=trim((string)request()->getUri()->getPath(),'/');
if($basePath!==''&&str_starts_with($seoPath,$basePath.'/'))$seoPath=substr($seoPath,strlen($basePath)+1);
$seoQuery=request()->getGet();$seo=(new \App\Services\SeoService())->page($seoPath,is_array($seoQuery)?$seoQuery:[],$seoInput);
$jsonFlags=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT;
?>
<title><?= esc($seo['title']) ?></title>
<meta name="description" content="<?= esc($seo['description'],'attr') ?>">
<meta name="robots" content="<?= esc($seo['robots'],'attr') ?>">
<link rel="canonical" href="<?= esc($seo['canonical'],'attr') ?>">
<meta property="og:locale" content="fa_IR"><meta property="og:site_name" content="گره">
<meta property="og:type" content="<?= esc($seo['type'],'attr') ?>">
<meta property="og:title" content="<?= esc($seo['title'],'attr') ?>">
<meta property="og:description" content="<?= esc($seo['description'],'attr') ?>">
<meta property="og:url" content="<?= esc($seo['canonical'],'attr') ?>">
<meta name="twitter:card" content="<?= $seo['image']?'summary_large_image':'summary' ?>">
<meta name="twitter:title" content="<?= esc($seo['title'],'attr') ?>">
<meta name="twitter:description" content="<?= esc($seo['description'],'attr') ?>">
<?php if($seo['image']): ?><meta property="og:image" content="<?= esc($seo['image'],'attr') ?>"><meta name="twitter:image" content="<?= esc($seo['image'],'attr') ?>"><?php endif; ?>
<?php if($seoPath===''): ?><script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@graph'=>[
['@type'=>'Organization','@id'=>site_url('/').'#organization','name'=>'گره','url'=>site_url('/'),'logo'=>base_url('assets/figma/b7853.png')],
['@type'=>'WebSite','@id'=>site_url('/').'#website','name'=>'گره','url'=>site_url('/'),'inLanguage'=>'fa-IR'],
]],$jsonFlags) ?></script><?php endif; ?>
