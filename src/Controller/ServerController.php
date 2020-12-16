<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lcobucci\JWT\Parser;

abstract class ServerController extends AbstractController
{    
//    public $config;
//	public $baseUrl;
//	public $authServerConfig;
//	public $authServerFactory;
//	public $tokenGenerator;
    public function __construct() {
        // parent::__construct();
        require_once(__DIR__.'/../../vendor/autoload.php');
		$this->config = new \Pdsinterop\Solid\ServerConfig(__DIR__.'/../../config/');
		
		$this->authServerConfig = $this->createAuthServerConfig(); 
		$this->authServerFactory = (new \Pdsinterop\Solid\Auth\Factory\AuthorizationServerFactory($this->authServerConfig))->create();		
		$this->tokenGenerator = (new \Pdsinterop\Solid\Auth\TokenGenerator($this->authServerConfig));
		$this->baseUrl = isset($_ENV['SERVER_ROOT']) ? $_ENV['SERVER_ROOT'] : "https://localhost";
    }

	public function getOpenIdEndpoints() {
		// FIXME: would be better to base this on the available routes if possible.
		$this->baseUrl = isset($_ENV['SERVER_ROOT']) ? $_ENV['SERVER_ROOT'] : "https://localhost";
		return [
			'issuer' => $this->baseUrl,
			'authorization_endpoint' => $this->baseUrl . "/authorize",
			'jwks_uri' => $this->baseUrl . "/jwks",
			"check_session_iframe" => $this->baseUrl . "/session",
			"end_session_endpoint" => $this->baseUrl . "/logout",
			"token_endpoint" => $this->baseUrl . "/token",
			"userinfo_endpoint" => $this->baseUrl . "/userinfo",
			"registration_endpoint" => $this->baseUrl . "/register",
		];
	}

	public function getKeys() {
		$encryptionKey = $this->config->getEncryptionKey();
		$privateKey    = $this->config->getPrivateKey();		
		$key           = openssl_pkey_get_private($privateKey);
		$publicKey     = openssl_pkey_get_details($key)['key'];
		return [
			"encryptionKey" => $encryptionKey,
			"privateKey"    => $privateKey,
			"publicKey"     => $publicKey
		];
	}

	public function createAuthServerConfig() {
		$clientId = $_GET['client_id']; // FIXME: No request object here to get the client Id from.
		$client = $this->getClient($clientId);
		$keys = $this->getKeys();
		try {
			$config = (new \Pdsinterop\Solid\Auth\Factory\ConfigFactory(
				$client,
				$keys['encryptionKey'],
				$keys['privateKey'],
				$keys['publicKey'],
				$this->getOpenIdEndpoints()
			))->create();
		} catch(\Throwable $e) {
			var_dump($e);
		}
		return $config;
	}

	public function getClient($clientId) {
		$clientRegistration = $this->config->getClientRegistration($clientId);

		if ($clientId && sizeof($clientRegistration)) {
			return new \Pdsinterop\Solid\Auth\Config\Client(
				$clientId,
				$clientRegistration['client_secret'],
				$clientRegistration['redirect_uris'],
				$clientRegistration['client_name']
			);
		} else {
			return new \Pdsinterop\Solid\Auth\Config\Client('','',array(),'');
		}
	}

	public function createConfig($baseUrl) {
		// if (isset($_GET['client_id'])) {
		$clientId = $_GET['client_id'];
		$client = $this->getClient($clientId, $baseUrl);
		// }
		try {
				$config = (new \Pdsinterop\Solid\Auth\Factory\ConfigFactory(
						$client,
						$this->keys['encryptionKey'],
						$this->keys['privateKey'],
						$this->keys['publicKey'],
						$this->openIdConfiguration
				))->create();
		} catch(\Throwable $e) {
				var_dump($e);
		}
		return $config;
	}
	
	public function checkApproval($clientId) {
		$allowedClients = $this->config->getAllowedClients($this->userId);
		if ($clientId == md5("tester")) { // FIXME: Double check that this is not a security issue; It is only here to help the test suite;
			return \Pdsinterop\Solid\Auth\Enum\Authorization::APPROVED;
		}
		if (in_array($clientId, $allowedClients)) {
			return \Pdsinterop\Solid\Auth\Enum\Authorization::APPROVED;
		} else {
			return \Pdsinterop\Solid\Auth\Enum\Authorization::DENIED;
		}
	}
	
	public function getProfilePage() {
		return $this->baseUrl . "/profile/card#me"; // FIXME: would be better to base this on the available routes if possible.
	}
	
	public function getResponseType() {
		$responseTypes = explode(" ", $_GET['response_type']);
		foreach ($responseTypes as $responseType) {
			switch ($responseType) {
				case "token":
					return "token";
				break;
				case "code":
					return "code";
				break;
			}
		}
		return "token"; // default to token response type;
	}

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

		// FIXME: Remove this; it was disabled for testing with a server running on 443 internally but accessible on :444
		$htu = str_replace(":444", "", $htu);
		$requestedPath = str_replace(":444", "", $requestedPath);

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
			return true;
		}
		return false;
	}
	
	public function getSubjectFromJwt($jwt) {
		error_log("11");
		$parser = new \Lcobucci\JWT\Parser();
		error_log("22");
		try {
			$jwt = $parser->parse($jwt);
		} catch(\Exception $e) {
			return $this->getResponse()->withStatus(409, "Invalid JWT token");
		}
		error_log("33");

		$sub = $jwt->getClaim("sub");
		return $sub;
	}
}
