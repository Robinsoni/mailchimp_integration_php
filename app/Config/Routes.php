<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Pages;
use App\Controllers\News;
/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'MailChimpController::index');
/* $routes->get('news', [News::class, 'index']);
$routes->get('news/new', [News::class, 'new']);
$routes->post('news', [News::class, 'create']);
 
$routes->get('news/(:segment)', [News::class, 'show']);  
$routes->get('pages', [Pages::class, 'index']);
$routes->get('(:segment)', [Pages::class, 'view']); */
