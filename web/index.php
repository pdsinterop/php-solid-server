<?php declare(strict_types=1);

namespace Pdsinterop\Solid;

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Flysystem\FilesystemInterface;
use League\Route\Http\Exception as HttpException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Pdsinterop\Solid\Controller\AddSlashToPathController;
use Pdsinterop\Solid\Controller\AuthorizeController;
use Pdsinterop\Solid\Controller\ApprovalController;
use Pdsinterop\Solid\Controller\CorsController;
use Pdsinterop\Solid\Controller\HandleApprovalController;
use Pdsinterop\Solid\Controller\HelloWorldController;
use Pdsinterop\Solid\Controller\HttpToHttpsController;
use Pdsinterop\Solid\Controller\JwksController;
use Pdsinterop\Solid\Controller\LoginController;
use Pdsinterop\Solid\Controller\LoginPageController;
use Pdsinterop\Solid\Controller\OpenidController;
use Pdsinterop\Solid\Controller\Profile\CardController;
use Pdsinterop\Solid\Controller\Profile\ProfileController;
use Pdsinterop\Solid\Controller\RegisterController;
use Pdsinterop\Solid\Controller\StorageController;
use Pdsinterop\Solid\Controller\TokenController;
use Pdsinterop\Solid\Resources\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/*/ Create objects /*/
$container = new Container();
$emitter = new SapiEmitter();
$request = ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$strategy = new ApplicationStrategy();

session_start();
$router = new Router();

/*/ Wire objects together /*/
$container->delegate(new ReflectionContainer());

$container->add(ServerRequestInterface::class, Request::class);
$container->add(ResponseInterface::class, Response::class);

$adapter = new \League\Flysystem\Adapter\Local(__DIR__ . '/../tests/fixtures');
$filesystem = new \League\Flysystem\Filesystem($adapter);
$graph = new \EasyRdf_Graph();
$plugin = new \Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf($graph);
$filesystem->addPlugin($plugin);

$container->share(FilesystemInterface::class, function () {
    // @FIXME: Filesystem root and the $adapter should be configurable.
    //         Implement this with `$filesystem = \MJRider\FlysystemFactory\create(getenv('STORAGE_ENDPOINT'));`
    $filesystemRoot = __DIR__ . '/../tests/fixtures';

    $adapter = new \League\Flysystem\Adapter\Local($filesystemRoot);

    $filesystem = new \League\Flysystem\Filesystem($adapter);
    $graph = new \EasyRdf_Graph();
    $plugin = new \Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf($graph);
    $filesystem->addPlugin($plugin);

    return $filesystem;
});

$container->share(\PHPTAL::class, function () {
    $template = new \PHPTAL();
    $template->setTemplateRepository(__DIR__.'/../src/Template');
    return $template;
});

$controllers = [
    AddSlashToPathController::class,
	ApprovalController::class,
    AuthorizeController::class,
    CardController::class,
    CorsController::class,
	HandleApprovalController::class,
    HelloWorldController::class,
    HttpToHttpsController::class,
    JwksController::class,
    LoginController::class,
    LoginPageController::class,
    OpenidController::class,
    ProfileController::class,
    RegisterController::class,
    StorageController::class,
	TokenController::class,
];

$traits = [
    'setFilesystem' => [FilesystemInterface::class],
    'setResponse' => [ResponseInterface::class],
    'setTemplate' => [\PHPTAL::class],
];

$traitMethods = array_keys($traits);

array_walk($controllers, function ($controller) use ($container, $traits, $traitMethods) {
    $definition = $container->add($controller);

    $methods = get_class_methods($controller);

    array_walk ($methods, function ($method) use ($definition, $traitMethods, $traits) {
        if (in_array($method, $traitMethods, true)) {
            $definition->addMethodCall($method, $traits[$method]);
        }
    });
});

$strategy->setContainer($container);

/*/ Default output is HTML, should return a Response object /*/
$router->setStrategy($strategy);

/*/ Make sure HTTPS is always used in production /*/
$scheme = 'http';
if (getenv('ENVIRONMENT') !== 'development') {
    $router->map('GET', '/{page:(?:.|/)*}', HttpToHttpsController::class)->setScheme($scheme);
    $scheme = 'https';
}

$router->map('GET', '/', HelloWorldController::class)->setScheme($scheme);

/*/ Create URI groups /*/
$router->map('GET', '/.well-known/openid-configuration', OpenidController::class)->setScheme($scheme);
$router->map('GET', '/jwks', JwksController::class)->setScheme($scheme);
$router->map('GET', '/login/', LoginPageController::class)->setScheme($scheme);
$router->map('POST', '/login/', LoginController::class)->setScheme($scheme);
$router->map('OPTIONS', '/{path}', CorsController::class)->setScheme($scheme);
$router->map('POST', '/register', RegisterController::class)->setScheme($scheme);
$router->map('GET', '/profile', AddSlashToPathController::class)->setScheme($scheme);
$router->map('GET', '/profile/', ProfileController::class)->setScheme($scheme);
$router->map('GET', '/profile/card', CardController::class)->setScheme($scheme);
$router->map('GET', '/profile/card{extension}', CardController::class)->setScheme($scheme);
$router->map('GET', '/authorize', AuthorizeController::class)->setScheme($scheme);
$router->map('GET', '/sharing/{clientId}/', ApprovalController::class)->setScheme($scheme);
$router->map('POST', '/sharing/{clientId}/', HandleApprovalController::class)->setScheme($scheme);
$router->map('GET', '/storage', AddSlashToPathController::class)->setScheme($scheme);
$router->map('GET', '/storage/', StorageController::class)->setScheme($scheme);
$router->map('GET', '/storage/{path}', StorageController::class)->setScheme($scheme);
$router->map('POST', '/token', TokenController::class)->setScheme($scheme);
$router->map('POST', '/token/', TokenController::class)->setScheme($scheme);

$path = $request->getUri()->getPath();
$target = $request->getMethod() . $request->getRequestTarget();
if ($path !== "/storage/" && strpos($path, '/storage/') === 0) {
    // Route starts with our data-source prefix
	$response = new \Laminas\Diactoros\Response();
	$server = new \Pdsinterop\Solid\Resources\Server($filesystem, $response);
    $response = $server->respondToRequest($request);
} else {
	try {
		$response = $router->dispatch($request);
	} catch (HttpException $exception) {
		$status = $exception->getStatusCode();

		$message = 'Yeah, that\'s an error.';
		if ($exception instanceof  NotFoundException) {
			$message = 'No such page.';
		}

		$html = "<h1>{$message}</h1><p>{$exception->getMessage()} ({$status})</p>";

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
}

// send the response to the browser
$emitter->emit($response);
exit;
