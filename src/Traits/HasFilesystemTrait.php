<?php

namespace Pdsinterop\Solid\Traits;

use League\Flysystem\FilesystemInterface;

trait HasFilesystemTrait
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var FilesystemInterface $filesystem */
    private $filesystem;

    //////////////////////////// GETTERS AND SETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @return FilesystemInterface
     */
    final public function getFilesystem() : FilesystemInterface
    {
        return $this->filesystem;
    }

    /**
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem) : void
    {
        $this->filesystem = $filesystem;
    }
}
