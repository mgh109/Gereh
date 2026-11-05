<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index', ['as' => 'store.home']);

$routes->group('admin', ['namespace' => 'App\\Controllers\\Admin'], static function (RouteCollection $routes) {
    $routes->get('login', 'AuthController::login', ['as' => 'admin.login']);
    $routes->post('login', 'AuthController::attempt', ['filter' => 'csrf']);
    $routes->post('logout', 'AuthController::logout', ['filter' => 'adminauth']);

    $routes->group('', ['filter' => 'adminauth'], static function (RouteCollection $routes) {
        $routes->get('/', 'DashboardController::index', ['as' => 'admin.dashboard']);
        $routes->get('api/summary', 'DashboardController::summary', ['as' => 'admin.api.summary']);
    });
});
