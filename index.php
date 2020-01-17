<?php

require __DIR__ . "/vendor/autoload.php";

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], (strlen(CONF_URL_BASE)));

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
	$_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$router = new League\Route\Router;

// map a route
$router->map('GET', '/', function (ServerRequestInterface $request) : ResponseInterface {
	$response = new Zend\Diactoros\Response;
	$response->getBody()->write('<h1>Hello, World!</h1>');
	return $response;
});

$response = $router->dispatch($request);

var_dump($response);die;

// send the response to the browser
(new Zend\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
