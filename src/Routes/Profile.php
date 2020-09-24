<?php

namespace Pdsinterop\Solid\Routes;

use League\Route\RouteGroup;
use Pdsinterop\Solid\Controller\AddSlashToPathController;
use Pdsinterop\Solid\Controller\Profile\CardController;
use Pdsinterop\Solid\Controller\Profile\ProfileController;

class Profile implements RoutesInterface
{
    final public function __invoke(RouteGroup $group) : void
    {
        $group->map('GET', '/', AddSlashToPathController::class);
        $group->map('GET', '', ProfileController::class);
        $group->map('GET', '/card', CardController::class);
        $group->map('GET', '/card{extension}', CardController::class);
    }
}
