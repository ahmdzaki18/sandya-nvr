<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes = Services::routes();

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// ---------- AUTH ----------
$routes->get('login',  'AuthController::login',       ['as' => 'login']);
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout',      ['as' => 'logout']);

// ---------- DASHBOARD / ROOT (butuh auth) ----------
$routes->get('/',          'Dashboard::index', ['filter' => 'auth']);
$routes->get('dashboard',  'Dashboard::index', ['filter' => 'auth']);

// ---------- CAMERAS ----------
$routes->group('admin', ['filter' => 'auth'], static function($routes) {
    $routes->get('cameras',                'Admin\Cameras::index');
    $routes->get('cameras/create',         'Admin\Cameras::create');
    $routes->post('cameras',               'Admin\Cameras::store');
    $routes->get('cameras/(:num)/edit',    'Admin\Cameras::edit/$1');
    $routes->post('cameras/(:num)',        'Admin\Cameras::update/$1');
    $routes->post('cameras/(:num)/toggle', 'Admin\Cameras::toggle/$1'); // Toggle Rec
    $routes->post('cameras/(:num)/delete', 'Admin\Cameras::delete/$1');
});

// viewer single camera (HLS)
$routes->get('camera/(:num)', 'Stream::play/$1', ['filter' => 'auth']);
