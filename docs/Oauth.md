# OAuth Server details

A grant is a method of acquiring an access token.

Deciding which grants to implement depends on the type of client the end user will be using, and the experience you want for your users.

![](https://oauth2.thephpleague.com/images/grants.min.svg)

## Generating keys

### Generating public and private keys

The public/private key pair is used to sign and verify JWTs transmitted. The Authorization Server possesses the private key to sign tokens and the Resource Server possesses the corresponding public key to verify the signatures. To generate the private key run this command on the terminal:

```
openssl genrsa -out private.key 2048
```

If you want to provide a passphrase for your private key run this command instead:

```
openssl genrsa -passout pass:_passphrase_ -out private.key 2048
```

then extract the public key from the private key:

```
openssl rsa -in private.key -pubout -out public.key
```

or use your passphrase if provided on private key generation:

```
openssl rsa -in private.key -passin pass:_passphrase_ -pubout -out public.key
```

The private key must be kept secret (i.e. out of the web-root of the authorization server). The authorization server also requires the public key.

If a passphrase has been used to generate private key it must be provided to the authorization server.

The public key should be distributed to any services (for example resource servers) that validate access tokens.

### Generating encryption keys

Encryption keys are used to encrypt authorization and refresh codes. The AuthorizationServer accepts two kinds of encryption keys, a string password or a \Defuse\Crypto\Key object from [the Secure PHP Encryption Library](https://github.com/defuse/php-encryption).

#### string password

To generate a string password for the AuthorizationServer, you can run the following command in the terminal:

```
php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'
```

#### Key object

A \Defuse\Crypto\Key can be generated with the generate-defuse-key script. To generate a Key for the AuthorizationServer run the following command in the terminal:

```
vendor/bin/generate-defuse-key
```

The string can be loaded as a Key with `Key::loadFromAsciiSafeString($string)`. For example:

```
  $server = new AuthorizationServer(
      $clientRepository,
      $accessTokenRepository,
      $scopeRepository,
      $privateKeyPath,
      \Defuse\Crypto\Key::loadFromAsciiSafeString($encryptionKey)
);
```
