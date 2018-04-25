<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Template;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

class TemplateUtil
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Return all template files of a particular group as array.
     *
     * @param string $prefix The template name prefix (e.g. "ce_")
     *
     * @return array An array of template names
     *
     * @coversNothing As long as Controller::getTemplateGroup is not testable (ThemeModel…)
     */
    public function getTemplateGroup(string $prefix): array
    {
        // allow twig templates
        $GLOBALS['TL_CONFIG']['templateFiles'] .= ',html.twig';

        return Controller::getTemplateGroup($prefix);
    }

    /**
     * Find a particular template file and return its path.
     *
     * @param string $name   The name of the template
     * @param string $format The file extension
     *
     * @throws \InvalidArgumentException If $strFormat is unknown
     * @throws \RuntimeException         If the template group folder is insecure
     *
     * @return string The path to the template file
     */
    public function getTemplate(string $name, string $format = 'html.twig'): string
    {
        // allow twig templates
        $GLOBALS['TL_CONFIG']['templateFiles'] .= ',html.twig';

        return Controller::getTemplate($name, $format);
    }
}
