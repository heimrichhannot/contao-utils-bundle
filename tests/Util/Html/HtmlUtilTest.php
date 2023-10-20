<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\Html;

use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\HtmlUtil\GenerateDataAttributesStringArrayHandling;
use HeimrichHannot\UtilsBundle\Util\HtmlUtil\GenerateDataAttributesStringOptions;
use HeimrichHannot\UtilsBundle\Util\HtmlUtil\HtmlUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class HtmlUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        return new HtmlUtil();
    }

    public function testGenerateAttributeString()
    {
        $instance = $this->getTestInstance();
        $this->assertEmpty($instance->generateAttributeString([]));
        $this->assertSame('a="b"', $instance->generateAttributeString(['a' => 'b']));
        $this->assertSame(
            'src="heimrich-hannot.de" type="text/website" async',
            $instance->generateAttributeString(['src' => 'heimrich-hannot.de', 'type' => 'text/website', 'async' => true])
        );
        $this->assertSame(
            'src="heimrich-hannot.de" type="text/website"',
            $instance->generateAttributeString(['src' => 'heimrich-hannot.de', 'type' => 'text/website', 'async' => false])
        );
        $this->assertSame(
            'src="heimrich-hannot.de" type="text/website" async="async"',
            $instance->generateAttributeString(['src' => 'heimrich-hannot.de', 'type' => 'text/website', 'async' => true], ['xhtml' => true])
        );
    }

    public function testGenerateDataAttributesString()
    {
        $instance = $this->getTestInstance();

        $this->assertSame('data-foo-bar="1"', $instance->generateDataAttributesString(['Foo Bar' => '1']));
        $this->assertSame('data-foo-bar="1"', $instance->generateDataAttributesString(['Foo-Bar' => '1']));
        $this->assertSame('data-foo-bar="1"', $instance->generateDataAttributesString(['FooBar' => '1']));

        $this->assertSame('', $instance->generateDataAttributesString(['Foo Bar' => false]));
        $this->assertSame('', $instance->generateDataAttributesString([]));

        $this->assertSame(
            'data-Foo Bar="1"',
            $instance->generateDataAttributesString(
                ['Foo Bar' => '1'],
                GenerateDataAttributesStringOptions::create()->setNormalizeKeys(false)
            )
        );
        $this->assertSame(
            'data-Foo-Bar="1"',
            $instance->generateDataAttributesString(
                ['Foo-Bar' => '1'],
                GenerateDataAttributesStringOptions::create()->setNormalizeKeys(false)
            )
        );
        $this->assertSame(
            'data-FooBar="1"',
            $instance->generateDataAttributesString(
                ['FooBar' => '1'],
                GenerateDataAttributesStringOptions::create()->setNormalizeKeys(false)
            )
        );

        $this->assertSame(
            'data-foo-bar="1"',
            $instance->generateDataAttributesString(['data-foo-bar' => '1'])
        );

        $this->assertSame(
            'data-foo-bar',
            $instance->generateDataAttributesString(['data-foo-bar' => true])
        );
        $this->assertSame(
            'data-foo-bar="Blub"',
            $instance->generateDataAttributesString(['Foo Bar' => ['Blah' => 'Blub']])
        );

        $this->assertSame(
            'data-foo-bar="{&quot;Blah&quot;:&quot;Blub&quot;}"',
            $instance->generateDataAttributesString(
                ['Foo Bar' => ['Blah' => 'Blub']],
                GenerateDataAttributesStringOptions::create()->setArrayHandling(GenerateDataAttributesStringArrayHandling::ENCODE)
            )
        );

        $this->assertSame(
            'data-foo-bar data-animal-type="bird" data-editable data-some-strange-attribute="Totally strange" data-perfectly-prepared="sure" data-class="button attention" data-count="5"',
            $instance->generateDataAttributesString([
                'data-foo-bar'            => true,
                'Animal type'             => 'bird',
                'editable'                => true,
                'Some Strange Attribute'  => 'Totally strange',
                'data-perfectly-prepared' => 'sure',
                'class'                   => ['button', 'attention'],
                'Count'                   => 5,
            ])
        );
    }
}
