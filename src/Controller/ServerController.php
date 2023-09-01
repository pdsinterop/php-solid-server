<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Pdsinterop\Solid\DpopFactoryTrait;
use Pdsinterop\Solid\Auth\Config\Client;
use Pdsinterop\Solid\Auth\Enum\Authorization;
use Pdsinterop\Solid\Auth\Factory\ConfigFactory;

abstract class ServerController extends AbstractController
{

	use DpopFactoryTrait;

    protected $authServerConfig;
    protected $authServerFactory;
    protected $baseUrl;
    protected $config;
    protected $openIdConfiguration;
    protected $tokenGenerator;
    protected $userId;

    private $keys = [];

    public function __construct()
    {
		$this->config = new \Pdsinterop\Solid\ServerConfig(__DIR__.'/../../config/');

		$this->authServerConfig = $this->createAuthServerConfig();
		$this->authServerFactory = (new \Pdsinterop\Solid\Auth\Factory\AuthorizationServerFactory($this->authServerConfig))->create();
		$this->tokenGenerator = (new \Pdsinterop\Solid\Auth\TokenGenerator(
            $this->authServerConfig,
			$this->getDpopValidFor(),
			$this->getDpop()
        ));
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

    public function getKeys()
    {
        $encryptionKey = $this->config->getEncryptionKey();
        $privateKey = $this->config->getPrivateKey();
        $key = openssl_pkey_get_private($privateKey);
        $publicKey = openssl_pkey_get_details($key)['key'];

        return [
            "encryptionKey" => $encryptionKey,
            "privateKey" => $privateKey,
            "publicKey" => $publicKey,
        ];
    }

    public function createAuthServerConfig()
    {
        $clientId = ''; // $_GET['client_id']; // FIXME: No request object here to get the client Id from.
        $client = $this->getClient($clientId);
        $keys = $this->getKeys();

        return (new ConfigFactory(
				$client,
				$keys['encryptionKey'],
				$keys['privateKey'],
				$keys['publicKey'],
				$this->getOpenIdEndpoints()
			))->create();
	}

    public function getClient($clientId)
    {
        $clientRegistration = $this->config->getClientRegistration($clientId);

        if ($clientId && count($clientRegistration)) {
            $client = new Client(
                $clientId,
                $clientRegistration['client_secret'],
                $clientRegistration['redirect_uris'],
                $clientRegistration['client_name']
            );
        } else {
            $client = new Client('', '', [], '');
        }

        return $client;
    }

    public function createConfig()
    {
        $clientId = $_GET['client_id'];
        $client = $this->getClient($clientId);

        return (new ConfigFactory(
            $client,
            $this->keys['encryptionKey'],
            $this->keys['privateKey'],
            $this->keys['publicKey'],
            $this->openIdConfiguration
        ))->create();
    }

    public function checkApproval($clientId)
    {
        $approval = Authorization::DENIED;

        $allowedClients = $this->config->getAllowedClients($this->userId);

        if (
            $clientId === md5("tester") // FIXME: Double check that this is not a security issue; It is only here to help the test suite;
            || in_array($clientId, $allowedClients, true)
        ) {
            $approval = Authorization::APPROVED;
        }

        return $approval;
    }

    public function getProfilePage() : string
    {
        return $this->baseUrl . "/profile/card#me"; // FIXME: would be better to base this on the available routes if possible.
    }

    public function getResponseType($params) : string
    {
        $responseTypes = explode(" ", $params['response_type'] ?? '');

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
