<?php

namespace Pdsinterop\Solid;

require __DIR__ . './../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$container = new \League\Container\Container;
$responseFactory = new \Laminas\Diactoros\ResponseFactory();
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$strategy = (new \League\Route\Strategy\ApplicationStrategy)->setContainer($container);

$router = (new \League\Route\Router)->setStrategy($strategy);

$router->map('GET', '/', function (ServerRequestInterface $request) : ResponseInterface {
    $response = new \Laminas\Diactoros\Response;
    $response->getBody()->write('<h1>Hello, World!</h1>');
    return $response;
});

$response = $router->dispatch($request);

// send the response to the browser
(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
exit;
