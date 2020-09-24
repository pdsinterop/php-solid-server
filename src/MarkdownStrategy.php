<?php declare(strict_types=1);

namespace Pdsinterop\Solid;

use League\CommonMark\MarkdownConverterInterface;
use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Route;
use League\Route\Strategy\StrategyInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class MarkdownStrategy implements StrategyInterface
{

    /** @var MarkdownConverterInterface */
    private $converter;

    /** @var ResponseInterface */
    private $response;

    /**
     * @param ResponseInterface $response
     * @param MarkdownConverterInterface $converter
     */
    final public function __construct(ResponseInterface $response, MarkdownConverterInterface $converter)
    {
        $this->converter = $converter;
        $this->response = $response;
    }

    /**
     * Invoke the route callable based on the strategy
     *
     * @param Route                  $route
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable();

        $response = $controller($request, $route->getVars());

        $message = $this->converter->convertToHtml($response);

        $body = $this->response->getBody();

        $body->write($message);

        return $this->response->withBody($body);
    }

    /**
     * Get a middleware that will decorate a NotFoundException
     *
     * @param NotFoundException $exception
     *
     * @return MiddlewareInterface
     */
    public function getNotFoundDecorator(NotFoundException $exception): MiddlewareInterface
    {
        return $this->buildResponseMiddleware($exception);
    }

    /**
     * Get a middleware that will decorate a NotAllowedException
     *
     * @param MethodNotAllowedException $exception
     *
     * @return MiddlewareInterface
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception): MiddlewareInterface
    {
        return $this->buildResponseMiddleware($exception);
    }

    /**
     * Get a middleware that will act as an exception handler
     *
     * The middleware must wrap the rest of the middleware stack and catch any
     * thrown exceptions.
     *
     * @return MiddlewareInterface
     */
    public function getExceptionHandler(): MiddlewareInterface
    {
        return $this->getThrowableHandler();
    }

    /**
     * Get a middleware that acts as a throwable handler, it should wrap the rest of the
     * middleware stack and catch any throwables.
     *
     * @return MiddlewareInterface
     */
    public function getThrowableHandler(): MiddlewareInterface
    {
        return new class ($this->response) implements MiddlewareInterface
        {
            protected $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                try {
                    return $requestHandler->handle($request);
                } catch (Throwable $exception) {
                    $response = $this->response;

                    $response->getBody()->write($exception->getMessage());

                    return $response->withStatus(500, strtok($exception->getMessage(), "\n"));
                }
            }
        };
    }

    private function buildResponseMiddleware(\Exception $exception): MiddlewareInterface
    {
        return new class ($this->response, $exception) implements MiddlewareInterface
        {
            protected $response;
            protected $exception;

            public function __construct(ResponseInterface $response, \Exception $exception)
            {
                $this->response  = $response;
                $this->exception = $exception;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                $response = $this->response;

                $response->getBody()->write($this->exception->getMessage());

                return $response->withStatus(500, strtok($this->exception->getMessage(), "\n"));
            }
        };
    }
}
