<?php declare(strict_types=1);

namespace Pdsinterop\Solid;

use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use League\Route\Http\Exception as HttpException;
use League\Route\Http\Exception\NotFoundException;

class ExceptionResponse
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private const MESSAGE_GENERIC_ERROR = 'Yeah, that\'s an error.';
    private const MESSAGE_NO_SUCH_PAGE = 'No such page.';

    /** @var Exception */
    private $exception;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    final public function createResponse() : HtmlResponse
    {
        $exception = $this->exception;

        if ($exception instanceof HttpException) {
            $response = $this->respondToHttpException($exception);
        } else {
            $response = $this->responseToException($exception);
        }

        return $response;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private function isDevelop() : bool
    {
        static $isDevelop;

        if ($isDevelop === null) {
            $isDevelop = getenv('ENVIRONMENT') === 'development';
        }

        return $isDevelop;
    }

    private function responseToException(Exception $exception) : HtmlResponse
    {
        $html = "<h1>Oh-no! The developers messed up!</h1><p>{$exception->getMessage()}</p>";

        if ($this->isDevelop()) {
            $html .=
                "<p>{$exception->getFile()}:{$exception->getLine()}</p>" .
                "<pre>{$exception->getTraceAsString()}</pre>";
        }

        return new HtmlResponse($html, 500, []);
    }

    private function respondToHttpException(HttpException $exception) : HtmlResponse
    {
        $status = $exception->getStatusCode();

        $message = self::MESSAGE_GENERIC_ERROR;

        if ($exception instanceof NotFoundException) {
            $message = self::MESSAGE_NO_SUCH_PAGE;
        }

        $html = vsprintf('<h1>%s</h1><p>%s (%s)</p>', [
            $message,
            $exception->getMessage(),
            $status,
        ]);

        if ($this->isDevelop()) {
            $html .= "<pre>{$exception->getTraceAsString()}</pre>";
        }

        return new HtmlResponse($html, $status, $exception->getHeaders());
    }
}
