<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Storefront::home', ['as' => 'store.home']);
$routes->get('shop', 'Storefront::shop', ['as' => 'store.shop']);
$routes->get('size-guide', static fn () => view('store/size_guide'), ['as' => 'store.size-guide']);
$routes->get('product/(:segment)', 'Storefront::product/$1', ['as' => 'store.product']);

$routes->group('admin', ['namespace' => 'App\\Controllers\\Admin'], static function (RouteCollection $routes) {
    $routes->get('login', 'AuthController::login', ['as' => 'admin.login']);
    $routes->post('login', 'AuthController::attempt', ['filter' => 'csrf']);
    $routes->post('logout', 'AuthController::logout', ['filter' => 'adminauth']);

    $routes->group('', ['filter' => 'adminauth'], static function (RouteCollection $routes) {
        $routes->get('/', 'DashboardController::index', ['as' => 'admin.dashboard']);
        $routes->get('products', 'ProductsController::index', ['as' => 'admin.products']);
        $routes->get('api/summary', 'DashboardController::summary', ['as' => 'admin.api.summary']);
    });
});
