<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

class AddSlashToPathController extends AbstractRedirectController
{
    /** @return string */
    public function getTargetUrl() : string
    {
        return $this->getPath() . '/' . $this->getQuery();
    }
}
