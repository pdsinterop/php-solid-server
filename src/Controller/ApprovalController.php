<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApprovalController extends ServerController
{    
    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
		$clientId = $_GET['client_id'];
		$returnUrl = $_GET['returnUrl'];

        return $this->createTemplateResponse('approval.html', [
            'clientId' => $clientId,
            'returnUrl' => $returnUrl,
        ]);
    }
}
