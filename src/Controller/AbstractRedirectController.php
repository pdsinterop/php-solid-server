<?php

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRedirectController extends AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var array */
    private $args;

    /** @var ServerRequestInterface */
    private $request;
    //////////////////////////// GETTERS AND SETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @return string  */
    final public function getPath() : string
    {
        $target = $this->request->getRequestTarget();

        [$path, $query] = explode('?', $target);

        return $path;
    }

    /** @return string */
    final public function getQuery() : string
    {
        $target = $this->request->getRequestTarget();

        [$url, $query] = explode('?', $target);

        if (is_string($query)) {
            $query .= '?' . $query;
        }


        return (string) $query;
    }

    abstract public function getTargetUrl(): string;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->args = $args;

        return $this->createRedirectResponse($this->getTargetUrl());
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

}
