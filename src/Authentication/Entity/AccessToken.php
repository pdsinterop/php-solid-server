<?php

namespace Pdsinterop\Solid\Authentication\Entity;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

class AccessToken implements AccessTokenEntityInterface
{
    use \League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
    use \League\OAuth2\Server\Entities\Traits\EntityTrait;
    use \League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
}
