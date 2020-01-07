<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Security;

use Contao\System;
use HeimrichHannot\UtilsBundle\Security\CodeUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use HeimrichHannot\UtilsBundle\Tests\TestCaseEnvironment;

class CodeUtilTest extends TestCaseEnvironment
{
    public function setUp()
    {
        parent::setUp();

        $container = System::getContainer();

        $stringUtil = new StringUtil($this->mockContaoFramework());
        $container->set('huh.utils.string', $stringUtil);

        System::setContainer($container);
    }

    public function testInstantiation()
    {
        $util = new CodeUtil($this->mockContaoFramework());
        $this->assertInstanceOf(CodeUtil::class, $util);
    }

    public function testGenerate()
    {
        $codeUtil = new CodeUtil($this->mockContaoFramework());
        $code = $codeUtil::generate(8);
        $this->assertSame(8, \strlen($code));

        $code = $codeUtil::generate(16);
        $this->assertSame(16, \strlen($code));

        $code = $codeUtil::generate(16, true, ['asdeg35g8*']);
        $this->assertSame(16, \strlen($code));

        $code = $codeUtil::generate(16, true, ['capitalLetters']);
        $this->assertSame($code, preg_replace('/^[a-z]+$/', '', $code));
        $this->assertSame(16, \strlen($code));
        $this->assertSame('', preg_replace('/^[A-Z]+$/', '', $code));

        $code = $codeUtil::generate(16, true, ['smallLetters']);
        $this->assertSame($code, preg_replace('/^[A-Z]+$/', '', $code));
        $this->assertSame(16, \strlen($code));
        $this->assertSame('', preg_replace('/^[a-z]+$/', '', $code));

        $code = $codeUtil::generate(16, true, ['numbers']);
        $this->assertSame($code, preg_replace('/^[A-Z]+$/', '', $code));
        $this->assertSame(16, \strlen($code));
        $this->assertSame('', preg_replace('/^[0-9]+$/', '', $code));

        $code = $codeUtil::generate(16, true, ['capitalLetters', 'specialChars'], null, '$%&?!');
        $this->assertSame(16, \strlen($code));
    }

    public function testGenerateRegex()
    {
        $container = System::getContainer();

        $stringUtil = $this->mockAdapter(['random']);
        $stringUtil->method('random')->willReturn('Aa2');
        $container->set('huh.utils.string', $stringUtil);

        System::setContainer($container);

        $codeUtil = new CodeUtil($this->mockContaoFramework());
        $code = $codeUtil::generate(16, false, ['capitalLetters']);
        $this->assertGreaterThan(16, \strlen($code));
        $this->assertSame(1, preg_match('@Aa2@', $code));

        $code = $codeUtil::generate(16, false, ['capitalLetters', 'specialChars']);
        $this->assertGreaterThan(16, \strlen($code));
        $this->assertSame(1, preg_match('@Aa2@', $code));
    }
}
