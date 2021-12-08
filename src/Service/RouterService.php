<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Service;

use League\Container\Container;
use League\Route\RouteGroup;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Pdsinterop\Solid\Controller\AddSlashToPathController;
use Pdsinterop\Solid\Controller\HelloWorldController;
use Pdsinterop\Solid\Controller\HttpToHttpsController;
use Pdsinterop\Solid\Controller\Profile\CardController;
use Pdsinterop\Solid\Controller\Profile\ProfileController;

class RouterService
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var Container */
    private $container;
    /** @var Router */
    private $router;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function __construct(Container $container, Router $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    final public function populate() : Router
    {
        $container = $this->container;
        $router = $this->router;

        /*/ Default output is HTML, routes should return a Response object /*/
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($container);
        $router->setStrategy($strategy);

        /*/ Redirect all HTTP requests to HTTPS, unless we are behind a proxy /*/
        if ( ! getenv('PROXY_MODE')) {
            $router->map('GET', '/{page:(?:.|/)*}', HttpToHttpsController::class)->setScheme('http');
        }

        /*/ Map routes and groups /*/
        $router->map('GET', '/', HelloWorldController::class);
        $router->group('/profile', $this->createProfileGroup());

        return $router;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private function createProfileGroup() : Callable
    {
        return static function (RouteGroup $group) {
            $group->map('GET', '/', AddSlashToPathController::class);
            $group->map('GET', '', ProfileController::class);
            $group->map('GET', '/card', CardController::class);
            $group->map('GET', '/card{extension}', CardController::class);
        };
    }
}
