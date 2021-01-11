<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\EventListener;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Event\RenderTwigTemplateEvent;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class InsertTagsListener
{
    /**
     * @var array
     */
    private $supportedTags = [
        'twig',
    ];
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var TemplateUtil
     */
    private $templateUtil;
    /**
     * @var ContaoFrameworkInterface
     */
    private $contaoFramework;

    /**
     * Constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Environment $twig, TemplateUtil $templateUtil, ContaoFrameworkInterface $contaoFramework)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
        $this->templateUtil = $templateUtil;
        $this->contaoFramework = $contaoFramework;
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

        $template = $this->templateUtil->getTemplate(preg_replace('#.html.twig$#i', '', $attributes[0]));

        $event = $this->eventDispatcher->dispatch(
            RenderTwigTemplateEvent::NAME,
            new RenderTwigTemplateEvent(
                $template, $data
            )
        );

        return $this->contaoFramework->getAdapter(Controller::class)->replaceInsertTags($this->twig->render($event->getTemplate(), $event->getContext()));
    }
}
