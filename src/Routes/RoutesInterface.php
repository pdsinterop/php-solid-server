<?php

namespace Pdsinterop\Solid\Routes;

use League\Route\RouteGroup;

interface RoutesInterface
{
    public function __invoke(RouteGroup $group) : void;
}
