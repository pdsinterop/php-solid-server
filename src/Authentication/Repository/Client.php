<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Authentication\Repository;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class Client implements ClientRepositoryInterface
{
    /**
     * ClientRepository constructor.
     */
    public function __construct()
    {
    }

    public function getClientEntity($clientIdentifier)
    {
        // TODO: Implement getClientEntity() method.
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        // TODO: Implement validateClient() method.
    }
}
