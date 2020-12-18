<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Pdsinterop\Solid\Resources\Server;
use Pdsinterop\Solid\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Pdsinterop\Solid\Auth\Utils\DPop as DPop;
use Pdsinterop\Solid\Auth\WAC as WAC;

class ResourceController extends AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var Server */
    private $server;
	private $DPop;
	private $WAC;
    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function __construct(Server $server)
    {
        $this->server = $server;
		$this->DPop = new DPop();
		$this->WAC = new WAC($server->getFilesystem());
    }

    final public function __invoke(Request $request, array $args) : Response
    {
		try {
			$webId = $this->DPop->getWebId($request);
		} catch(\Exception $e) {
			return $this->server->getResponse()->withStatus(409, "Invalid token");
		}
		if (!$this->WAC->isAllowed($request, $webId)) {
			return $this->server->getResponse()->withStatus(403, "Access denied");
		}

		$response = $this->server->respondToRequest($request);
		$response = $this->WAC->addWACHeaders($request, $response, $webId);
		
        return $response;
    }
}
