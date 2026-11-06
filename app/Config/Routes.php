<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Storefront::home', ['as' => 'store.home']);
$routes->get('shop', 'Storefront::shop', ['as' => 'store.shop']);
$routes->get('size-guide', static fn () => view('store/size_guide'), ['as' => 'store.size-guide']);
$routes->get('product/(:segment)', 'Storefront::product/$1', ['as' => 'store.product']);
$routes->get('cart', 'CartController::index', ['as' => 'store.cart']);
$routes->post('cart/add', 'CartController::add', ['filter' => 'csrf']);
$routes->post('cart/update', 'CartController::update', ['filter' => 'csrf']);

$routes->group('admin', ['namespace' => 'App\\Controllers\\Admin'], static function (RouteCollection $routes) {
    $routes->get('login', 'AuthController::login', ['as' => 'admin.login']);
    $routes->post('login', 'AuthController::attempt', ['filter' => 'csrf']);
    $routes->post('logout', 'AuthController::logout', ['filter' => 'adminauth']);

    $routes->group('', ['filter' => 'adminauth'], static function (RouteCollection $routes) {
        $routes->get('/', 'DashboardController::index', ['as' => 'admin.dashboard']);
        $routes->get('products', 'ProductsController::index', ['as' => 'admin.products']);
        $routes->get('products/create', 'CatalogEditorController::edit');
        $routes->post('products/create', 'CatalogEditorController::save', ['filter' => 'csrf']);
        $routes->get('products/(:num)/edit', 'CatalogEditorController::edit/$1');
        $routes->post('products/(:num)/edit', 'CatalogEditorController::save/$1', ['filter' => 'csrf']);
        $routes->post('products/(:num)/variants', 'CatalogEditorController::addVariant/$1', ['filter' => 'csrf']);
        $routes->get('api/summary', 'DashboardController::summary', ['as' => 'admin.api.summary']);
    });
});
