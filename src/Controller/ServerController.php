<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
		
//		$this->baseUrl = "https://localhost";
    }

	public function getOpenIdEndpoints() {
		$this->baseUrl = "https://localhost/"; // FIXME: generate proper urls
		return [
			'issuer' => $this->baseUrl,
			'authorization_endpoint' => $this->baseUrl . "authorize",
			'jwks_uri' => $this->baseUrl . "jwks",
			"check_session_iframe" => $this->baseUrl . "session",
			"end_session_endpoint" => $this->baseUrl . "logout",
			"token_endpoint" => $this->baseUrl . "token",
			"userinfo_endpoint" => $this->baseUrl . "userinfo",
			"registration_endpoint" => $this->baseUrl . "register"
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
		$clientId = $_GET['client_id'];
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

		if (in_array($clientId, $allowedClients)) {
			return \Pdsinterop\Solid\Auth\Enum\Authorization::APPROVED;
		} else {
			return \Pdsinterop\Solid\Auth\Enum\Authorization::DENIED;
		}
	}
	
	public function getProfilePage() {
		return "https://localhost/profile/card#me";
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
	
}
