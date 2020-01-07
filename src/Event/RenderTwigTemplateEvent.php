<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class RenderTwigTemplateEvent extends Event
{
    const NAME = 'huh.utils.template.render';

    /**
     * The name of the twig template.
     *
     * @var string
     */
    protected $template;

    /**
     * The context template data.
     *
     * @var array
     */
    protected $context = [];

    public function __construct(string $template, array $context = [])
    {
        $this->template = $template;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }
}
