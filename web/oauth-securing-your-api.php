<?php declare(strict_types=1);

// https://oauth2.thephpleague.com/resource-server/securing-your-api/

// Init our repositories
$accessTokenRepository = new \Pdsinterop\Solid\Authentication\AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

// Path to authorization server's public key
$publicKeyPath = 'file://path/to/public.key';

// Setup the authorization server
$server = new \League\OAuth2\Server\ResourceServer(
    $accessTokenRepository,
    $publicKeyPath
);


// Then add the middleware to your stack:

new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server);
