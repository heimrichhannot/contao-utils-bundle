<?php

namespace HeimrichHannot\UtilsBundle\Tests\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Image;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\BackendUiUtil;
use HeimrichHannot\UtilsBundle\Util\Html\HtmlUtil;
use HeimrichHannot\UtilsBundle\Util\Routing\RoutingUtil;
use HeimrichHannot\UtilsBundle\Util\Ui\PopupWizardLinkOptions;
use PHPUnit\Framework\MockObject\MockBuilder;

class BackendUiUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $parameters['routingUtil'] = $parameters['routingUtil'] ?? $this->createMock(RoutingUtil::class);
        $parameters['framework'] = $parameters['framework'] ?? $this->createMock(ContaoFramework::class);
        $parameters['htmlUtil'] = $parameters['htmlUtil'] ?? new HtmlUtil();

        return new BackendUiUtil($parameters['routingUtil'], $parameters['framework'], $parameters['htmlUtil']);
    }


    public function testPopupWizardLinkReturnsCorrectUrlOnly()
    {
        $routingUtil = $this->createMock(RoutingUtil::class);
        $routingUtil->method('generateBackendRoute')->willReturnArgument(3);

        $backendUiUtil = $this->getTestInstance(['routingUtil' => $routingUtil]);

        $config = new PopupWizardLinkOptions();
        $config->setUrlOnly(true);

        $this->assertEquals(
            'contao_backend',
            $backendUiUtil->popupWizardLink(['param' => 'value'], $config)
        );
        $config->setRoute('utils_backend');
        $this->assertEquals(
            'utils_backend',
            $backendUiUtil->popupWizardLink(['param' => 'value'], $config)
        );
    }

    public function testPopupWizardLinkGeneratesCorrectLink()
    {
        $routingUtil = $this->createMock(RoutingUtil::class);

        $routingUtil->method('generateBackendRoute')->willReturn('generated_url');

        $backendUiUtil = $this->getTestInstance(['routingUtil' => $routingUtil]);

        $config = (new PopupWizardLinkOptions())
            ->setTitle('Test Title')
            ->setStyle('border: 0;')
            ->setPopupTitle('Test Popup Title')
            ->setWidth(800)
            ->setLinkText('Test Link Text');
        ;

        $this->assertStringContainsString(
            '<a href="generated_url" title="Test Title" style="border: 0;" onclick="Backend.openModalIframe({\'width\':800,\'title\':\'Test Popup Title\',\'url\':this.href});return false">Test Link Text</a>',
            $backendUiUtil->popupWizardLink(['param' => 'value'], $config)
        );

        $GLOBALS['TL_LANG']['tl_content']['edit'][0] = 'Edit';
        $config->title = '';
        $this->assertStringContainsString(
            '<a href="generated_url" title="Edit" style="border: 0;" onclick="Backend.openModalIframe({\'width\':800,\'title\':\'Test Popup Title\',\'url\':this.href});return false">Test Link Text</a>',
            $backendUiUtil->popupWizardLink(['param' => 'value'], $config)
        );

        $config->popupTitle = '';
        $this->assertStringContainsString(
            '<a href="generated_url" title="Edit" style="border: 0;" onclick="Backend.openModalIframe({\'width\':800,\'title\':\'Edit\',\'url\':this.href});return false">Test Link Text</a>',
            $backendUiUtil->popupWizardLink(['param' => 'value'], $config)
        );

        $config->setAttributes(['class' => 'test-class']);
        $this->assertStringContainsString(
            'class="test-class"',
            $backendUiUtil->popupWizardLink(['param' => 'value'], $config)
        );
    }

    public function testPopupWizardLinkGeneratesLinkWithIcon()
    {
        $routingUtil = $this->createMock(RoutingUtil::class);
        $routingUtil->method('generateBackendRoute')->willReturn('generated_url');

        $image = $this->mockAdapter(['getHtml']);

        $image->method('getHtml')->willReturnCallback(function (string $image, string $alt = '', string $attributes = '') {
            return '<img src="'.$image.'" alt="'.$alt.'" style="vertical-align:top">';
        });

        $framework = $this->mockContaoFramework([Image::class => $image]);

        $config = new PopupWizardLinkOptions();
        $config->title = 'Test Title';
        $config->style = 'Test Style';
        $config->popupTitle = 'Test Popup Title';
        $config->width = 800;
        $config->linkText = 'Test Link Text';
        $config->icon = 'alias.svg';

        $backendUiUtil = $this->getTestInstance(['routingUtil' => $routingUtil, 'framework' => $framework]);

        $this->assertStringContainsString(
            '<a href="generated_url" title="Test Title" style="Test Style" onclick="Backend.openModalIframe({\'width\':800,\'title\':\'Test Popup Title\',\'url\':this.href});return false"><img src="alias.svg" alt="Test Title" style="vertical-align:top"> Test Link Text</a>',
            $backendUiUtil->popupWizardLink(['param' => 'value'], $config)
        );

        $config->setIcon('edit.svg');
        $this->assertStringContainsString(
            'src="edit.svg"',
            $backendUiUtil->popupWizardLink(['param' => 'value'], $config)
        );
    }
}