<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginPageController extends ServerController
{    
    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        return $this->createTemplateResponse('login.html');
    }
}
