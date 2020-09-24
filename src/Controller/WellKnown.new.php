<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Pdsinterop\Solid\Auth\Server;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;

class WellKnown_new extends AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var Server */
    private $server;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    public function __construct(Server $server)
    {
        $this->server = $server;
    }


    final public function __invoke(ServerRequestInterface $request, array $args) : Response
    {
        $uri = $request->getUri();

        $domain = vsprintf('%s://%s', [$uri->getScheme(),$uri->getHost()]);

        $file = $args['subject'];

        switch ($file) {
            case 'openid-configuration':
                $response = $this->createOpenidConfigurationResponse($domain, $request);
                break;

            case 'security.txt':
                $response = $this->createSecurityTextResponse();
                break;

            default:
                /** @noinspection HtmlUnknownTarget */
                $response = $this->createTextResponse(<<<HTML
                    <ul>
                        <li><a href="./openid-configuration">openid-configuration</a></li>
                        <li><a href="./security.txt">security.txt</a></li>
                    </ul>
HTML
                );
                break;
        }

        return $response;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private function createOpenidConfigurationResponse() : Response
    {
        return $this->server->respondToWellKnownRequest();
    }

    private function createSecurityTextResponse() : Response
    {
        $data = <<<'TXT'
# Thank you for your interest in the security of PDS Interop
# Please report security issues responsibly!
Contact: security@pdsinterop.org
TXT;

        return $this->createTextResponse($data)
            ->withHeader('content-type', 'text/plain; charset=UTF-8')
        ;
    }
}
