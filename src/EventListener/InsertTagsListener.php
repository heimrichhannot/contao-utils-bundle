<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EventListener;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use Contao\System;

class InsertTagsListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $supportedTags = [
        'twig',
    ];

    /**
     * Constructor.
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Replaces calendar insert tags.
     *
     * @return string|false
     */
    public function onReplaceInsertTags(string $tag)
    {
        $elements = explode('::', $tag);
        $key = strtolower($elements[0]);
        $attributes = \array_slice($elements, 1);

        if (\in_array($key, $this->supportedTags)) {
            return $this->replaceSupportedTags($key, $attributes);
        }

        return false;
    }

    /**
     * Replace supported tags.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function replaceSupportedTags(string $key, array $attributes = []): string
    {
        switch ($key) {
            case 'twig':
                return $this->replaceTwigTag($attributes);
        }

        return '';
    }

    /**
     * Replace twig template insert tags {{twig::logo.html.twig::a:1:{s:3:"foo";s:3:"bar";}}}.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function replaceTwigTag(array $attributes = []): string
    {
        if (!isset($attributes[0]) || empty($attributes[0])) {
            return '';
        }

        $data = [];

        if (isset($attributes[1]) && !empty($attributes[1])) {
            $data = StringUtil::deserialize($attributes[1], true);
        }

        $template = System::getContainer()->get('huh.utils.template')->getTemplate(preg_replace('#.html.twig$#i', '', $attributes[0]));

        return Controller::replaceInsertTags(System::getContainer()->get('twig')->render($template, $data));
    }
}
