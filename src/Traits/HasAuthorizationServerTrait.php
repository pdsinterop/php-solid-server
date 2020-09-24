<?php

namespace Pdsinterop\Solid\Traits;

use League\OAuth2\Server\AuthorizationServer;

trait HasAuthorizationServerTrait
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var AuthorizationServer $authorizationServer */
    private $authorizationServer;

    //////////////////////////// GETTERS AND SETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function getAuthorizationServer() : AuthorizationServer
    {
        return $this->authorizationServer;
    }

    public function setAuthorizationServer(AuthorizationServer $authorizationServer) : void
    {
        $this->authorizationServer = $authorizationServer;
    }
}
