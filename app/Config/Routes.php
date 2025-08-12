<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Dashboard default
$routes->get('/', 'Dashboard::index');

// Detail play (opsional, kalau kamu pakai)
$routes->get('camera/(:num)', 'Stream::play/$1');

// Group admin
$routes->group('admin', static function ($routes) {
    // Cameras CRUD
    $routes->get('cameras',               'Admin\Cameras::index');
    $routes->get('cameras/create',        'Admin\Cameras::create');
    $routes->post('cameras',              'Admin\Cameras::store');
    $routes->get('cameras/(:num)/edit',   'Admin\Cameras::edit/$1');
    $routes->post('cameras/(:num)',       'Admin\Cameras::update/$1');
    $routes->get('cameras/(:num)/delete', 'Admin\Cameras::delete/$1');

    // (opsional) Users, dsb.
});

$routes->post('admin/cameras/(:num)/toggle', 'Admin\Cameras::toggle/$1');
