<?php declare(strict_types=1);

namespace Pdsinterop\Solid;

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\Container\Container;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Pdsinterop\Solid\Controller\AddSlashToPathController;
use Pdsinterop\Solid\Controller\AvatarController;
use Pdsinterop\Solid\Controller\DataController;
use Pdsinterop\Solid\Controller\HelloWorldController;
use Pdsinterop\Solid\Controller\HttpToHttpsController;
use Pdsinterop\Solid\Controller\InboxController;
use Pdsinterop\Solid\Controller\NotesController;
use Pdsinterop\Solid\Controller\NotificationController;
use Pdsinterop\Solid\Controller\NotificationPingController;
use Pdsinterop\Solid\Controller\PostController;
use Pdsinterop\Solid\Controller\PreferenceController;
use Pdsinterop\Solid\Controller\SettingsController;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionObject;

/*/ Create objects /*/
$container = new Container();
$emitter = new SapiEmitter();
$request = ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$router = new Router();
$htmlStrategy = new ApplicationStrategy();
$jsonStrategy = new \League\Route\Strategy\JsonStrategy(new ResponseFactory(), JSON_PRETTY_PRINT);
$markdownStrategy = new MarkdownStrategy(new Response(), new GithubFlavoredMarkdownConverter());
$jsonStrategy->setContainer($container);
$htmlStrategy->setContainer($container);

/*/ Default output is HTML, should return a Response object /*/
$router->setStrategy($htmlStrategy);


// @CHECKME: Add middleware slash to URL?
//@TODO: Current setup is single-user, add {user} group and move routes there

/*/ Make sure HTTPS is always used in production /*/
$scheme = 'http';
if (getenv('ENVIRONMENT') !== 'development') {
    $router->map('GET', '/{page:(?:.|/)*}', HttpToHttpsController::class)->setScheme($scheme);
    $scheme = 'https';
}

$router->map('GET', '/help', function (ServerRequestInterface $request, array $args) use ($router) {
    $routes = [];

    $class = new ReflectionObject($router);
    $property = $class->getProperty('routes');
    $property->setAccessible(true);

    $routes = array_map(function ($route) {
        $route = '/'.trim($route->getPath(), '/');
        return str_replace('//', '/', $route);
    }, $property->getValue($router));

    asort($routes);

    $routes = array_unique($routes);

    $routes = array_map(function ($route) {
        if ($route !== '/help') {
            $route = sprintf('[%1$s](%1$s)', $route);
        }

        return '- ' .$route;
    }, $routes);

    $routes =join("\n", $routes);

    return <<<"TXT"
The following routes are available:

{$routes}
TXT;
})->setStrategy($markdownStrategy)->setScheme($scheme);

/*/ Create URI groups /*/

$router->group('/api', function (\League\Route\RouteGroup $route) {
    $route->map('GET', '/', AddSlashToPathController::class);
    $route->map('GET', '', HelloWorldController::class.'::getSubject');
})->setStrategy($jsonStrategy)->setScheme($scheme);

$router->map('GET', '/data', AddSlashToPathController::class);
$router->map('GET', '/inbox', AddSlashToPathController::class);
$router->map('GET', '/notes', AddSlashToPathController::class);
$router->map('GET', '/notifications', AddSlashToPathController::class);
$router->map('GET', '/posts', AddSlashToPathController::class);
$router->map('GET', '/profile', AddSlashToPathController::class);
$router->map('GET', '/settings', AddSlashToPathController::class);

$router->group('/data/', function (\League\Route\RouteGroup $route) {
    $route->map('GET', '/', DataController::class);
})->setScheme($scheme);

$router->group('/inbox/', function (\League\Route\RouteGroup $route) {
    $route->map('GET', '/', InboxController::class);
})->setScheme($scheme);

$router->group('/notes/', function (\League\Route\RouteGroup $route) {
    $route->map('GET', '/', NotesController::class);
    $route->map('GET', '/{slug:slug}', NotesController::class);
})->setScheme($scheme);

$router->group('/notification/', function (\League\Route\RouteGroup $route) {
    $route->map('GET', '/', NotificationController::class);
    $route->map('GET', '/ping', NotificationPingController::class);
})->setScheme($scheme);

$router->group('/posts/', function (\League\Route\RouteGroup $route) {
    $route->map('GET', '/', PostController::class);
    $route->map('GET', '/{id:int}', PostController::class);
})->setScheme($scheme);

$router->group('/profile/', function (\League\Route\RouteGroup $route) {
    $route->map('GET', '/avatar', AvatarController::class);
})->setScheme($scheme);

$router->group('/settings/', function (\League\Route\RouteGroup $route) {
    $route->map('GET', '/', SettingsController::class);
    $route->map('GET', '/preference', PreferenceController::class);
})->setScheme($scheme);


try {
    $response = $router->dispatch($request);
} catch (\Exception $exception) {
    $response = (new ExceptionResponse($exception))->createResponse();
}

// send the response to the browser
$emitter->emit($response);
exit;
