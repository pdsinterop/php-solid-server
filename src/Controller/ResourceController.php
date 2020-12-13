<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Pdsinterop\Solid\Resources\Server;
use Pdsinterop\Solid\Traits\HasFilesystemTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Lcobucci\JWT\Parser;

class ResourceController extends AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    use HasFilesystemTrait;

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
		error_log("JWT:$jwt");

		if (strtolower($auth[0]) == "dpop") {
			$dpop = $request->getServerParams()['HTTP_DPOP'];
			error_log("DPOP:$dpop");
			if ($dpop) {
				try {
					$dpopKey = $this->getDpopKey($dpop, $request);
					error_log("dpop looks valid!");
					if (!$this->validateDpop($jwt, $dpopKey)) {
						return $this->getResponse()->withStatus(409, "Invalid token");
					}
				} catch(\Exception $e) {
					error_log("dpop is invalid!");
					return $this->getResponse()->withStatus(409, "Invalid token");
				}
			}
		}
		if ($jwt) {
			$webId = $this->getSubjectFromJwt($jwt);
		}
		if (!$this->isAllowed($webId, $request)) {
			return $this->getResponse()->withStatus(403, "Access denied");
		}
		
		$response = $this->server->respondToRequest($request);
		$response = $response->withHeader("Link", '<.acl>; rel="acl"');

        return $response;
    }

    	/**
    	 * Checks the requested filename (path+name) and user (webid) to see if the request
    	 * is allowed to continue, according to the web acl
    	 * see: https://github.com/solid/web-access-control-spec
    	 */
	public function isAllowed($webId, $request) {
		return true; // FIXME: Check if $webid actually has access to the requested resource;
		$fs = $this->getFilesystem();
		// get the path from the request
		$path = $request->getUri()->getPath();
		// get the filename from the request
		$filename = basename($path);
		$path = dirname($path);
		// get the method from the request
		$method = $request->getMethod();
		// look for .acl file, deepest directory first (filename.acl or .acl in dirs going up)
		if ($fs->has($path.$filename.'.acl')) {
			// parse acl
			// check that method is allowed
		} else {
			// see: https://github.com/solid/web-access-control-spec#acl-inheritance-algorithm
			// check for acl:default predicate, if not found, continue searching up the directory tree
			$default = false;
			while (!$default && $path && $path!='.') {
				while ($path && $path!='.' && !$fs->has($path.'.acl') ) {
					$path = dirname($path);
				}
				if ($path && $path!='.') {
					$acl = $fs->read($path.'.acl');
//					$default = \Pdsinterop\Solid\WebACL::parse($acl)->getDefault();
				}
			}
			if ($default) {
				// check that method is allowed
				$methodsToGrant = [
					'GET'    => 'Read',
					'HEAD'   => 'Read',
					'POST'   => 'Append',
					'PATCH'  => 'Write',
					'PUT'    => 'Write',
					'DELETE' => 'Write'
				];
			}
		}
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
		error_log("1");

		$parser = new \Lcobucci\JWT\Parser();
		// 1.  the string value is a well-formed JWT,
		$dpop = $parser->parse($dpop);
		
		error_log("2");
	    // 2.  all required claims are contained in the JWT,
		$htm = $dpop->getClaim("htm"); // http method
		$htu = $dpop->getClaim("htu"); // http uri
		$typ = $dpop->getHeader("typ");
		$alg = $dpop->getHeader("alg");

		error_log("3");
		// 3.  the "typ" field in the header has the value "dpop+jwt",
		if ($typ != "dpop+jwt") {
			throw new Exception("typ is not dpop+jwt");
		}

		error_log("4");
		// 4.  the algorithm in the header of the JWT indicates an asymmetric 
		//	   digital signature algorithm, is not "none", is supported by the
		//	   application, and is deemed secure,   
		if ($alg == "none") {
			throw new Exception("alg is none");
		}
		if ($alg != "RS256") {
			throw new Exception("alg is not supported");
		}
		
		error_log("5");
		// 5.  that the JWT is signed using the public key contained in the
		//     "jwk" header of the JWT,
		
		// FIXME: get the public key
		
		error_log("6");
		// 6.  the "htm" claim matches the HTTP method value of the HTTP request
		//	   in which the JWT was received (case-insensitive),
		if (strtolower($htm) != strtolower($request->getMethod())) {
			throw new Exception("htm http method is invalid");
		}

		error_log("7");
		// 7.  the "htu" claims matches the HTTP URI value for the HTTP request
		//     in which the JWT was received, ignoring any query and fragment
		// 	   parts,
		$requestedPath = $request->getServerParams()['REQUEST_SCHEME'] . "://" . $request->getServerParams()['SERVER_NAME'] . $request->getRequestTarget();
		$requestedPath = preg_replace("/[?#].*$/", "", $requestedPath);
		error_log("REQUESTED HTU $htu");
		error_log("REQUESTED PATH $requestedPath");
		if ($htu != $requestedPath) { 
			throw new Exception("htu does not match requested path");
		}

		error_log("8");
		$jwk = $dpop->getHeader("jwk");
		error_log($jwk->kid);

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
		error_log("9");
		return $jwk->kid;
	}
	
	public function validateDpop($jwt, $dpopKey) {
		$parser = new \Lcobucci\JWT\Parser();
		$jwt = $parser->parse($jwt);
		$cnf = $jwt->getClaim("cnf");
		
		if ($cnf->jkt == $dpopKey) {
			error_log("dpopKey matches");
			return true;
		}
		error_log("dpopKey mismatch");
		error_log(print_r($cnf, true));
		error_log($dpopKey);
		
		return false;
	}
	
	public function getSubjectFromJwt($jwt) {
		$parser = new \Lcobucci\JWT\Parser();
		try {
			$jwt = $parser->parse($jwt);
		} catch(\Exception $e) {
			return $this->getResponse()->withStatus(409, "Invalid JWT token");
		}

		$sub = $jwt->getClaim("sub");
		return $sub;
	}	
}
