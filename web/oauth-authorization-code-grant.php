<?php declare(strict_types=1);

// https://oauth2.thephpleague.com/authorization-server/auth-code-grant/

// Init our repositories
$clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
$scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
$accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
$authCodeRepository = new AuthCodeRepository(); // instance of AuthCodeRepositoryInterface
$refreshTokenRepository = new RefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface

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

$grant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
     $authCodeRepository,
     $refreshTokenRepository,
     new \DateInterval('PT10M') // authorization codes will expire after 10 minutes
 );

$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

// Enable the authentication code grant on the server
$server->enableGrantType(
    $grant,
    new \DateInterval('PT1H') // access tokens will expire after 1 hour
);

$app->get('/authorize', function (ServerRequestInterface $request, ResponseInterface $response) use ($server) {

  try {

      // Validate the HTTP request and return an AuthorizationRequest object.
      $authRequest = $server->validateAuthorizationRequest($request);

      // The auth request object can be serialized and saved into a user's session.
      // You will probably want to redirect the user at this point to a login endpoint.

      // Once the user has logged in set the user on the AuthorizationRequest
      $authRequest->setUser(new UserEntity()); // an instance of UserEntityInterface

      // At this point you should redirect the user to an authorization page.
      // This form will ask the user to approve the client and the scopes requested.

      // Once the user has approved or denied the client update the status
      // (true = approved, false = denied)
      $authRequest->setAuthorizationApproved(true);

      // Return the HTTP redirect response
      return $server->completeAuthorizationRequest($authRequest, $response);

  } catch (OAuthServerException $exception) {

      // All instances of OAuthServerException can be formatted into a HTTP response
      return $exception->generateHttpResponse($response);

  } catch (\Exception $exception) {

      // Unknown exception
      $body = new Stream(fopen('php://temp', 'r+'));
      $body->write($exception->getMessage());
      return $response->withStatus(500)->withBody($body);

  }
});

// The client will request an access token using an authorization code so create an /access_token endpoint.

$app->post('/access_token', function (ServerRequestInterface $request, ResponseInterface $response) use ($server) {

  try {

      // Try to respond to the request
      return $server->respondToAccessTokenRequest($request, $response);

  } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

      // All instances of OAuthServerException can be formatted into a HTTP response
      return $exception->generateHttpResponse($response);

  } catch (\Exception $exception) {

      // Unknown exception
      $body = new Stream(fopen('php://temp', 'r+'));
      $body->write($exception->getMessage());
      return $response->withStatus(500)->withBody($body);
  }
});
