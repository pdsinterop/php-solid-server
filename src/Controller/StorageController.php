<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StorageController extends ServerController
{    
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {	
$body = <<< EOF
@prefix : <#>.
@prefix inbox: <>.
@prefix ldp: <http://www.w3.org/ns/ldp#>.
@prefix terms: <http://purl.org/dc/terms/>.
@prefix XML: <http://www.w3.org/2001/XMLSchema#>.
@prefix st: <http://www.w3.org/ns/posix/stat#>.

inbox:
    a ldp:BasicContainer, ldp:Container, ldp:Resource;
    terms:modified "2019-12-20T14:52:54Z"^^XML:dateTime;
    st:mtime 1576853574.389;
    st:size 4096.
EOF;
		$response = $this->getResponse();
		
		$response->getBody()->write($body);
		return $response
			->withHeader("Content-type", "text/turtle");
    }
}
