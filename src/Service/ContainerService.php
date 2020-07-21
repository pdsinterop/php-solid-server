<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Service;

use EasyRdf_Graph;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf;
use Pdsinterop\Solid\Controller\AddSlashToPathController;
use Pdsinterop\Solid\Controller\HelloWorldController;
use Pdsinterop\Solid\Controller\HttpToHttpsController;
use Pdsinterop\Solid\Controller\Profile\CardController;
use Pdsinterop\Solid\Controller\Profile\ProfileController;
use PHPTAL;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContainerService
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var Container */
    private $container;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function __construct(Container $container)
    {
        $this->container = $container;
    }

    final public function populate() : Container
    {
        $container = $this->container;

        /*/ Wire objects together /*/
        $container->delegate(new ReflectionContainer());

        $container->add(ServerRequestInterface::class, Request::class);
        $container->add(ResponseInterface::class, Response::class);

        $container->share(FilesystemInterface::class, $this->createFilesystem());

        $container->share(PHPTAL::class,  $this->createTemplate());

        $this->addControllers($container);

        return $container;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private function addControllers(Container $container) : void
    {
        $controllers = [
            AddSlashToPathController::class,
            CardController::class,
            HelloWorldController::class,
            HttpToHttpsController::class,
            ProfileController::class,
        ];

        $traits = [
            'setFilesystem' => [FilesystemInterface::class],
            'setResponse' => [ResponseInterface::class],
            'setTemplate' => [PHPTAL::class],
        ];

        $traitMethods = array_keys($traits);

        array_walk($controllers, static function ($controller) use ($container, $traits, $traitMethods) {
            $definition = $container->add($controller);

            $methods = get_class_methods($controller);

            array_walk($methods, static function ($method) use ($definition, $traitMethods, $traits) {
                if (in_array($method, $traitMethods, true)) {
                    $definition->addMethodCall($method, $traits[$method]);
                }
            });
        });
    }

    private function createFilesystem() : Callable
    {
        return static function () {
            // @FIXME: Filesystem root and the $adapter should be configurable.
            //         Implement this with `$filesystem = \MJRider\FlysystemFactory\create(getenv('STORAGE_ENDPOINT'));`
            $filesystemRoot = __DIR__ . '/../../tests/fixtures/';

            $adapter = new Local($filesystemRoot);

            $filesystem = new Filesystem($adapter);
            $graph = new EasyRdf_Graph();
            $plugin = new ReadRdf($graph);
            $filesystem->addPlugin($plugin);

            return $filesystem;
        };
    }

    private function createTemplate() : Callable
    {
        return static function () {
            $template = new PHPTAL();
            $template->setTemplateRepository(__DIR__ . '/../Template');

            return $template;
        };
    }
}
