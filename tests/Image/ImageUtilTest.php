<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Image;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\FilesModel;
use Contao\Image\ImageInterface;
use Contao\Image\Picture;
use Contao\PageModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Tests\FixturesTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageUtilTest extends ContaoTestCase
{
    use FixturesTrait;

    public function setUp()
    {
        parent::setUp();

        if (!\defined('TL_FILES_URL')) {
            \define('TL_FILES_URL', '');
        }

        if (!\defined('TL_ERROR')) {
            \define('TL_ERROR', 'ERROR: ');
        }

        if (!isset($GLOBALS['TL_LANGUAGE'])) {
            $GLOBALS['TL_LANGUAGE'] = 'de';
        }

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir().'/files');

        copy(
            $this->getFixturesDir().'/files/screenshot.png',
            $this->getTempDir().'/files/screenshot.png'
        );
    }

    public function testAddToTemplateDataWithoutModel()
    {
        $templateData = [];
        $imageArray['imagemargin'] = 'a:5:{s:6:"bottom";s:0:"";s:4:"left";s:0:"";s:5:"right";s:0:"";s:3:"top";s:0:"";s:4:"unit";s:0:"";}';
        $imageArray['singleSRC'] = 'de28ed4c-2eb5-11e9-ac5f-a08cfddc0261';
        $imageArray['size'] = 'a:3:{i:0;s:0:"2";i:1;s:0:"2";i:2;s:0:"2";}';
        $imageArray['alt'] = '';
        $imageArray['fullsize'] = true;
        $imageArray['floating'] = false;
        $imageArray['imageUrl'] = $this->getTempDir().'/files/screenshot.png';
        $imageArray['imageTitle'] = 'imageTitle';
        $imageArray['linkTitle'] = false;
        $imageArray['id'] = 12;

        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $container = $this->getContainerMock();
        $monologLoggerMock = $this->mockAdapter(['log']);
        $monologLoggerMock->method('log');
        $container->set('monolog.logger.contao', $monologLoggerMock);

        System::setContainer($container);
        $image = new ImageUtil($container);
        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray);

        $this->assertNotSame(['href' => true, 'singleSRC' => []], $templateData);
        $this->assertSame($this->getTempDir().'/files/screenshot.png', $templateData['singleSRC']);
        $this->assertSame('imageTitle', $templateData['linkTitle']);
        $this->assertSame($this->getTempDir().'/files/screenshot.png', $templateData['imageHref']);
        $this->assertSame(' data-lightbox="5dc05b"', $templateData['attributes']);

        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray);
    }

    public function testAddToTemplateDataWithModel()
    {
        $GLOBALS['TL_LANG']['MSC']['deleteConfirmFile'] = 'delete';
        $templateData = [];
        global $objPage;

        $objPage = $this->mockClassWithProperties(PageModel::class, ['language' => 'de', 'rootFallbackLanguage' => 'de']);

        $GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'] = ['title' => 'maxlenght="255"', 'alt' => 'maxlenght="255"', 'link' => 'maxlenght="255"', 'caption' => 'maxlenght="255"'];

        $imageArray['imagemargin'] = 'a:5:{s:6:"bottom";i:10;s:4:"left";i:10;s:5:"right";i:10;s:3:"top";i:10;s:4:"unit";s:2:"px";}';
        $imageArray['singleSRC'] = 'de28ed4c-2eb5-11e9-ac5f-a08cfddc0261';
        $imageArray['size'] = 'a:3:{i:0;s:0:"2";i:1;s:0:"2";i:2;s:0:"2";}';
        $imageArray['alt'] = '';
        $imageArray['fullsize'] = true;
        $imageArray['imageUrl'] = $this->getTempDir().'/files/screenshot.png';
        $imageArray['linkTitle'] = 'linkTitle';
        $imageArray['floating'] = 'floating';
        $imageArray['overwriteMeta'] = false;
        $imageArray['caption'] = [];
        $imageArray['id'] = 12;
        $imageArray['imageTitle'] = 'imageTitle';

        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $model = $this->mockClassWithProperties(FilesModel::class, ['meta' => 'a:1:{s:2:"de";a:4:{s:5:"title";s:9:"Diebstahl";s:3:"alt";s:0:"";s:4:"link";s:0:"";s:7:"caption";s:209:"Ob Stifte, Druckerpapier oder Büroklammern: Jeder vierte Arbeitnehmer lässt im Büro etwas mitgehen. Doch egal, wie günstig die gestohlenen Gegenstände sein mögen: Eine Abmahnung ist gerechtfertigt.";}}']);

        $container = $this->getContainerMock();
        $monologLoggerMock = $this->mockAdapter(['log']);
        $monologLoggerMock->method('log');
        $container->set('monolog.logger.contao', $monologLoggerMock);

        $image = new ImageUtil($container);
        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 400, null, null, $model);

        $this->assertNotSame(['href' => true, 'singleSRC' => []], $templateData);
        $this->assertSame($this->getTempDir().'/files/screenshot.png', $templateData['singleSRC']);
        $this->assertSame('margin:10px;', $templateData['margin']);
        $this->assertSame('Diebstahl', $templateData['imageTitle']);
        $this->assertSame(' float_floating', $templateData['floatClass']);
    }

    public function testAddToTemplateDataError()
    {
        $container = $this->getContainerMock();
        $exception = new \Exception();
        $pictureFactoryAdapter = $this->mockAdapter(['create']);
        $pictureFactoryAdapter->method('create')->willThrowException($exception);
        $container->set('contao.image.picture_factory', $pictureFactoryAdapter);

        $templateData = [];
        global $objPage;

        $objPage = $this->mockClassWithProperties(PageModel::class, ['language' => 'de', 'rootFallbackLanguage' => 'de']);

        $GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'] = ['title' => 'maxlenght="255"', 'alt' => 'maxlenght="255"', 'link' => 'maxlenght="255"', 'caption' => 'maxlenght="255"'];

        $imageArray['imagemargin'] = 'a:5:{s:6:"bottom";i:10;s:4:"left";i:10;s:5:"right";i:10;s:3:"top";i:10;s:4:"unit";s:2:"px";}';
        $imageArray['singleSRC'] = 'de28ed4c-2eb5-11e9-ac5f-a08cfddc0261';
        $imageArray['size'] = 'a:3:{i:0;s:0:"2";i:1;s:0:"2";i:2;s:0:"2";}';
        $imageArray['alt'] = '';
        $imageArray['fullsize'] = true;
        $imageArray['floating'] = false;
        $imageArray['imageUrl'] = 'files/screenshot.png';
        $imageArray['linkTitle'] = 'linkTitle';
        $imageArray['overwriteMeta'] = false;
        $imageArray['caption'] = [];
        $imageArray['id'] = 12;
        $imageArray['imageTitle'] = 'imageTitle';

        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $model = $this->mockClassWithProperties(FilesModel::class, ['meta' => 'a:1:{s:2:"de";a:4:{s:5:"title";s:9:"Diebstahl";s:3:"alt";s:0:"";s:4:"link";s:0:"";s:7:"caption";s:209:"Ob Stifte, Druckerpapier oder Büroklammern: Jeder vierte Arbeitnehmer lässt im Büro etwas mitgehen. Doch egal, wie günstig die gestohlenen Gegenstände sein mögen: Eine Abmahnung ist gerechtfertigt.";}}']);

        $monologLoggerMock = $this->mockAdapter(['log']);
        $monologLoggerMock->expects($this->once())->method('log');
        $container->set('monolog.logger.contao', $monologLoggerMock);

        System::setContainer($container);
        $image = new ImageUtil($container);
        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 4, 12, 'lightBoxName', $model);
        $this->assertSame('', $templateData['src']);
        $this->assertSame('margin-top:10px;margin-bottom:10px;', $templateData['margin']);

        $templateData = [];
        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $imageArray['singleSRC'] = '';
        $imageArray['overwriteMeta'] = true;
        $imageArray['fullsize'] = true;
        $imageArray['imageUrl'] = 'files/screensho';

        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 400, 12, 'lightBoxName', $model);
        $this->assertArrayNotHasKey('width', $templateData);
        $this->assertArrayNotHasKey('height', $templateData);
        $this->assertArrayNotHasKey('attributes', $templateData);

        $utilsContainer = $this->mockAdapter(['isBackend', 'isFrontend']);
        $utilsContainer->method('isBackend')->willReturn(true);
        $utilsContainer->method('isFrontend')->willReturn(false);
        $container->set('huh.utils.container', $utilsContainer);

        $image = new ImageUtil($container);

        $templateData = [];
        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $imageArray['imageUrl'] = $this->getTempDir().'/files/screenshot.png';
        $imageArray['singleSRC'] = $this->getTempDir().'/files/screenshot.png';
        $imageArray['size'] = 12;

        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 4, 12, 'lightBoxName', $model);
        $this->assertArrayNotHasKey('margin', $templateData);
    }

    public function addImageToTemplateDataHook(
        array $templateData,
        string $imageField,
        string $imageSelectorField,
        array $item,
        int $maxWidth = null,
        string $lightboxId = null,
        string $lightboxName = null,
        FilesModel $model = null
    ) {
        $templateData['picture']['test'] = true;

        return $templateData;
    }

    public function testAddToTemplateDataHook()
    {
        $GLOBALS['TL_HOOKS']['addImageToTemplateData'][] = [static::class, 'addImageToTemplateDataHook'];

        $GLOBALS['TL_LANG']['MSC']['deleteConfirmFile'] = 'delete';
        $templateData = [];
        global $objPage;

        $objPage = $this->mockClassWithProperties(PageModel::class, ['language' => 'de', 'rootFallbackLanguage' => 'de']);

        $GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'] = ['title' => 'maxlenght="255"', 'alt' => 'maxlenght="255"', 'link' => 'maxlenght="255"', 'caption' => 'maxlenght="255"'];

        $imageArray['imagemargin'] = 'a:5:{s:6:"bottom";i:10;s:4:"left";i:10;s:5:"right";i:10;s:3:"top";i:10;s:4:"unit";s:2:"px";}';
        $imageArray['singleSRC'] = 'de28ed4c-2eb5-11e9-ac5f-a08cfddc0261';
        $imageArray['size'] = 'a:3:{i:0;s:0:"2";i:1;s:0:"2";i:2;s:0:"2";}';
        $imageArray['alt'] = '';
        $imageArray['fullsize'] = true;
        $imageArray['imageUrl'] = $this->getTempDir().'/files/screenshot.png';
        $imageArray['linkTitle'] = 'linkTitle';
        $imageArray['floating'] = 'floating';
        $imageArray['overwriteMeta'] = false;
        $imageArray['caption'] = [];
        $imageArray['id'] = 12;
        $imageArray['imageTitle'] = 'imageTitle';

        $templateData['href'] = true;
        $templateData['singleSRC'] = [];

        $model = $this->mockClassWithProperties(FilesModel::class, ['meta' => 'a:1:{s:2:"de";a:4:{s:5:"title";s:9:"Diebstahl";s:3:"alt";s:0:"";s:4:"link";s:0:"";s:7:"caption";s:209:"Ob Stifte, Druckerpapier oder Büroklammern: Jeder vierte Arbeitnehmer lässt im Büro etwas mitgehen. Doch egal, wie günstig die gestohlenen Gegenstände sein mögen: Eine Abmahnung ist gerechtfertigt.";}}']);

        $container = $this->getContainerMock();
        $monologLoggerMock = $this->mockAdapter(['log']);
        $monologLoggerMock->method('log');
        $container->set('monolog.logger.contao', $monologLoggerMock);

        $image = new ImageUtil($container);
        $image->addToTemplateData('singleSRC', 'addImage', $templateData, $imageArray, 400, null, null, $model);

        $this->assertNotSame(['href' => true, 'singleSRC' => []], $templateData);
        $this->assertSame($this->getTempDir().'/files/screenshot.png', $templateData['singleSRC']);
        $this->assertSame('margin:10px;', $templateData['margin']);
        $this->assertSame('Diebstahl', $templateData['imageTitle']);
        $this->assertSame(' float_floating', $templateData['floatClass']);
        $this->assertTrue($templateData['picture']['test']);
    }

    public function testGetPixelValue()
    {
        $class = new ImageUtil($this->getContainerMock());

        $result = $class->getPixelValue('10px');
        $this->assertSame(10, $result);
        $result = $class->getPixelValue('10em');
        $this->assertSame(160, $result);
        $result = $class->getPixelValue('10ex');
        $this->assertSame(80, $result);
        $result = $class->getPixelValue('10pt');
        $this->assertSame(13, $result);
        $result = $class->getPixelValue('10pc');
        $this->assertSame(160, $result);
        $result = $class->getPixelValue('10in');
        $this->assertSame(960, $result);
        $result = $class->getPixelValue('10cm');
        $this->assertSame(378, $result);
        $result = $class->getPixelValue('10mm');
        $this->assertSame(38, $result);
        $result = $class->getPixelValue('10%');
        $this->assertSame(2, $result);
        $result = $class->getPixelValue('10%%%');
        $this->assertSame(0, $result);
    }

    /**
     * @param ContaoFramework $framework
     *
     * @return ContainerBuilder|ContainerInterface
     */
    protected function getContainerMock(ContainerBuilder $container = null, $framework = null)
    {
        if (!$container) {
            $container = $this->mockContainer($this->getTempDir());
        }

        if (!$framework) {
            $controllerAdapter = $this->mockAdapter(['loadDataContainer']);

            $framework = $this->mockContaoFramework([
                Controller::class => $controllerAdapter,
            ]);
        }
        $container->set('contao.framework', $framework);

        $utilsContainer = $this->mockAdapter(['isBackend', 'isFrontend']);
        $utilsContainer->method('isBackend')->willReturn(false);
        $utilsContainer->method('isFrontend')->willReturn(true);
        $container->set('huh.utils.container', $utilsContainer);

        $imageFile = $this->mockClassWithProperties(File::class, [
            'path' => $this->getTempDir().'/files/screenshot.png',
            'imageSize' => [
                800,
                1200,
                0, // replace this with IMAGETYPE_SVG when it becomes available
                'width="'. 1200 .'" height="'. 800 .'"',
                'bits' => 8,
                'channels' => 3,
                'mime' => 'image/png',
            ],
            'extension' => 'png',
        ]);
        $fileUtil = $this->createMock(FileUtil::class);
        $fileUtil->method('getFileFromUuid')->willReturn($imageFile);
        $container->set('huh.utils.file', $fileUtil);

        $imageAdapter = $this->mockAdapter(['getUrl']);
        $imageAdapter->method('getUrl')->willReturn('files/screenshot.png');
        $imageFactoryAdapter = $this->mockAdapter(['create']);
        $imageFactoryAdapter->method('create')->willReturn($imageAdapter);
        $container->set('contao.image.image_factory', $imageFactoryAdapter);

        $imageMock = $this->createMock(ImageInterface::class);
        $pictureMock = new Picture(['src' => $imageMock, 'srcset' => []], []);
        $pictureFactoryAdapter = $this->mockAdapter(['create']);
        $pictureFactoryAdapter->method('create')->willReturn($pictureMock);
        $container->set('contao.image.picture_factory', $pictureFactoryAdapter);

        return $container;
    }
}
