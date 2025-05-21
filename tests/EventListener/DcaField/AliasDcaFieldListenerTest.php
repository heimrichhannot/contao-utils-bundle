<?php

namespace EventListener\DcaField;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\DataContainer;
use Dflydev\DotAccessData\Data;
use HeimrichHannot\UtilsBundle\Dca\AliasField;
use HeimrichHannot\UtilsBundle\EventListener\DcaField\AliasDcaFieldListener;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use PHPUnit\Framework\MockObject\MockBuilder;
use Psr\Container\ContainerInterface;
use function Clue\StreamFilter\fun;

class AliasDcaFieldListenerTest extends AbstractUtilsTestCase
{

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $container = $parameters['container'] ?? $this->createMock(ContainerInterface::class);

        return new AliasDcaFieldListener($container);
    }

    public function testOnLoadDataContainer()
    {
        $GLOBALS['TL_DCA']['tl_test'] = [];

        $instance = $this->getTestInstance();
        $instance->onLoadDataContainer('tl_test');
        $this->assertEmpty($GLOBALS['TL_DCA']['tl_test']);

        AliasField::register('tl_test');
        $instance->onLoadDataContainer('tl_test');
        $this->assertArrayHasKey('fields', $GLOBALS['TL_DCA']['tl_test']);
        $this->assertArrayHasKey('alias', $GLOBALS['TL_DCA']['tl_test']['fields']);
        $this->assertSame(
            [AliasDcaFieldListener::class, 'onFieldsAliasSaveCallback'],
            $GLOBALS['TL_DCA']['tl_test']['fields']['alias']['save_callback'][0]
        );

        AliasField::register('tl_test')->setAliasExistCallback(null);
        $instance->onLoadDataContainer('tl_test');
        $this->assertArrayHasKey('fields', $GLOBALS['TL_DCA']['tl_test']);
        $this->assertArrayHasKey('alias', $GLOBALS['TL_DCA']['tl_test']['fields']);
        $this->assertEmpty(
            $GLOBALS['TL_DCA']['tl_test']['fields']['alias']['save_callback']
        );
    }

    public function testOnFieldsAliasSaveCallbackGeneratesAliasIfEmpty()
    {
        $slug = $this->createMock(Slug::class);
        $slug->expects($this->once())
            ->method('generate')
            ->willReturn('generated-alias');

        $framework = $this->createMock(ContaoFramework::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnCallback(function (string $id) use ($slug, $framework) {
            switch ($id) {
                case 'contao.slug':
                case Slug::class:
                    return $slug;
                case 'contao.framework':
                    return $framework;
                default:
                    throw new \InvalidArgumentException("Unknown service: $id");
            }
        });

        $listener = $this->getTestInstance([
            'container' => $container,
        ]);

        $dc = new class () extends DataContainer
        {
            public int $id;
            public string $table;
            public object $activeRecord;

            public function __construct()
            {
            }

            public function __get($strKey)
            {
                if (isset($this->{$strKey})) {
                    return $this->{$strKey};
                }

                return parent::__get($strKey);
            }

            public function __set($strKey, $varValue)
            {
                if (isset($this->{$strKey})) {
                    $this->{$strKey} = $varValue;
                } else {
                    parent::__set($strKey, $varValue);
                }
            }

            public function getPalette()
            {
                // TODO: Implement getPalette() method.
            }

            protected function save($varValue)
            {
                // TODO: Implement save() method.
            }
        };

//        $dc = $this->createMock(DataContainer::class);
        $dc->activeRecord = (object)['title' => 'Test', 'pid' => 1];
        $dc->table = 'tl_article';
        $dc->id = 1;

        $result = $listener->onFieldsAliasSaveCallback('', $dc);
        $this->assertEquals('generated-alias', $result);
    }

    public function testOnFieldsAliasSaveCallbackThrowsOnNumericAlias()
    {
        $this->expectException(\Exception::class);

        $slug = $this->createMock(Slug::class);
        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($this->createMock(Database::class));

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnCallback(function (string $id) use ($slug, $framework) {
            switch ($id) {
                case 'contao.slug':
                case Slug::class:
                    return $slug;
                case 'contao.framework':
                    return $framework;
                default:
                    throw new \InvalidArgumentException("Unknown service: $id");
            }
        });



        $listener = $this->getTestInstance([
            'container' => $container,
        ]);

        $dc = $this->createMock(DataContainer::class);
        $dc->activeRecord = (object)['title' => 'Test', 'pid' => 1];
        $dc->table = 'tl_article';
        $dc->id = 1;

        $GLOBALS['TL_LANG']['ERR']['aliasNumeric'] = 'Alias darf nicht numerisch sein: %s';

        $listener->onFieldsAliasSaveCallback('123', $dc);
    }

    public function testOnFieldsAliasSaveCallbackThrowsOnExistingAlias()
    {
        $this->expectException(\Exception::class);

        $slug = $this->createMock(Slug::class);

        $dbResult = new \stdClass();
        $dbResult->numRows = 1;

        $db = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['prepare', 'execute'])
            ->getMock();
        $db->method('prepare')->willReturnSelf();
        $db->method('execute')->willReturn($dbResult);

        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($db);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnCallback(function (string $id) use ($slug, $framework) {
            switch ($id) {
                case 'contao.slug':
                case Slug::class:
                    return $slug;
                case 'contao.framework':
                    return $framework;
                default:
                    throw new \InvalidArgumentException("Unknown service: $id");
            }
        });

        $listener = $this->getTestInstance([
            'container' => $container,
        ]);

        $dc = $this->createMock(DataContainer::class);
        $dc->activeRecord = (object)['title' => 'Test', 'pid' => 1];
        $dc->table = 'tl_article';
        $dc->id = 1;

        $GLOBALS['TL_LANG']['ERR']['aliasExists'] = 'Alias existiert bereits: %s';

        $listener->onFieldsAliasSaveCallback('existing-alias', $dc);
    }

}