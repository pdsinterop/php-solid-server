<?php

namespace Pdsinterop\Solid\Routes;

use League\Route\RouteGroup;
use Pdsinterop\Solid\Controller\AddSlashToPathController;
use Pdsinterop\Solid\Controller\Authentication\ClientCredentialsGrant;
use Pdsinterop\Solid\Controller\AuthorizationController;

class Authentication implements RoutesInterface
{
    final public function __invoke(RouteGroup $group) : void
    {
        $group->map('GET', '/', AddSlashToPathController::class);
        $group->map('GET', '', AuthorizationController::class);
        $group->map('GET', '/access_token', ClientCredentialsGrant::class);
    }
}
