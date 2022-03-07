<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Dca;

use Contao\Controller;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Dca\DcaUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class DcaUtilTest extends AbstractUtilsTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testGetPaletteFields(): void
    {
        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $contaoFramework = $this->mockContaoFramework([
            Controller::class => $controllerAdapter,
        ]);

        $instance = $this->getTestInstance([
            'contaoFramework' => $contaoFramework,
        ]);

        $GLOBALS['TL_DCA']['tl_content'] = [
            'palettes' => [
                '__selector__' => ['addImage', 'invisible'],
                'default' => '{type_legend},type',
                'text' => '{type_legend},type,headline;{text_legend},text;{image_legend},addImage;{invisible_legend:hide},invisible',
            ],
            'subpalettes' => [
                'addImage' => 'singleSRC',
            ],
        ];

        $this->assertCount(1, $instance->getPaletteFields('tl_content', 'default'));
        $this->assertCount(6, $instance->getPaletteFields('tl_content', 'text'));
    }

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework();

        return new DcaUtil($contaoFramework);
    }
}
