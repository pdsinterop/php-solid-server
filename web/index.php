<?php declare(strict_types=1);

namespace Pdsinterop\Solid;

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Route\Http\Exception as HttpException;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/*/ Create objects /*/
$container = new Container();
$emitter = new SapiEmitter();
$request = ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$strategy = new ApplicationStrategy();

$router = new Router();

/*/ Wire objects together /*/
$container->delegate(new ReflectionContainer());

$strategy->setContainer($container);

/*/ Default output is HTML, should return a Response object /*/
$router->setStrategy($strategy);

$router->map('GET', '/', function (ServerRequestInterface $request) : ResponseInterface {
    $response = new Response();
    $response->getBody()->write('<h1>Hello, World!</h1>');
    return $response;
});

try {
    $response = $router->dispatch($request);
} catch (HttpException $exception) {
    $status = $exception->getStatusCode();

    $html = "<h1>Yeah, that's an error.</h1><p>{$exception->getMessage()} ({$status})</p>";

    if (getenv('ENVIRONMENT') === 'development') {
        $html .= "<pre>{$exception->getTraceAsString()}</pre>";
    }

    $response = new HtmlResponse($html, $status, $exception->getHeaders());
} catch (\Exception $exception) {
    $html = "<h1>Oh-no! The developers messed up!</h1><p>{$exception->getMessage()}</p>";

    if (getenv('ENVIRONMENT') === 'development') {
        $html .=
            "<p>{$exception->getFile()}:{$exception->getLine()}</p>" .
            "<pre>{$exception->getTraceAsString()}</pre>"
        ;
    }

    $response = new HtmlResponse($html, 500, []);
}

// send the response to the browser
$emitter->emit($response);
exit;
