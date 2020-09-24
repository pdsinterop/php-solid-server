<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OpenidController extends AbstractController
{    
    private $keys;
	
    public function __construct(){
        // parent::__construct();
        require_once(__DIR__.'/../../vendor/autoload.php');

        $this->keys = $this->getKeys();
    }
    private function linkToRoute($route) {
        return "/$route";
    }
    private function getBaseUrl($httpHost) {
        return "https://$httpHost";
    }
    private function getAbsoluteUrl($relativeUrl, $baseUrl) {
        return "$baseUrl$relativeUrl";
    }
    private function getOpenIdConfiguration($baseUrl) {
				return array(
					'issuer' => $baseUrl,
					'authorization_endpoint' => $this->getAbsoluteUrl($this->linkToRoute("authorize"), $baseUrl),
					'jwks_uri' => $this->getAbsoluteUrl($this->linkToRoute("jwks"), $baseUrl),
					"response_types_supported" => array("code","code token","code id_token","id_token code","id_token","id_token token","code id_token token","none"),
					"token_types_supported" => array("legacyPop","dpop"),
					"response_modes_supported" => array("query","fragment"),
					"grant_types_supported" => array("authorization_code","implicit","refresh_token","client_credentials"),
					"subject_types_supported" => ["public"],
					"id_token_signing_alg_values_supported" => ["RS256"],
					"token_endpoint_auth_methods_supported" => "client_secret_basic",
					"token_endpoint_auth_signing_alg_values_supported" => ["RS256"],
					"display_values_supported" => [],
					"claim_types_supported" => ["normal"],
					"claims_supported" => [],
					"claims_parameter_supported" => false,
					"request_parameter_supported" => true,
					"request_uri_parameter_supported" => false,
					"require_request_uri_registration" => false,
					"check_session_iframe" => $this->getAbsoluteUrl($this->linkToRoute("session"), $baseUrl),
					"end_session_endpoint" => $this->getAbsoluteUrl($this->linkToRoute("logout"), $baseUrl),
					"token_endpoint" => $this->getAbsoluteUrl($this->linkToRoute("token"), $baseUrl),
					"userinfo_endpoint" => $this->getAbsoluteUrl($this->linkToRoute("userinfo"), $baseUrl),
					"registration_endpoint" => $this->getAbsoluteUrl($this->linkToRoute("register"), $baseUrl),
			//		"sharing_endpoint" => $this->getAbsoluteUrl($this->linkToRoute("sharing"), $baseUrl)
				);
    }
    private function getKeys() {
				// FIXME: read these from the solid config in nextcloud;
				$encryptionKey = 'P76gcBVeXsVzrHiYp4IIwore5rQz4cotdZ2j9GV5V04=';
				$privateKey = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAvqb0htUFZaZ+z5rn7cHWg0VzsSoVnusbtJvwWtHfD0T0s6Hb
OqzE5h2fgdGbB49HRtc21SNHx6jeEStGv03yyqYkLUKrJJSg+ksrL+pT3Nd0h25q
sx7YUoPPxnm6sbd3XTg5efCb2yyV2dOoAegUPjK46Ra6PqUvmICQWDsjnv0VJIx+
TdDWmKY2xElk0T6CVNMD08OZVTHPwJgpGdRZyCK/SSmrvmAZ6K3ocKySJdKgYriR
bVMdx9NsczRkYU9b7tUpPmLu3IvsLboTbfRN23Y70Gx3Z8fuI1FRn23sEuQSIRW+
NsAi7l+AEdJ7MdYn0xSY6YMNJ0/aGXi55gagQwIDAQABAoIBAQCz8CNNtnPXkqKR
EmTfk1kAoGYmyc+KI+AMQDlDnlzmrnA9sf+Vi0Zy4XaQMeId6m6dP7Yyx4+Rs6GT
lsK4/7qs5M20If4hEl40nQlvubvY7UjAIch2sh/9EQbjDjTUUpJH2y70FdEjtRrh
cdBZrE6evYSkCZ1STtlzF7QkcfyWqilTHEntrHRaM3N+B6F74Yi5g6VyGE9uqKEM
EuGDHVSXizdUjauTTVEa4o7pxTh+eTIdQsfRewer7iuxFPo2vBNOTU2O/obNUsVK
mgmGM4QDjurgXLL2XPr0dVVo3eiFvIdmtZgGVyLfL/vUXH7bwUIfkV6qWyRmdBiY
Dfsm8BJBAoGBAOGebDUVnP3NgFacWVYrtvBXcH2Q6X1W6JEAxctDDsnjchTdyG9E
zcsMVM/gFKXIDF5VeNoSt2pwCTBL6K0oPC31c01clActbHStaJWOOCuifzrvmu4n
X51TNGoKggbbSVx1UTifKte2t6SPRaZ26EqVrmO44fGkA3ip6TRYnSFzAoGBANhT
J47EieRWiNflq9XqDAZ1fZzo3AHB+b+pO4r8GZr3Dw0ShCAnQXv7Gb2JAJvE3UrC
Aq5r3yZMM7nI+n/OT06+UcJ3/vDGAPx9trNrpWkwmcWBmoBfp86vDRhT0kEIiKbO
wLYMmSNLHNkmQQdBX2ytnsRxRyCWtQmm09bzOJHxAoGBAKEB/nSPnP5elfS5FOPy
xFWWANgK/yWMTOGV7JFWpIocvz/22d/V+QqrHSdP4UxBi9oSIvF1I+FYXKZTtZNE
wFWH8SXHKHhKyTgmvBjmal1xVFyJu0WzYX+TbjcykoI0IZFSw4ilxdw1L67G88yM
1M7NLKtLuCpKgpOspZjOmCvTAoGAGji6KswYCt2SaNkmIx/jpUTInSR8xpnEtD7H
QOmeEPKxmFwON/eKMIUXcaoRsNAEIvOxb4MT4YiLHJIIC0XuxxS6xF/XP0hBBloW
s1jxC/cgLJixKa5uoNcHN1OxwMBQECgvo+GTDnwkWw4QA9kgwAOroxQ4EvMxrqHS
O9Pvn4ECgYA7xr/3Sz8n+BhgOdABW0m91P144rK9QDYiaClSxAha1KiFunmAy3pB
Uxdl4yTCTA9yKIH7X3bShDXnj+RmEZ+SkwzpPuKvAE8ZkZQuXv41anFrZYkR2PZy
oYiERqXgH5yS/mkDeXRFx1nWsVxjoLWfd/Vi7Lr43cuYFy4UjqXZdg==
-----END RSA PRIVATE KEY-----
EOF;

				$key = openssl_pkey_get_private($privateKey);
				$publicKey = openssl_pkey_get_details($key)['key'];
				
				return array(
					"encryptionKey" => $encryptionKey,
					"privateKey" => $privateKey,
					"publicKey" => $publicKey
				);
    }
    private function getClientId() {
        return "CoolApp";
    }
    private function getClient($clientId, $baseUrl) {
        if (!$clientId) {
            $clientId = $this->getClientId(); // FIXME: only continue if a clientId is set;
        }
        
        if ($clientId) { // FIXME: and check that we know this client and get the client secret/client name for this client;
            $clientSecret = "super-secret-secret-squirrel";
            
            // FIXME: use the redirect URIs as indicated by the client;
            $clientRedirectUris = array(
                $this->getAbsoluteURL($this->linkToRoute("token"), $baseUrl),
                'https://solid.community/.well-known/solid/login',
                'http://localhost:3001/redirect'
            );
            $clientName = "Nextcloud";

            return new \Pdsinterop\Solid\Auth\Config\Client(
                $clientId,
                $clientSecret,
                $clientRedirectUris,
                $clientName
            );
        } else {
            return new \Pdsinterop\Solid\Auth\Config\Client('','',array(),'');
        }
    }

	  private function createConfig($baseUrl) {
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

    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
				$httpHost = $request->getServerParams()['HTTP_HOST'];
				$baseUrl = $this->getBaseUrl($httpHost);
				$this->openIdConfiguration = $this->getOpenIdConfiguration($baseUrl);
				
				$this->authServerConfig = $this->createConfig($baseUrl);
				$this->authServerFactory = (new \Pdsinterop\Solid\Auth\Factory\AuthorizationServerFactory($this->authServerConfig))->create();

        $response = $this->getResponse();
				$server	= new \Pdsinterop\Solid\Auth\Server($this->authServerFactory, $this->authServerConfig, $response);
				return $server->respondToOpenIdMetadataRequest();
    }
}
