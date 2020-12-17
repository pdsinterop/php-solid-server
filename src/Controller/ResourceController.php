<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Pdsinterop\Solid\Resources\Server;
use Pdsinterop\Solid\Controller\AbstractController;
use Pdsinterop\Rdf\Enum\Format as Format;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Lcobucci\JWT\Parser;

class ResourceController extends AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var Server */
    private $server;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function __construct(Server $server)
    {
        $this->server = $server;
    }

    final public function __invoke(Request $request, array $args) : Response
    {
		$auth = explode(" ", $request->getServerParams()['HTTP_AUTHORIZATION']);
		$jwt = $auth[1];
		//error_log("JWT:$jwt");

		if (strtolower($auth[0]) == "dpop") {
			$dpop = $request->getServerParams()['HTTP_DPOP'];
			//error_log("DPOP:$dpop");
			if ($dpop) {
				try {
					$dpopKey = $this->getDpopKey($dpop, $request);
					//error_log("dpop looks valid!");
					if (!$this->validateDpop($jwt, $dpopKey)) {
						return $this->server->get()->withStatus(409, "Invalid token");
					}
				} catch(\Exception $e) {
					//error_log("dpop is invalid!");
					return $this->server->getResponse()->withStatus(409, "Invalid token");
				}
			}
		}

		if ($jwt) {
			$webId = $this->getSubjectFromJwt($jwt);
		} else {
			$webId = "public";
		}
		
		if (!$this->isAllowed($request, $webId)) {
			return $this->server->getResponse()->withStatus(403, "Access denied");
		}

		$userGrants = $this->getWACGrants($this->getUserGrants($request->getUri()->getPath(), $webId), $request->getUri());
		$publicGrants = $this->getWACGrants($this->getPublicGrants($request->getUri()->getPath()), $request->getUri());

		$wacHeaders = array();
		if ($userGrants) {
			$wacHeaders[] = "user=\"$userGrants\"";
		}
		if ($publicGrants) {
			$wacHeaders[] = "public=\"$publicGrants\"";
		}
		
		$response = $this->server->respondToRequest($request);
		$response = $response->withHeader("Link", '<.acl>; rel="acl"');
		$response = $response->withHeader("WAC-Allow", implode(",", $wacHeaders));

        return $response;
    }

	private function getWACGrants($grants, $uri) {
		$wacGrants = array();
		
		foreach ((array)$grants['accessTo'] as $grant => $grantedUri) {
			if ($this->arePathsEqual($grantedUri, $uri)) {
				$wacGrants[] = $this->grantToWac($grant);
			}
		}
		foreach ((array)$grants['default'] as $grant => $grantedUri) {
			if (!$this->arePathsEqual($grantedUri, $uri)) {
				$wacGrants[] = $this->grantToWac($grant);
			}
		}

		return implode(" ", $wacGrants);
	}
	private function grantToWac($grant) {
		return strtolower(explode("#", $grant)[1]); // http://www.w3.org/ns/auth/acl#Read => read
	}

	private function getPublicGrants($resourcePath) {
		$fs = $this->server->getFilesystem();
		$aclPath = $this->getAclPath($resourcePath);
		if (!$aclPath) {
			return array();
		}
		
		$acl = $fs->read($aclPath);

		$graph = new \EasyRdf_Graph();
		$graph->parse($acl, Format::TURTLE, $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME']);
		
		$grants = array();

		$foafAgent = "http://xmlns.com/foaf/0.1/Agent";
		$matching = $graph->resourcesMatching('http://www.w3.org/ns/auth/acl#agentClass');
		foreach ($matching as $match) {
			$agentClass = $match->get("<http://www.w3.org/ns/auth/acl#agentClass>");
			if ($agentClass == $foafAgent) {
				$accessTo = $match->get("<http://www.w3.org/ns/auth/acl#accessTo>");
				$default = $match->get("<http://www.w3.org/ns/auth/acl#default>");
				$modes = $match->all("<http://www.w3.org/ns/auth/acl#mode>");
				if ($default) {
					foreach ($modes as $mode) {
						$grants["default"][$mode->getUri()] = $default->getUri();
					}
				}
				if ($accessTo) {
					foreach ($modes as $mode) {
						$grants["accessTo"][$mode->getUri()] = $accessTo->getUri();
					}
				}
			}
		}
		return $grants;
	}

	private function getUserGrants($resourcePath, $webId) {
		$fs = $this->server->getFilesystem();
		$aclPath = $this->getAclPath($resourcePath);
		if (!$aclPath) {
			return array();
		}
		$acl = $fs->read($aclPath);

		$graph = new \EasyRdf_Graph();
		$graph->parse($acl, Format::TURTLE, $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME']);
		
		// error_log("GET GRANTS for $webId");

		$grants = $this->getPublicGrants($resourcePath);

		$matching = $graph->resourcesMatching('http://www.w3.org/ns/auth/acl#agent');
		//error_log("MATCHING " . sizeof($matching));
		// Find all grants machting our webId;
		foreach ($matching as $match) {
			$agent = $match->get("<http://www.w3.org/ns/auth/acl#agent>");
			if ($agent == $webId) {
				$accessTo = $match->get("<http://www.w3.org/ns/auth/acl#accessTo>");
				//error_log("$webId accessTo $accessTo");
				$default = $match->get("<http://www.w3.org/ns/auth/acl#default>");
				$modes = $match->all("<http://www.w3.org/ns/auth/acl#mode>");
				if ($default) {
					foreach ($modes as $mode) {
						$grants["default"][$mode->getUri()] = $default->getUri();
					}
				}
				if ($accessTo) {
					foreach ($modes as $mode) {
						$grants["accessTo"][$mode->getUri()] = $accessTo->getUri();
					}
				}
			}
		}

		return $grants;
	}

	private function getAclPath($path) {
		$fs = $this->server->getFilesystem();
		// get the filename from the request
		$filename = basename($path);
		$path = dirname($path);
		
		//error_log("REQUESTED PATH: $path");
		//error_log("REQUESTED FILE: $filename");

		$aclOptions = array(
			$path.'/'.$filename.'.acl',
			$path.'/'.$filename.'/.acl',
			$path.'/.acl'
		);

		foreach ($aclOptions as $aclPath) {
			if (
				$fs->has($aclPath)
			) {
				return $aclPath;
			}
		}

		//error_log("Seeking .acl from $path");
		// see: https://github.com/solid/web-access-control-spec#acl-inheritance-algorithm
		// check for acl:default predicate, if not found, continue searching up the directory tree
		return $this->getParentAcl($path);
	}

	private function getParentAcl($path) {
		//error_log("GET PARENT ACL $path");
		$fs = $this->server->getFilesystem();
		if ($fs->has($path.'/.acl')) {
			//error_log("CHECKING ACL FILE ON $path/.acl");
			return $path . "/.acl";
		}
		$parent = dirname($path);
		if ($parent == $path) {
			return false;
		} else {
			return $this->getParentAcl($parent);
		}
	}

	public function getRequestedGrants($request) {
		$method = strtoupper($request->getMethod());
		$fs = $this->server->getFilesystem();
		$path = $request->getUri()->getPath();

		switch ($method) {
			case "GET":
			case "HEAD":
				return array(
					"resource" => array('http://www.w3.org/ns/auth/acl#Read')
				);
			break;
			case "DELETE":
				return array(
					"resource" => array('http://www.w3.org/ns/auth/acl#Write')
				);
			break;
			case "PUT":
				if ($fs->has($path)) {
					$body = $request->getBody()->getContents();
					$request->getBody()->rewind();

					$existingFile = $fs->read($path);
					if (strpos($body, $existingFile) === 0) { // new file starts with the content of the old, so 'Append' grant wil suffice;
						return array(
							"resource" => array(
								'http://www.w3.org/ns/auth/acl#Write',
								'http://www.w3.org/ns/auth/acl#Append'
							)
						);
					} else {
						return array(
							"resource" => array('http://www.w3.org/ns/auth/acl#Write')
						);
					}
				} else {
					// FIXME: to add a new file, Append is needed on the parent container;
					return array(
						"resource" => array('http://www.w3.org/ns/auth/acl#Write'),
						"parent"   => array('http://www.w3.org/ns/auth/acl#Append', 'http://www.w3.org/ns/auth/acl#Write')
					);
				}
			break;
			case "POST":
				return array(
					"resource" => array(
						'http://www.w3.org/ns/auth/acl#Write', // We need 'append' for this, but because Write trumps Append, also allow it when we have Write;
						'http://www.w3.org/ns/auth/acl#Append'
					)
				);
			break;
			case "PATCH";
				$grants = array();
				$body = $request->getBody()->getContents();
				if (strstr($body, "DELETE")) {
					$grants[] = 'http://www.w3.org/ns/auth/acl#Write';
				}
				if (strstr($body, "INSERT")) {
					if ($fs->has($path)) {
						$grants[] = 'http://www.w3.org/ns/auth/acl#Append';
					}
					$grants[] = 'http://www.w3.org/ns/auth/acl#Write';
				}
				// error_log($body);
				$request->getBody()->rewind();
				if ($fs->has($path)) {
					return array(
						"resource" => $grants
					);
				} else {
					return array(
						"resource" => $grants,
						"parent"   => array('http://www.w3.org/ns/auth/acl#Append', 'http://www.w3.org/ns/auth/acl#Write')
					);
				}
			break;
		}
	}

	private function arePathsEqual($grantPath, $requestPath) {
		// error_log("COMPARING GRANTPATH: [" . $grantPath. "]");
		// error_log("COMPARING REQPATH: [" . $requestPath . "]");
		return $grantPath == $requestPath;
	}

	private function getParentUri($uri) {
		$path = $uri->getPath();
		if ($path == "/") {
			return $uri;
		}

		$parentPath = dirname($path) . '/';
		$fs = $this->server->getFilesystem();
		if ($fs->has($parentPath)) {
			return $uri->withPath($parentPath);
		} else {
			return $this->getParentUri($uri->withPath($parentPath));
		}
	}

	/**
	 * Checks the requested filename (path+name) and user (webid) to see if the request
	 * is allowed to continue, according to the web acl
	 * see: https://github.com/solid/web-access-control-spec
	 */

	public function isAllowed($request, $webId) {
		$requestedGrants = $this->getRequestedGrants($request);
		$uri = $request->getUri();
		$parentUri = $this->getParentUri($uri);
		if (
			$this->isGranted($requestedGrants['resource'], $uri, $webId) &&
			$this->isGranted($requestedGrants['parent'], $parentUri, $webId)
		) {
			return true;
		}
		return false;
	}

	private function isGranted($requestedGrants, $uri, $webId) {
		if (!$requestedGrants) {
			return true;
		}
		
		// error_log("REQUESTED GRANT: " . join(" or ", $requestedGrants) . " on $uri");
		$grants = $this->getUserGrants($uri->getPath(), $webId);
		// error_log("GRANTED GRANTS for $webId: " . json_encode($grants));
		if (is_array($grants)) {
			foreach ($requestedGrants as $requestedGrant) {
				if ($grants['accessTo'] && $grants['accessTo'][$requestedGrant] && $this->arePathsEqual($grants['accessTo'][$requestedGrant], $uri)) {
					return true;
				} else if ($grants['default'][$requestedGrant]) {
					if ($this->arePathsEqual($grants['default'][$requestedGrant], $uri)) {
						return false; // only use default for children, not for an exact match;
					}
					return true;
				}
			}
		}
		return false;
	}
	
	// FIXME: Duplicate code from servercontroller, because we don't extend that;
	public function getDpopKey($dpop, $request) {
		/*
			4.2.  Checking DPoP Proofs
			   To check if a string that was received as part of an HTTP Request is
			   a valid DPoP proof, the receiving server MUST ensure that
			   1.  the string value is a well-formed JWT,
			   2.  all required claims are contained in the JWT,
			   3.  the "typ" field in the header has the value "dpop+jwt",
			   4.  the algorithm in the header of the JWT indicates an asymmetric
				   digital signature algorithm, is not "none", is supported by the
				   application, and is deemed secure,
			   5.  that the JWT is signed using the public key contained in the
				   "jwk" header of the JWT,
			   6.  the "htm" claim matches the HTTP method value of the HTTP request
				   in which the JWT was received (case-insensitive),
			   7.  the "htu" claims matches the HTTP URI value for the HTTP request
				   in which the JWT was received, ignoring any query and fragment
				   parts,
			   8.  the token was issued within an acceptable timeframe (see
				   Section 9.1), and
			   9.  that, within a reasonable consideration of accuracy and resource
				   utilization, a JWT with the same "jti" value has not been
				   received previously (see Section 9.1).
		*/
		//error_log("1");

		$parser = new \Lcobucci\JWT\Parser();
		// 1.  the string value is a well-formed JWT,
		$dpop = $parser->parse($dpop);
		
		//error_log("2");
	    // 2.  all required claims are contained in the JWT,
		$htm = $dpop->getClaim("htm"); // http method
		$htu = $dpop->getClaim("htu"); // http uri
		$typ = $dpop->getHeader("typ");
		$alg = $dpop->getHeader("alg");

		//error_log("3");
		// 3.  the "typ" field in the header has the value "dpop+jwt",
		if ($typ != "dpop+jwt") {
			throw new Exception("typ is not dpop+jwt");
		}

		//error_log("4");
		// 4.  the algorithm in the header of the JWT indicates an asymmetric 
		//	   digital signature algorithm, is not "none", is supported by the
		//	   application, and is deemed secure,   
		if ($alg == "none") {
			throw new Exception("alg is none");
		}
		if ($alg != "RS256") {
			throw new Exception("alg is not supported");
		}
		
		//error_log("5");
		// 5.  that the JWT is signed using the public key contained in the
		//     "jwk" header of the JWT,
		
		// FIXME: get the public key
		
		//error_log("6");
		// 6.  the "htm" claim matches the HTTP method value of the HTTP request
		//	   in which the JWT was received (case-insensitive),
		if (strtolower($htm) != strtolower($request->getMethod())) {
			throw new Exception("htm http method is invalid");
		}

		//error_log("7");
		// 7.  the "htu" claims matches the HTTP URI value for the HTTP request
		//     in which the JWT was received, ignoring any query and fragment
		// 	   parts,
		$requestedPath = $request->getServerParams()['REQUEST_SCHEME'] . "://" . $request->getServerParams()['SERVER_NAME'] . $request->getRequestTarget();
		$requestedPath = preg_replace("/[?#].*$/", "", $requestedPath);
		// FIXME: Remove this; it was disabled for testing with a server running on 443 internally but accessible on :444
		$htu = str_replace(":444", "", $htu);
		$requestedPath = str_replace(":444", "", $requestedPath);
		//error_log("REQUESTED HTU $htu");
		//error_log("REQUESTED PATH $requestedPath");
		if ($htu != $requestedPath) { 
			throw new Exception("htu does not match requested path");
		}

		//error_log("8");
		$jwk = $dpop->getHeader("jwk");
		//error_log($jwk->kid);

		// FIXME: validate that the dpop was signed with the dpop key;
		// $signer = new Sha256();
		// if (!$dpop->verify($signer, $jwk->kid)) {
		// 	throw new Exception("token was not signed by the supplied key");
		// }
		
		// 8.  the token was issued within an acceptable timeframe (see Section 9.1), and
		// $iat = $dpop->getClaim("iat"); // FIXME: Is it correct that this was already verified by the parser?
		// $exp = $dpop->getClaim("exp"); // FIXME: Is it correct that this was already verified by the parser?
		
		// 9.  that, within a reasonable consideration of accuracy and resource utilization, a JWT with the same "jti" value has not been received previously (see Section 9.1).
		// FIXME: Check if we know the jti;
		//error_log("9");
		return $jwk->kid;
	}
	
	public function validateDpop($jwt, $dpopKey) {
		$parser = new \Lcobucci\JWT\Parser();
		$jwt = $parser->parse($jwt);
		$cnf = $jwt->getClaim("cnf");
		
		if ($cnf->jkt == $dpopKey) {
			//error_log("dpopKey matches");
			return true;
		}
		//error_log("dpopKey mismatch");
		//error_log(print_r($cnf, true));
		//error_log($dpopKey);
		
		return false;
	}
	
	public function getSubjectFromJwt($jwt) {
		$parser = new \Lcobucci\JWT\Parser();
		try {
			$jwt = $parser->parse($jwt);
		} catch(\Exception $e) {
			return $this->server->getResponse()->withStatus(409, "Invalid JWT token");
		}

		$sub = $jwt->getClaim("sub");
		return $sub;
	}	
}
