<?php declare(strict_types=1);

// @TODO: See if there area ny RDF PHPStorm plugins
/*
    /.acl

    /2014/
    /2014/financials
    /2014/financials.acl
    /2015/05/01/event1

    /data/
    /data/*
    /data/.acl
    /data/.meta
    /data/?query=SELECT%20*%20WHERE%20%7B%20%3Fs%20%3Fp%20%3Fo%20.%20%7D
    /data/foo
    /data/image.jpg
    /data/image.jpg.meta
    /data/res*
    /data/res1
    /data/res2
    /data/test

    /docs/
    /docs/.acl
    /docs/.acl/file1.acl
    /docs/file1
    /docs/file1.acl
    /docs/shared-file1
    /docs/shared-file1.acl

    /documents/papers/.acl
    /documents/papers/paper1
    /documents/papers/paper1.acl

    /work-groups#Accounting
    /work-groups#Management
 */
/* @FIXME: Figure out WTF is going on with these not being supported?

$htmlStrategy->setDefaultResponseHeader('content-type', 'text/html');
$jsonStrategy->setDefaultResponseHeader('content-type', 'application/json');
*/


/*/
    400 BadRequestException                 The request cannot be fulfilled due to bad syntax.
    401 UnauthorizedException               Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet been provided.
    403 ForbiddenException                  The request was a valid request, but the server is refusing to respond to it.
    404 NotFoundException                   The requested resource could not be found but may be available again in the future.
    405 MethodNotAllowedException           A request was made of a resource using a request method not supported by that resource; for example, using GET on a form which requires data to be presented via POST, or using PUT on a read-only resource.
    406 NotAcceptableException              The requested resource is only capable of generating content not acceptable according to the Accept headers sent in the request.
    409 ConflictException                   Indicates that the request could not be processed because of conflict in the request, such as an edit conflict in the case of multiple updates.
    410 GoneException                       Indicates that the resource requested is no longer available and will not be available again.
    411 LengthRequiredException             The request did not specify the length of its content, which is required by the requested resource.
    412 PreconditionFailedException         The server does not meet one of the preconditions that the requester put on the request.
    415 UnsupportedMediaException           The request entity has a media type which the server or resource does not support.
    417 ExpectationFailedException          The server cannot meet the requirements of the Expect request-header field.
    418 ImATeapotException                  [I'm a teapot](http://en.wikipedia.org/wiki/April_Fools%27_Day_RFC).
    428 PreconditionRequiredException       The origin server requires the request to be conditional.
    429 TooManyRequestsException            The user has sent too many requests in a given amount of time.
    451 UnavailableForLegalReasonsException The resource is unavailable for legal reasons.
    /*/


// map a JSON route
$router->map('GET', '/', function (ServerRequestInterface $request) : array {
  return [
      'title'   => 'My New Simple API',
      'version' => 1,
  ];
});


// =============================================================================
/* Strategies

 * Custom Strategies can be created by implementing League\Route\Strategy\StrategyInterface
 * For instance when returning an ResponseInterface is overkill, but the returned value should
 * be converted to something else (for instance Turle).

  Route provides two strategies out of the box, one aimed at standard web apps and one aimed at JSON APIs.

    - League\Route\Strategy\ApplicationStrategy (Default)
    - League\Route\Strategy\JsonStrategy (Requires a HTTP Response Factory)

  League\Route\Strategy\ApplicationStrategy is used by default, it provides the
  controller with a PSR-7 Psr\Http\Message\ServerRequestInterface implementation
  and any route arguments. It expects your controller to build and return an
  implementation of Psr\Http\Message\ResponseInterface.

  1. On the router - will run for every matched route.
  2. On a route group - will run for any matched route in that group.
  3. On a specific route - will run only when that route is matched.

Custom Strategies implement League\Route\Strategy\StrategyInterface

interface StrategyInterface
{
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request) : ResponseInterface;
    public function getNotFoundDecorator(NotFoundException $exception) : MiddlewareInterface;
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception) : MiddlewareInterface;
    public function getExceptionHandler() : MiddlewareInterface;
}

  See an existing strategy for details: https://github.com/thephpleague/route/blob/master/src/Strategy/JsonStrategy.php
*/
    // =============================================================================

