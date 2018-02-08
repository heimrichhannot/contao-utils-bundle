<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Dca;

use Contao\Database;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

class DcaUtilTest extends ContaoTestCase
{
    public function testInstantiation()
    {
        $util = new DcaUtil($this->mockContaoFramework());
        $this->assertInstanceOf(DcaUtil::class, $util);
    }

    public function testGenerateAlias()
    {
        $util = new DcaUtil($this->mockContaoFramework([
            Database::class => $this->getDatabaseMock(),
        ]));

        $this->assertSame('alias', $util->generateAlias('alias', 15, 'tl_table', 'Alias'));
        $this->assertSame('alias', $util->generateAlias('', 15, 'tl_table', 'Alias'));
        $this->assertSame('hans-dieter', $util->generateAlias('', 15, 'tl_table', 'Hans Dieter'));
        $this->assertSame('hans-däter', $util->generateAlias('', 15, 'tl_table', 'Hans Däter'));
        $this->assertSame('hans-daeter', $util->generateAlias('', 15, 'tl_table', 'Hans Däter', false));
        $this->assertSame('existing-alias', $util->generateAlias('', 1, 'tl_table', 'Existing Alias'));
        $this->assertSame('existing-alias-5', $util->generateAlias('', 5, 'tl_table', 'Existing Alias'));
        $this->assertSame('existing-alias', $util->generateAlias('existing-alias', 1, 'tl_table', 'Existing Alias'));
        $this->assertSame('ich-du-cookies-für-alle', $util->generateAlias('', 6, 'tl_table', 'Ich & du || Cookie\'s für $alle'));
        $this->assertSame('ich-du-cookies-fuer-alle', $util->generateAlias('', 6, 'tl_table', 'Ich & du || Cookie\'s für $alle', false));
        $GLOBALS['TL_LANG']['ERR']['aliasExists'] = 'Alias %s already exist!';
        $this->expectException(\Exception::class);
        $util->generateAlias('existing-alias', 5, 'tl_table', 'Existing Alias');
    }

    public function getDatabaseMock()
    {
        $databaseAdapter = $this->mockAdapter([
            'getInstance', 'prepare', 'execute',
        ]);
        $databaseAdapter->method('getInstance')->willReturnSelf();
        $databaseAdapter->method('prepare')->withAnyParameters()->willReturnSelf();
        $databaseAdapter->method('execute')->with($this->anything())->willReturnCallback(function ($alias) {
            $result = new \stdClass();
            $result->numRows = 0;
            $result->id = 5;
            switch ($alias) {
                case 'existing-alias':
                    $result->numRows = 1;
                    $result->id = 1;
                    break;
            }

            return $result;
        });

        return $databaseAdapter;
    }
}
