<?php

namespace Pdsinterop\Solid\Routes;

use League\Route\RouteGroup;
use Pdsinterop\Solid\Controller\AddSlashToPathController;
use Pdsinterop\Solid\Controller\WellKnown as WellKnownController;

class WellKnown implements RoutesInterface
{
    final public function __invoke(RouteGroup $group) : void
    {
        $group->map('GET', '/', AddSlashToPathController::class);
        // @TODO: $group->map('GET', '', ShowAvailableRoutes::class);
        $group->map('GET', '/security.txt', WellKnownController::class);
        $group->map('GET', '/openid-configuration', WellKnownController::class);
    }
}
