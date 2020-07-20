<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Traits;

use Psr\Http\Message\ResponseInterface;

trait HasResponseTrait
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var ResponseInterface */
    private $response;

    //////////////////////////// GETTERS AND SETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function getResponse() : ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response) : void
    {
        $this->response = $response;
    }
}
