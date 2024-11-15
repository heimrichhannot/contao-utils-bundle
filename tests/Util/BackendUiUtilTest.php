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
        $parameters['htmlUtil'] = $parameters['htmlUtil'] ?? $this->createMock(HtmlUtil::class);

        return new BackendUiUtil(...$parameters);
    }


    public function testPopupWizardLinkReturnsCorrectUrlOnly()
    {
        $routingUtil = $this->createMock(RoutingUtil::class);
        $routingUtil->method('generateBackendRoute')->willReturn('generated_url');

        $backendUiUtil = $this->getTestInstance(['routingUtil' => $routingUtil]);

        $config = new PopupWizardLinkOptions();
        $config->urlOnly = true;

        $result = $backendUiUtil->popupWizardLink(['param' => 'value'], $config);

        $this->assertEquals('generated_url', $result);
    }

    public function testPopupWizardLinkGeneratesCorrectLink()
    {
        $routingUtil = $this->createMock(RoutingUtil::class);
        $htmlUtil = $this->createMock(HtmlUtil::class);

        $routingUtil->method('generateBackendRoute')->willReturn('generated_url');
        $htmlUtil->method('generateAttributeString')->willReturn('title="Test Title" style="Test Style" onclick="Test Onclick"');

        $backendUiUtil = $this->getTestInstance(['routingUtil' => $routingUtil, 'htmlUtil' => $htmlUtil]);

        $config = new PopupWizardLinkOptions();
        $config->title = 'Test Title';
        $config->style = 'Test Style';
        $config->popupTitle = 'Test Popup Title';
        $config->width = 800;
        $config->linkText = 'Test Link Text';

        $result = $backendUiUtil->popupWizardLink(['param' => 'value'], $config);

        $this->assertStringContainsString('<a href="generated_url" title="Test Title" style="Test Style" onclick="Test Onclick">Test Link Text</a>', $result);
    }

    public function testPopupWizardLinkGeneratesLinkWithIcon()
    {
        $routingUtil = $this->createMock(RoutingUtil::class);
        $framework = $this->createMock(ContaoFramework::class);
        $htmlUtil = $this->createMock(HtmlUtil::class);

        $routingUtil->method('generateBackendRoute')->willReturn('generated_url');
        $htmlUtil->method('generateAttributeString')->willReturn('title="Test Title" style="Test Style" onclick="Test Onclick"');

        $image = $this->mockAdapter(['getHtml']);
        $image->method('getHtml')->willReturn('<img src="alias.svg" alt="Test Title" style="vertical-align:top">');
        $framework = $this->mockContaoFramework([Image::class => $image]);

        $config = new PopupWizardLinkOptions();
        $config->title = 'Test Title';
        $config->style = 'Test Style';
        $config->popupTitle = 'Test Popup Title';
        $config->width = 800;
        $config->linkText = 'Test Link Text';
        $config->icon = 'alias.svg';

        $backendUiUtil = new BackendUiUtil($routingUtil, $framework, $htmlUtil);
        $result = $backendUiUtil->popupWizardLink(['param' => 'value'], $config);

        $this->assertStringContainsString('<a href="generated_url" title="Test Title" style="Test Style" onclick="Test Onclick"><img src="alias.svg" alt="Test Title" style="vertical-align:top"> Test Link Text</a>', $result);
    }
}