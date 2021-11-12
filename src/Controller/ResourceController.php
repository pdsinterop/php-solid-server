<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Pdsinterop\Solid\Resources\Server;
use Pdsinterop\Solid\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Pdsinterop\Solid\Auth\Utils\DPop as DPop;
use Pdsinterop\Solid\Auth\WAC as WAC;

class idToken {
	public function getWebId($request) {
		$auth = explode(" ", $request->getServerParams()['HTTP_AUTHORIZATION']);
		$jwt = $auth[1];
		
		if (strtolower($auth[0]) == "dpop") {
			return DPop::getWebId($request);
		}
		
		if ($jwt) {
			$webId = $this->getSubjectFromJwt($jwt);
		} else {
			$webId = "public";
		}

		return $webId;
	}
	
	public function getSubjectFromJwt($jwt) {
		$parser = new \Lcobucci\JWT\Parser();
		try {
			$jwt = $parser->parse($jwt);
		} catch(\Exception $e) {
			return $this->server->getResponse()->withStatus(409, "Invalid JWT token");
		}

		if ($jwt->getClaim("token_type") == "pop") {
			$idToken = $jwt->getClaim("id_token");
			$idt = $parser->parse($idToken);
			return $idt->getClaim("sub");
		} else {
			return $jwt->getClaim("sub");
		}
	}	
}

class ResourceController extends AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var Server */
	private $baseUrl;
    private $server;
	private $DPop;
	private $WAC;
    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function __construct(Server $server)
    {
		$this->baseUrl = isset($_ENV['SERVER_ROOT']) ? $_ENV['SERVER_ROOT'] : "https://localhost";
        $this->server = $server;
		$this->DPop = new DPop();
		$this->idToken = new idToken();
		$this->WAC = new WAC($server->getFilesystem());

		// Make sure the root folder has an acl file, as is required by the spec;
		// Generate a default file granting the owner full access if there is nothing there.
		if (!$server->getFilesystem()->has("/storage/.acl")) {
			$defaultAcl = $this->generateDefaultAcl();
			$server->getFilesystem()->write("/storage/.acl", $defaultAcl);
		}
    }

    final public function __invoke(Request $request, array $args) : Response
    {
		try {
			$webId = $this->idToken->getWebId($request);
		} catch(\Exception $e) {
			return $this->server->getResponse()->withStatus(409, "Invalid token");
		}

		$origin = $request->getHeader("Origin");
		if (!$this->WAC->isAllowed($request, $webId, $origin)) {
			return $this->server->getResponse()->withStatus(403, "Access denied");
		}

		$response = $this->server->respondToRequest($request);
		$response = $this->WAC->addWACHeaders($request, $response, $webId);
		
        return $response;
    }

	private function generateDefaultAcl() {
		$defaultProfile = <<< EOF
# Root ACL resource for the user account
@prefix acl: <http://www.w3.org/ns/auth/acl#>.
@prefix foaf: <http://xmlns.com/foaf/0.1/>.

<#public>
        a acl:Authorization;
        acl:agentClass foaf:Agent;
        acl:accessTo </>;
        acl:default </>;
        acl:mode
				acl:Read.

# The owner has full access to every resource in their pod.
# Other agents have no access rights,
# unless specifically authorized in other .acl resources.
<#owner>
	a acl:Authorization;
	acl:agent <{user-profile-uri}>;
	# Set the access to the root storage folder itself
	acl:accessTo </>;
	# All resources will inherit this authorization, by default
	acl:default </>;
	# The owner has all of the access modes allowed
	acl:mode
		acl:Read, acl:Write, acl:Control.
EOF;

		$profileUri = $this->getUserProfile();
		$defaultProfile = str_replace("{user-profile-uri}", $profileUri, $defaultProfile);
		return $defaultProfile;
	}

	private function getUserProfile() {
		return $this->baseUrl . "/profile/card#me";
	}
}
