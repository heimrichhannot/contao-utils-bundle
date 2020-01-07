<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

abstract class AbstractChoice
{
    /**
     * Context data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Current file cache.
     *
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * Current cache key name.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Current context.
     *
     * @var mixed
     */
    protected $context;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
        $this->cache = new FilesystemAdapter('', 0, System::getContainer()->get('kernel')->getCacheDir());
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    public function getChoices($context = null)
    {
        if (null !== $context) {
            $this->setContext($context);
        }

        $choices = $this->collect();

        return $choices;
    }

    public function getCachedChoices($context = null)
    {
        if (null !== $context) {
            $this->setContext($context);
        }

        // disable cache while in debug mode or backend
        if (true === System::getContainer()->getParameter('kernel.debug') || System::getContainer()->get('huh.utils.container')->isBackend()) {
            return $this->getChoices($this->getContext());
        }

        $this->cacheKey = 'choice.'.preg_replace('#Choice$#', '', (new \ReflectionClass($this))->getShortName());

        // add unique identifier based on context
        if (null !== $this->getContext() && false !== ($json = json_encode($this->getContext(), JSON_FORCE_OBJECT))) {
            $this->cacheKey .= '.'.sha1($json);
        }

        $cache = $this->cache->getItem($this->cacheKey);

        if (!$cache->isHit() || empty($cache->get())) {
            $choices = $this->getChoices($this->getContext());

            if (!\is_array($choices)) {
                $choices = [];
            }

            // TODO: clear cache on delegated field save_callback
            $cache->expiresAfter(\DateInterval::createFromDateString('4 hour'));
            $cache->set($choices);

            $this->cache->save($cache);
        }

        return $cache->get();
    }

    /**
     * @return array
     */
    abstract protected function collect();
}
