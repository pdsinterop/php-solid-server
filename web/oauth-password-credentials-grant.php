<?php declare(strict_types=1);

// https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/

// Init our repositories
$clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
$scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
$accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
$userRepository = new UserRepository(); // instance of UserRepositoryInterface
$refreshTokenRepository = new RefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface

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

$grant = new \League\OAuth2\Server\Grant\PasswordGrant(
     $userRepository,
     $refreshTokenRepository
);

$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

// Enable the password grant on the server
$server->enableGrantType(
    $grant,
    new \DateInterval('PT1H') // access tokens will expire after 1 hour
);

$app->post('/access_token', function (ServerRequestInterface $request, ResponseInterface $response) use ($app) {

  /* @var \League\OAuth2\Server\AuthorizationServer $server */
  $server = $app->getContainer()->get(AuthorizationServer::class);

  try {

      // Try to respond to the request
      return $server->respondToAccessTokenRequest($request, $response);

  } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

      // All instances of OAuthServerException can be formatted into a HTTP response
      return $exception->generateHttpResponse($response);

  } catch (\Exception $exception) {

      // Unknown exception
      $body = new Stream('php://temp', 'r+');
      $body->write($exception->getMessage());
      return $response->withStatus(500)->withBody($body);

  }
});
