<?php declare(strict_types=1);

// https://oauth2.thephpleague.com/authorization-server/refresh-token-grant/

// Init our repositories
$clientRepository = new ClientRepository();
$accessTokenRepository = new AccessTokenRepository();
$scopeRepository = new ScopeRepository();
$refreshTokenRepository = new RefreshTokenRepository();

// Path to public and private keys
$privateKey = 'file://path/to/private.key';
//$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
$encryptionKey = 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'; // generate using base64_encode(random_bytes(32))

// Setup the authorization server
$server = new \League\OAuth2\Server\AuthorizationServer(
    $clientRepository,
    $accessTokenRepository,
    $scopeRepository,
    $privateKey,
    $encryptionKey
);

$grant = new \League\OAuth2\Server\Grant\RefreshTokenGrant($refreshTokenRepository);
$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // new refresh tokens will expire after 1 month

// Enable the refresh token grant on the server
$server->enableGrantType(
    $grant,
    new \DateInterval('PT1H') // new access tokens will expire after an hour
);

$app->post('/access_token', function (ServerRequestInterface $request, ResponseInterface $response) use ($app) {

  /* @var \League\OAuth2\Server\AuthorizationServer $server */
  $server = $app->getContainer()->get(AuthorizationServer::class);

  // Try to respond to the request
  try {
      return $server->respondToAccessTokenRequest($request, $response);

  } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
      return $exception->generateHttpResponse($response);

  } catch (\Exception $exception) {
      $body = new Stream('php://temp', 'r+');
      $body->write($exception->getMessage());
      return $response->withStatus(500)->withBody($body);
  }
});
