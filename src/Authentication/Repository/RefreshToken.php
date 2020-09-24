<?php

namespace Pdsinterop\Solid\Authentication\Repository;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

class RefreshToken implements RefreshTokenEntityInterface
{

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        // TODO: Implement getIdentifier() method.
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier($identifier)
    {
        // TODO: Implement setIdentifier() method.
    }

    /**
     * @inheritDoc
     */
    public function getExpiryDateTime()
    {
        // TODO: Implement getExpiryDateTime() method.
    }

    /**
     * @inheritDoc
     */
    public function setExpiryDateTime(DateTimeImmutable $dateTime)
    {
        // TODO: Implement setExpiryDateTime() method.
    }

    /**
     * @inheritDoc
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        // TODO: Implement setAccessToken() method.
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken()
    {
        // TODO: Implement getAccessToken() method.
    }
}
