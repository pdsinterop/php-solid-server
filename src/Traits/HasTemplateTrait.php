<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Traits;

use \PHPTAL;

trait HasTemplateTrait
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var PHPTAL */
    private $template;

    //////////////////////////// GETTERS AND SETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function getTemplate() : PHPTAL
    {
        return $this->template;
    }

    /**
     * @param PHPTAL $template
     */
    public function setTemplate(PHPTAL $template) : void
    {
        $this->template = $template;
    }
}
