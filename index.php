<?php
ob_start();

require __DIR__ . "/vendor/autoload.php";

/**
 * BOOTSTRAP
 */

use CoffeeCode\Router\Router;

/**
 * API ROUTES
 * index
 */
$route = new Router(url(), ":");
$route->namespace("Source\App");

//user
$route->group('/users');
$route->post('/', 'Users:create');
$route->get('/', 'Users:index');
$route->get('/{id}', 'Users:show');
$route->put('/{id}', 'Users:update');

//login
$route->group('/login');
$route->post('/', 'Users:login');

/**
 * ROUTE
 */
$route->dispatch();

/**
 * ERROR REDIRECT
 */
if ($route->error()) {
	header('Content-Type: application/json; charset=UTF-8');
	http_response_code(404);

	echo json_encode([
		"errors" => [
			"type " => "endpoint_not_found",
			"message" => "Não foi possível processar a requisição"
		]
	], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

ob_end_flush();
