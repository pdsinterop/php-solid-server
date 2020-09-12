<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller\Profile;

use League\Route\Http\Exception\NotFoundException;
use Pdsinterop\Rdf\Enum\Format;
use Pdsinterop\Solid\Controller\AbstractController;
use Pdsinterop\Solid\Traits\HasFilesystemTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class CardController extends AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    use HasFilesystemTrait;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     *
     * @return ResponseInterface
     *
     * @throws NotFoundException
     */
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // @FIXME: Target file is hard-coded for not, replace with path from $request->getRequestTarget()
        $filePath = '/foaf.rdf';
        $filesystem = $this->getFilesystem();
        $extension = '.ttl';

        // @TODO: Content negotiation from Accept headers
        //$format = $request->getHeader('Accept');

        if (array_key_exists('extension', $args)) {
            $extension = $args['extension'];
        }

        $format = $this->getFormatForExtension($extension);

        if (in_array($format, Format::keys()) === false) {
            throw new NotFoundException($request->getRequestTarget());
        }

        $contentType = $this->getContentTypeForFormat($format);

        /** @noinspection PhpUndefinedMethodInspection */ // Method `readRdf` is defined by plugin
        $content = $filesystem->readRdf($filePath, $format);

        return $this->createTextResponse($content)->withHeader('Content-Type', $contentType);
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @param string $format
     *
     * @return string
     */
    private function getContentTypeForFormat(string $format) : string
    {
        $contentType = '';

        switch ($format) {
            case Format::JSON_LD:
                $contentType = 'application/ld+json';
                break;

            case Format::N_TRIPLES:
                $contentType = 'application/n-triples';
                break;

            case Format::NOTATION_3:
                $contentType = 'text/n3;charset=utf-8';
                break;

            case Format::RDF_XML:
                $contentType = 'application/rdf+xml';
                break;

            case Format::TURTLE:
                $contentType = 'text/turtle';
                break;

            default:
                break;
        }

        return $contentType;
    }

    /**
     * @param string $extension
     *
     * @return string
     */
    private function getFormatForExtension(string $extension) : string
    {
        $format = '';

        switch ($extension) {
            case '.jsonld':
                $format = Format::JSON_LD;
                break;

            case '.nt':
                $format = Format::N_TRIPLES;
                break;

            case '.n3':
                $format = Format::NOTATION_3;
                break;

            case '.rdf':
                $format = Format::RDF_XML;
                break;

            case '.ttl':
                $format = Format::TURTLE;
                break;

            default:
                break;
        }

        return $format;
}
}
