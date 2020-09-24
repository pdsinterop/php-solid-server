<?php

namespace Pdsinterop\Solid\Authentication\Factory;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Pdsinterop\Solid\Authentication\Repository\AccessToken;
use Pdsinterop\Solid\Authentication\Repository\Client;
use Pdsinterop\Solid\Authentication\Repository\Scope;

class AuthorizationServerFactory
{
    public function createClientCredentialsGrant(): Callable
    {
        return static function () {
            $clientRepository = new Client(); // instance of ClientRepositoryInterface
            $scopeRepository = new Scope(); // instance of ScopeRepositoryInterface
            $accessTokenRepository = new AccessToken(); // instance of AccessTokenRepositoryInterface

            // @FIXME: Keys,path to keys, passphrase, etc. should be configurable.
            $keysPath = dirname(__DIR__, 2) . '/tests/fixtures/keys';
            $privateKeyPath = 'file://' . $keysPath .'/private.key';
            //$privateKeyPath = new CryptKey($privateKeyPath, 'passphrase'); // if private key has a pass phrase

            // string key
            $encryptionKey = file_get_contents($keysPath.'/encryption.key');

            // Defuse\Crypto\Key
            // $encryptionKey = \Defuse\Crypto\Key::loadFromAsciiSafeString($keysPath . '/crypto.key');

            // Setup the authorization server
            $server = new AuthorizationServer(
                $clientRepository,
                $accessTokenRepository,
                $scopeRepository,
                $privateKeyPath,
                $encryptionKey
            );

            // Enable the client credentials grant on the server
            $server->enableGrantType(
                new ClientCredentialsGrant(),
                new \DateInterval('PT1H') // access tokens will expire after 1 hour
            );

            return $server;
        };
    }

}
