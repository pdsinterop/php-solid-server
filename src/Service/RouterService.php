<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Service;

use League\Container\Container;
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

        /*/ Make sure HTTPS is always used in production /*/
        $scheme = 'http';
        if (getenv('ENVIRONMENT') !== 'development') {
            $router->map('GET', '/{page:(?:.|/)*}', HttpToHttpsController::class)->setScheme($scheme);
            $scheme = 'https';
        }

        /*/ Map routes and groups /*/
        $router->map('GET', '/', HelloWorldController::class)->setScheme($scheme);
        $this->mapProfile($router, $scheme);

        return $router;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private function mapProfile(Router $router, string $scheme) : void
    {
        $router->map('GET', '/profile', AddSlashToPathController::class)->setScheme($scheme);
        $router->map('GET', '/profile/', ProfileController::class)->setScheme($scheme);
        $router->map('GET', '/profile/card', CardController::class)->setScheme($scheme);
        $router->map('GET', '/profile/card{extension}', CardController::class)->setScheme($scheme);
    }
}
