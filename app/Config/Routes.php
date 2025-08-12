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
// contoh proteksi role:
// $routes->get('admin/cameras', 'Admin\Cameras::index', ['filter'=>'auth:role:admin']);