// More routes, using map shortcut
$router->delete('/acme/route', 'Acme\Controller::deleteMethod');
$router->get('/acme/route', 'Acme\Controller::getMethod');
$router->head('/acme/route', 'Acme\Controller::headMethod');
$router->options('/acme/route', 'Acme\Controller::optionsMethod');
$router->patch('/acme/route', 'Acme\Controller::patchMethod');
$router->post('/acme/route', 'Acme\Controller::postMethod');
$router->put('/acme/route', 'Acme\Controller::putMethod');

// Host
$router
    ->map('GET', '/acme/route', 'Acme\Controller::getMethod')
    ->setHost('example.com')
;

// Scheme
$router
    ->map('GET', '/acme/route', 'Acme\Controller::getMethod')
    ->setHost('example.com')
    ->setScheme('https')
;

// Port
$router
    ->map('GET', '/acme/route', 'Acme\Controller::getMethod')
    ->setHost('example.com')
    ->setScheme('https')
    ->setPort(8080)
;

// Wildcards
$router->map('GET', '/user/{id}/{name}', function (ServerRequestInterface $request, array $args) : ResponseInterface {
  $args = [
      'id'   => '{id}',  // the actual value of {id}
      'name' => '{name}', // the actual value of {name}
  ];

  // ...
});

// Wildcard conditions
$router->map('GET', '/user/{id:number}/{name:word}', function (ServerRequestInterface $request, array $args) : ResponseInterface {
/*
  There are several built in conditions for dynamic segments of a URI.

  number
  word
  alphanum_dash
  slug
  uuid

  Dynamic segments can also be set as any regular expression such as {id:[0-9]+}.
*/
});

// Custom matchers
$router->addPatternMatcher('wordStartsWithM', '(m|M)[a-zA-Z]+');
$router->map('GET', 'user/mTeam/{name:wordStartsWithM}', function (ServerRequestInterface $request, array $args) : ResponseInterface {
    // $args = [
    //     'id'   => {id},  // the actual value of {id}
    //     'name' => {name} // the actual value of {name}
    // ];

    // ...
});


/* Middleware can be defined to run in 3 ways: */
//    1. On the router - will run for every matched route.
$router->middleware(new Acme\AuthMiddleware);

//    2. On a route group - will run for any matched route in that group.
$router
    ->group('/admin', function ($router) {
        // ... add routes
    })
    ->middleware(new Acme\AuthMiddleware)
;
//    3. On a specific route - will run only when that route is matched.
$router
    ->map('GET', '/private', 'Acme\SomeController::someMethod')
    ->middleware(new Acme\AuthMiddleware)
;

/*
The invocation order is as follows:

    1. Exception handler defined by the middleware. This middleware should wrap the rest of the application and catch any exceptions to be gracefully handled.
    2. Middleware added to the router.
    3. Middleware added to a matched route group.
    4. Middleware added to a specific matched route.
*/

// =============================================================================
// Fetching external resources
// -----------------------------------------------------------------------------
$homeResponse = $client->sendRequest(
  $messageFactory->createRequest('GET', 'http://httplug.io')
);

var_dump($homeResponse->getStatusCode()); // 200, hopefully

$missingPageResponse = $client->sendRequest(
  $messageFactory->createRequest('GET', 'http://httplug.io/missingPage')
);

var_dump($missingPageResponse->getStatusCode()); // 404

$promise = $httpAsyncClient->sendAsyncRequest($request);

$promise->then(function (ResponseInterface $response) {
  // onFulfilled callback
  echo 'The response is available';

  return $response;
}, function (Exception $e) {
  // onRejected callback
  echo 'An error happens';

  throw $e;
});
