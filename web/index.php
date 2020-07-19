<?php declare(strict_types=1);

namespace Pdsinterop\Solid;

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/*/ Create objects /*/
$container = new \League\Container\Container();
$emitter = new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter();
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$strategy = new \League\Route\Strategy\ApplicationStrategy();

$router = new \League\Route\Router();

/*/ Wire objects together /*/
$container->delegate(new \League\Container\ReflectionContainer);

$strategy->setContainer($container);

/*/ Default output is HTML, should return a Response object /*/
$router->setStrategy($strategy);

$router->map('GET', '/', function (ServerRequestInterface $request) : ResponseInterface {
    $response = new \Laminas\Diactoros\Response;
    $response->getBody()->write('<h1>Hello, World!</h1>');
    return $response;
});

$response = $router->dispatch($request);

// send the response to the browser
$emitter->emit($response);
exit;
