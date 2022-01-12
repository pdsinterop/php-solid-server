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

    public function setTemplate(PHPTAL $template) : void
    {
        $this->template = $template;
    }

    public function buildTemplate(string $template, array $context) : string
    {
        $engine = $this->getTemplate();

        $templateExists = $this->isTemplate($template, $engine);

        if ($templateExists === true) {
            $engine->setTemplate($template);
        } else {
            $template = $this->createTemplateFromString($template);

            $engine->setSource($template);
        }

        $engine = $this->setContextOnTemplate($context, $engine);

        return $engine->execute();
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private function createTemplateFromString(string $template) : string
    {
        return <<<"TAL"
<tal:block metal:use-macro="default.html/default">
    <main class="container section content box" metal:fill-slot="content">
    {$template}
    </main>
</tal:block>
TAL;
    }

    private function isTemplate(string $template, \PHPTAL $engine) : bool
    {
        $templateExists = array_map(static function ($templateRepository) use ($template) {
            return file_exists($templateRepository . '/' . ltrim($template, '/'));
        }, $engine->getTemplateRepositories());

        return array_sum($templateExists) > 0;
    }

    private function setContextOnTemplate(array $context, \PHPTAL $engine) : \PHPTAL
    {
        array_walk($context, static function ($value, $name) use (&$engine) {
            $engine->set($name, $value);
        });

        return $engine;
    }
}
