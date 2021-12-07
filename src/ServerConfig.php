<?php declare(strict_types=1);

namespace Pdsinterop\Solid;

class ServerConfig {
	private $path;
	private $serverConfig;
	private $userConfig;
	
	public function __construct($path) {
		$this->path = $path;
		$this->serverConfigFile = $this->path . "serverConfig.json";
		$this->userConfigFile = $this->path . "user.json";
		$this->serverConfig = $this->loadConfig();
		$this->userConfig = $this->loadUserConfig();
		
	}

    public function getAllowedOrigins()
    {
        $allowedOrigins = [];

        $serverConfig = $this->serverConfig;
        foreach ($serverConfig as $value) {
            if (isset($value['redirect_uris'])) {
                foreach($value['redirect_uris'] as $url) {
                    $allowedOrigins[] = parse_url($url)['host'];
                }
            }
        }

        return array_unique($allowedOrigins);
    }

	private function loadConfig() {
		if (!file_exists($this->serverConfigFile)) {
			$keySet = $this->generateKeySet();
			$this->serverConfig = array(
				"encryptionKey" => $keySet['encryptionKey'],
				"privateKey" => $keySet['privateKey']
			);
			$this->saveConfig();
		}
		return json_decode(file_get_contents($this->serverConfigFile), true);
	}
	private function saveConfig() {
		file_put_contents($this->serverConfigFile, json_encode($this->serverConfig, JSON_PRETTY_PRINT));
	}
	private function loadUserConfig() {
		if (!file_exists($this->userConfigFile)) {
			$this->userConfig = array(
				"allowedClients" => array()
			);
			$this->saveUserConfig();
		}
		return json_decode(file_get_contents($this->userConfigFile), true);
	}
	private function saveUserConfig() {
		file_put_contents($this->userConfigFile, json_encode($this->userConfig, JSON_PRETTY_PRINT));
	}
	
	/* Server data */
	public function getPrivateKey() {
		return $this->serverConfig['privateKey'];
	}
	
	public function getEncryptionKey() {
		return $this->serverConfig['encryptionKey'];
	}

	public function getClientConfigById($clientId) {
		$clients = (array)$this->serverConfig['clients'];

		if (array_key_exists($clientId, $clients)) {
			return $clients[$clientId];
		}
		return null;
	}

	public function saveClientConfig($clientConfig) {
		$clientId = uuidv4();
		$this->serverConfig['clients'][$clientId] = $clientConfig;
		$this->saveConfig();
		return $clientId;
	}
	
	public function saveClientRegistration($origin, $clientData) {
		$originHash = md5($origin);
		$existingRegistration = $this->getClientRegistration($originHash);
		if ($existingRegistration && isset($existingRegistration['client_name'])) {
			return $originHash;
		}

		$clientData['client_name'] = $origin;
		$clientData['client_secret'] = md5(random_bytes(32));
		$this->serverConfig['client-' . $originHash] = $clientData;
		$this->saveConfig();
		return $originHash;
	}

	public function getClientRegistration($clientId) {
		if (isset($this->serverConfig['client-' . $clientId])) {
			return $this->serverConfig['client-' . $clientId];
		} else {
			return array();
		}
	}

	/* User specific data */
	public function getAllowedClients($userId) {
		return $this->userConfig['allowedClients'];
	}

	public function addAllowedClient($userId, $clientId) {
		$this->userConfig['allowedClients'][] = $clientId;
		$this->userConfig['allowedClients'] = array_unique($this->userConfig['allowedClients']);
		$this->saveUserConfig();
	}

	public function removeAllowedClient($userId, $clientId) {
		$this->userConfig['allowedClients'] = array_diff($this->userConfig['allowedClients'], array($clientId));
		$this->saveUserConfig();
	}

	/* Helper functions */		
	private function generateKeySet() {
		$config = array(
			"digest_alg" => "sha256",
			"private_key_bits" => 2048,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		);
		// Create the private and public key
		$key = openssl_pkey_new($config);

		// Extract the private key from $key to $privateKey
		openssl_pkey_export($key, $privateKey);
		$encryptionKey = base64_encode(random_bytes(32));
		$result = array(
			"privateKey" => $privateKey,
			"encryptionKey" => $encryptionKey
		);
		return $result;
	}
}
