<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller\Profile;

use Pdsinterop\Solid\Controller\AbstractRedirectController;

class ProfileController extends AbstractRedirectController
{
    /** @return string */
    public function getTargetUrl() : string
    {
        return $this->getPath() . 'card' . $this->getQuery();
    }
}
