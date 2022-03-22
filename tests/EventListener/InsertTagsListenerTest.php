<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\EventListener;

use Contao\Controller;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\EventListener\InsertTagsListener;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Error\LoaderError;

class InsertTagsListenerTest extends ContaoTestCase
{
    public function testReplaceTwigTag()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $twig = $this->createMock(Environment::class);
        $templateUtil = $this->createMock(TemplateUtil::class);
        $controllerMock = $this->mockAdapter(['replaceInsertTags']);

        $contao = $this->mockContaoFramework([Controller::class => $controllerMock]);

        $controllerMock->method('replaceInsertTags')->willReturnArgument(0);

        $eventDispatcher->method('dispatch')->willReturnCallback(function ($name, $event) {
            if (\is_object($name)) {
                return $name;
            }

            return $event;
        });

        $templateUtil->method('getTemplate')->willReturnArgument(0);
        $twig->method('render')->willReturnCallback(
            function ($templateName, $templateData) {
                switch ($templateName) {
                    case 'accessibility_bar_bs4_fa':
                        return 'accessibility';
                }

                switch ($templateData) {
                    case ['name' => 'accessibility']:
                        return 'accessibility';
                }

                throw new LoaderError('Template not found');
            }
        );

        $testInstance = new InsertTagsListener($eventDispatcher, $twig, $templateUtil, $contao);

        $this->assertFalse($testInstance->onReplaceInsertTags('unsuported::accessibility_bar_bs4_fa'));
        $this->assertSame('accessibility', $testInstance->onReplaceInsertTags('twig::accessibility_bar_bs4_fa'));
        $this->assertSame('', $testInstance->onReplaceInsertTags('twig'));
        $this->assertFalse($testInstance->onReplaceInsertTags(''));
        $this->assertSame('accessibility', $testInstance->onReplaceInsertTags('twig::accessibility_bar_bs4_fa::name:accessibility'));
    }
}
