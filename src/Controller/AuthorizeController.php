<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizeController extends AbstractController
{
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
				$httpHost = $request->getServerParams()['HTTP_HOST'];

				// // Create a request
				// if (!$this->userManager->userExists($this->userId)) {
				// 	$result = new JSONResponse('Authorization required');
				// 	$result->setStatus(401);
				// 	return $result;
				// }

				$parser = new \Lcobucci\JWT\Parser();
				$token = $parser->parse($_GET['request']);
				$_SESSION['token'] = $token;
			
				$user = new \Pdsinterop\Solid\Auth\Entity\User();
				$user->setIdentifier('https://server/profile/card#me');

				$getVars = $_GET;
				if (!isset($getVars['grant_type'])) {
					$getVars['grant_type'] = 'implicit';
				}
				$getVars['response_type'] = 'token';
				$getVars['scope'] = "openid";
				
				if (!isset($getVars['redirect_uri'])) {
					$getVars['redirect_uri'] = 'https://solid.community/.well-known/solid/login';
				}
				$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals($_SERVER, $getVars, $_POST, $_COOKIE, $_FILES);
				$response = new \Laminas\Diactoros\Response();
				$server	= new \Pdsinterop\Solid\Auth\Server($this->authServerFactory, $this->authServerConfig, $response);

				// if (!$this->checkApproval()) {
				// 	$result = new JSONResponse('Approval required');
				// 	$result->setStatus(302);
				// 	$result->addHeader("Location", $this->urlGenerator->getAbsoluteURL($this->urlGenerator->linkToRoute("solid.server.sharing")));
				// 	return $result;
				// }

				// FIXME: check if the user has approved - if not, show approval screen;
				$approval = \Pdsinterop\Solid\Auth\Enum\Authorization::APPROVED;
				//		$approval = false;
				return $server->respondToAuthorizationRequest($request, $user, $approval);
    }
}
