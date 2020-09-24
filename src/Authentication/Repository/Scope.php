<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Authentication\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class Scope implements ScopeRepositoryInterface
{
    use \League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
    use \League\OAuth2\Server\Entities\Traits\ScopeTrait;
    use \League\OAuth2\Server\CryptTrait;

    /**
     * ScopeRepository constructor.
     */
    public function __construct()
    {
    }

    public function getScopeEntityByIdentifier($identifier)
    {
        // TODO: Implement getScopeEntityByIdentifier() method.
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        // TODO: Implement finalizeScopes() method.
    }
}
