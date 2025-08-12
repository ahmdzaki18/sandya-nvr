<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login', ['filter'=>'csrf']);
$routes->post('login', 'Auth::doLogin', ['filter'=>'csrf']);
$routes->get('logout', 'Auth::logout');

$routes->get('/', 'Home::index', ['filter'=>'auth']); // dashboard nantinya

$routes->group('admin', ['filter' => 'role:admin'], static function($routes) {
    $routes->get('cameras',             'Admin\Cameras::index');
    $routes->get('cameras/create',      'Admin\Cameras::create');
    $routes->post('cameras',            'Admin\Cameras::store');
    $routes->get('cameras/(:num)/edit', 'Admin\Cameras::edit/$1');
    $routes->post('cameras/(:num)',     'Admin\Cameras::update/$1');
    $routes->post('cameras/(:num)/del', 'Admin\Cameras::delete/$1');
    $routes->post('cameras/(:num)/toggle','Admin\Cameras::toggle/$1');
});

// dashboard grid (home)
$routes->get('/', 'Dashboard::index'); // filter auth sudah global

// play per kamera
$routes->get('camera/(:num)', 'Stream::play/$1');

// (stub) admin/dashboards biar gak 404 dulu
$routes->group('admin', ['filters' => ['auth','role:admin']], static function($r){
    $r->get('dashboards', 'Admin\Dashboards::index'); // nanti kita isi
});
