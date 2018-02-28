<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Image;

use Contao\FilesModel;
use Contao\Image\ImageInterface;
use Contao\Image\Picture;
use Contao\PageModel;
use Contao\System;
use HeimrichHannot\UtilsBundle\Image\Image;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;
use Psr\Log\NullLogger;

class ImageTest extends TestCaseEnvironment
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_FILES_URL')) {
            define('TL_FILES_URL', '');
        }

        if (!defined('TL_ERROR')) {
            define('TL_ERROR', 'ERROR: ');
        }

        if (!isset($GLOBALS['TL_LANGUAGE'])) {
            $GLOBALS['TL_LANGUAGE'] = 'de';
        }

        $container = System::getContainer();

        $utilsContainer = $this->mockAdapter(['isBackend', 'isFrontend']);
        $utilsContainer->method('isBackend')->willReturn(false);
        $utilsContainer->method('isFrontend')->willReturn(true);
        $container->set('huh.utils.container', $utilsContainer);

        $imageAdapter = $this->mockAdapter(['getUrl']);
        $imageAdapter->method('getUrl')->willReturn('data/screenshot.jpg');
        $imageFactoryAdapter = $this->mockAdapter(['create']);
        $imageFactoryAdapter->method('create')->willReturn($imageAdapter);
        $container->set('contao.image.image_factory', $imageFactoryAdapter);

        $imageMock = $this->createMock(ImageInterface::class);
        $pictureMock = new Picture(['src' => $imageMock, 'srcset' => []], []);
        $pictureFactoryAdapter = $this->mockAdapter(['create']);
        $pictureFactoryAdapter->method('create')->willReturn($pictureMock);
        $container->set('contao.image.picture_factory', $pictureFactoryAdapter);
        System::setContainer($container);
    }

    public function testAddToTemplateDataWithoutModel()
    {
        $templateData = [];
        $imageArray['imagemargin'] = 'a:5:{s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:5:"right";s:0:"";s:3:"top";s:0:"";s:4:"unit";s:0:"";}';
        $imageArray['singleSRC'] = 'data/screenshot.jpg';
        $imageArray['size'] = 'a:3:{i:0;s:0:"2";i:1;s:0:"2";i:2;s:0:"2";}';
        $imageArray['alt'] = '';
        $imageArray['fullsize'] = true;
        $imageArray['floating'] = false;
        $imageArray['imageUrl'] = 'data/screenshot.jpg';
        $imageArray['imageTitle'] = 'imageTitle';
        $imageArray['linkTitle'] = false;
        $imageArray['id'] = 12;

        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $image = new Image();
        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray);

        $this->assertNotSame(['href' => true, 'singleSRC' => []], $templateData);
        $this->assertSame('data/screenshot.jpg', $templateData['singleSRC']);
        $this->assertSame('imageTitle', $templateData['linkTitle']);
        $this->assertSame('data/screenshot.jpg', $templateData['imageHref']);
        $this->assertSame(' data-lightbox="5dc05b"', $templateData['attributes']);
    }

    public function testAddToTemplateDataWithModel()
    {
        $GLOBALS['TL_LANG']['MSC']['deleteConfirmFile'] = 'delete';
        $templateData = [];
        global $objPage;

        $objPage = $this->mockClassWithProperties(PageModel::class, ['language' => 'de', 'rootFallbackLanguage' => 'de']);

        $GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'] = ['title' => 'maxlenght="255"', 'alt' => 'maxlenght="255"', 'link' => 'maxlenght="255"', 'caption' => 'maxlenght="255"'];

        $imageArray['imagemargin'] = 'a:5:{s:6:"bottom";i:10;s:4:"left";i:10;s:5:"right";i:10;s:3:"top";i:10;s:4:"unit";s:2:"px";}';
        $imageArray['singleSRC'] = 'data/screenshot.jpg';
        $imageArray['size'] = 'a:3:{i:0;s:0:"2";i:1;s:0:"2";i:2;s:0:"2";}';
        $imageArray['alt'] = '';
        $imageArray['fullsize'] = true;
        $imageArray['imageUrl'] = 'data/screenshot.jpg';
        $imageArray['linkTitle'] = 'linkTitle';
        $imageArray['floating'] = 'floating';
        $imageArray['overwriteMeta'] = false;
        $imageArray['caption'] = [];
        $imageArray['id'] = 12;
        $imageArray['imageTitle'] = 'imageTitle';

        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $model = $this->mockClassWithProperties(FilesModel::class, ['meta' => 'a:1:{s:2:"de";a:4:{s:5:"title";s:9:"Diebstahl";s:3:"alt";s:0:"";s:4:"link";s:0:"";s:7:"caption";s:209:"Ob Stifte, Druckerpapier oder Büroklammern: Jeder vierte Arbeitnehmer lässt im Büro etwas mitgehen. Doch egal, wie günstig die gestohlenen Gegenstände sein mögen: Eine Abmahnung ist gerechtfertigt.";}}']);

        $image = new Image();
        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 400, null, null, $model);

        $this->assertNotSame(['href' => true, 'singleSRC' => []], $templateData);
        $this->assertSame('data/screenshot.jpg', $templateData['singleSRC']);
        $this->assertSame('margin:10px;', $templateData['margin']);
        $this->assertSame('Diebstahl', $templateData['imageTitle']);
        $this->assertSame(' float_floating', $templateData['floatClass']);
    }

    public function testAddToTemplateDataError()
    {
        $container = System::getContainer();
        $exception = new \Exception();
        $pictureFactoryAdapter = $this->mockAdapter(['create']);
        $pictureFactoryAdapter->method('create')->willThrowException($exception);
        $container->set('contao.image.picture_factory', $pictureFactoryAdapter);
        System::setContainer($container);

        // suppress Image xyz could not be processed from Image::addToTemplateData()
        System::getContainer()->set('monolog.logger.contao', new NullLogger());

        $templateData = [];
        global $objPage;

        $objPage = $this->mockClassWithProperties(PageModel::class, ['language' => 'de', 'rootFallbackLanguage' => 'de']);

        $GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'] = ['title' => 'maxlenght="255"', 'alt' => 'maxlenght="255"', 'link' => 'maxlenght="255"', 'caption' => 'maxlenght="255"'];

        $imageArray['imagemargin'] = 'a:5:{s:6:"bottom";i:10;s:4:"left";i:10;s:5:"right";i:10;s:3:"top";i:10;s:4:"unit";s:2:"px";}';
        $imageArray['singleSRC'] = 'data/screenshot.jpg';
        $imageArray['size'] = 'a:3:{i:0;s:0:"2";i:1;s:0:"2";i:2;s:0:"2";}';
        $imageArray['alt'] = '';
        $imageArray['fullsize'] = true;
        $imageArray['floating'] = false;
        $imageArray['imageUrl'] = 'data/screenshot.jpg';
        $imageArray['linkTitle'] = 'linkTitle';
        $imageArray['overwriteMeta'] = false;
        $imageArray['caption'] = [];
        $imageArray['id'] = 12;
        $imageArray['imageTitle'] = 'imageTitle';

        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $model = $this->mockClassWithProperties(FilesModel::class, ['meta' => 'a:1:{s:2:"de";a:4:{s:5:"title";s:9:"Diebstahl";s:3:"alt";s:0:"";s:4:"link";s:0:"";s:7:"caption";s:209:"Ob Stifte, Druckerpapier oder Büroklammern: Jeder vierte Arbeitnehmer lässt im Büro etwas mitgehen. Doch egal, wie günstig die gestohlenen Gegenstände sein mögen: Eine Abmahnung ist gerechtfertigt.";}}']);

        $image = new Image();
        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 4, 12, 'lightBoxName', $model);
        $this->assertSame('', $templateData['src']);
        $this->assertSame('margin-top:10px;margin-bottom:10px;', $templateData['margin']);

        $templateData = [];
        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $imageArray['singleSRC'] = '';
        $imageArray['overwriteMeta'] = true;
        $imageArray['fullsize'] = true;
        $imageArray['imageUrl'] = 'data/screensho';

        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 400, 12, 'lightBoxName', $model);
        $this->assertNull($templateData['width']);
        $this->assertNull($templateData['height']);
        $this->assertSame(' target="_blank"', $templateData['attributes']);

        $container = System::getContainer();
        $utilsContainer = $this->mockAdapter(['isBackend', 'isFrontend']);
        $utilsContainer->method('isBackend')->willReturn(true);
        $utilsContainer->method('isFrontend')->willReturn(false);
        $container->set('huh.utils.container', $utilsContainer);
        System::setContainer($container);

        $templateData = [];
        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $imageArray['imageUrl'] = 'data/screenshot.jpg';
        $imageArray['singleSRC'] = 'data/screenshot.jpg';
        $imageArray['size'] = 12;

        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 4, 12, 'lightBoxName', $model);
        $this->assertSame('', $templateData['margin']);
    }
}
