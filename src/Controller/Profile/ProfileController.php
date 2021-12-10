<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller\Profile;

use League\Flysystem\FilesystemInterface;
use Pdsinterop\Rdf\Enum\Format;
use Pdsinterop\Solid\Controller\AbstractController;
use Pdsinterop\Solid\Traits\HasFilesystemTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController extends AbstractController
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    use HasFilesystemTrait;

    /** @var ServerRequestInterface */
    private $request;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\


    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        $this->request = $request;

        $filesystem = $this->getFilesystem();

        $formats = Format::keys();
        $contents = $this->fetchFileContents($filesystem, $formats);

        return $this->createTemplateResponse('card.html', [
            'files' => $contents,
            'formats' => $formats,
        ]);
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @param FilesystemInterface $filesystem
     * @param array string[]
     *
     * @return string[]
     */
    private function fetchFileContents(FilesystemInterface $filesystem, array $formats) : array
    {
        $contents = [];

        $serverParams = $this->request->getServerParams();
        $url = $serverParams["REQUEST_URI"] ?? '';

        array_walk($formats, static function ($format, $index) use (&$contents, $filesystem, $url) {
            /** @noinspection PhpUndefinedMethodInspection */ // Method `readRdf` is defined by plugin
            $contents[$index] = $filesystem->readRdf('/foaf.rdf', $format, $url);
        });

        return $contents;
    }
}
