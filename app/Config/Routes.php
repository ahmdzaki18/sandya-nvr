<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();

// Default Settings
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// =====================
// Auth Routes
// =====================
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::attemptLogin');
$routes->get('/logout', 'Auth::logout');

// =====================
// Public Routes (tanpa filter auth)
// =====================
// kalau ada halaman public, taruh di sini

// =====================
// Admin & Dashboard Routes (dengan AuthFilter)
// =====================
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Dashboard
    $routes->get('/', 'Dashboard::index');
    $routes->get('/dashboard', 'Dashboard::index');

    // Cameras
    $routes->get('admin/cameras', 'Cameras::index');
    $routes->get('admin/cameras/add', 'Cameras::add');
    $routes->post('admin/cameras/store', 'Cameras::store');
    $routes->get('admin/cameras/edit/(:num)', 'Cameras::edit/$1');
    $routes->post('admin/cameras/update/(:num)', 'Cameras::update/$1');
    $routes->get('admin/cameras/delete/(:num)', 'Cameras::delete/$1');
    $routes->post('admin/cameras/(:num)/toggle', 'Cameras::toggle/$1');

    // Users
    $routes->get('admin/users', 'Users::index');
    $routes->get('admin/users/add', 'Users::add');
    $routes->post('admin/users/store', 'Users::store');
    $routes->get('admin/users/edit/(:num)', 'Users::edit/$1');
    $routes->post('admin/users/update/(:num)', 'Users::update/$1');
    $routes->get('admin/users/delete/(:num)', 'Users::delete/$1');

    // Settings
    $routes->get('admin/settings', 'Settings::index');
    $routes->post('admin/settings/save', 'Settings::save');
});
