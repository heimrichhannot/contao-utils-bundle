<?php

namespace HeimrichHannot\UtilsBundle\Tests\EventListener\DcaField;

use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\Model;
use HeimrichHannot\UtilsBundle\Dca\AuthorField;
use HeimrichHannot\UtilsBundle\EventListener\DcaField\DcaAuthorListener;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use PHPUnit\Framework\MockObject\MockBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

class DcaAuthorListenerTest extends AbstractUtilsTestCase
{

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $framework = $parameters['framework'] ?? $this->mockContaoFramework();
        $security = $parameters['security'] ?? $this->createMock(TokenStorageInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnCallback(function ($key) use ($framework, $security) {
            switch ($key) {
                case 'contao.framework':
                    return $framework;
                case 'token_storage':
                    return $security;
            }
            return null;
        });

        return new DcaAuthorListener($container);
    }

    public function testOnLoadDataContainer()
    {
        $testDca = [
            'fields' => []
        ];
        $GLOBALS['TL_DCA']['tl_test'] = $testDca;
        $instance = $this->getTestInstance();
        $instance->onLoadDataContainer('tl_test');
        $this->assertSame($testDca, $GLOBALS['TL_DCA']['tl_test']);

        AuthorField::register('tl_test');
        $instance->onLoadDataContainer('tl_test');
        $this->assertArrayHasKey('author', $GLOBALS['TL_DCA']['tl_test']['fields']);

        $testDca = ['fields' => []];
        $GLOBALS['TL_DCA']['tl_test'] = $testDca;

        AuthorField::register('tl_test')->setFieldNamePrefix('test_');
        $instance->onLoadDataContainer('tl_test');
        $this->assertArrayHasKey('test_author', $GLOBALS['TL_DCA']['tl_test']['fields']);

        AuthorField::register('tl_test')->setFieldNamePrefix('test');
        $instance->onLoadDataContainer('tl_test');
        $this->assertArrayHasKey('testAuthor', $GLOBALS['TL_DCA']['tl_test']['fields']);
    }

    public function testOnConfigCopyCallback()
    {
        $testModel = $this->mockModelObject(Model::class);
        $testModel->id = 4;

        $modelClassAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelClassAdapter->method('getClassFromTable')->willReturn('HuhUtilsTestModel');

        $modelAdapter = $this->mockAdapter(['findByPk']);
        $modelAdapter->method('findByPk')->willReturn($testModel);


        $framework = $this->mockContaoFramework([
            Model::class => $modelClassAdapter,
            'HuhUtilsTestModel' => $modelAdapter
        ]);

        $modelClassAdapter->method('getClassFromTable')->willReturn('tl_test');

        $frontendUser = $this->createMock(FrontendUser::class);
        $this->mockClassWithProperties(FrontendUser::class, ['id' => 1]);

        $instance = $this->getTestInstance([
            'framework' => $framework
        ]);

        AuthorField::register('tl_test');

        $dc = $this->mockClassWithProperties(DataContainer::class, ['table' => 'tl_test']);

        $instance->onConfigCopyCallback(1, $dc);
        $this->assertSame(0, $testModel->author);
    }
}