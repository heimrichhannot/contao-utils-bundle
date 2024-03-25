<?php

namespace Options;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\Options\OptionsManager;

class OptionsFactoryTest extends ContaoTestCase
{
    public function testOptionsFactory()
    {
        $options = new OptionsManager();
        $options->set('foo', 'foo');
        $options->bar = 'bar';

        $this->assertTrue($options->has('foo'));
        $this->assertTrue($options->has('bar'));
        $this->assertFalse($options->has('baz'));

        $this->assertSame('foo', $options->get('foo'));
        $this->assertSame('bar', $options->get('bar'));
        $this->assertNull($options->get('baz'));
        $this->assertSame('foobar', $options->get('baz', 'foobar'));
        $this->assertSame('foo', $options->foo);
        $this->assertSame('bar', $options->bar);
        $this->assertNull($options->baz);

        $options->foo = 'bar';
        $options->del('bar');

        $this->assertSame('bar', $options->foo);
        $this->assertFalse($options->has('bar'));
    }

    public function testOptionsFactoryInheritance()
    {
        $options = new class extends OptionsManager {
            public string $foo = 'foo';
        };

        $this->assertTrue($options->has('foo'));
        $this->assertSame('foo', $options->foo);
        $this->assertSame('foo', $options->get('foo'));

        $options->set('foo', 'baz');
        $options->set('bar', 'bar');

        $this->assertTrue($options->has('foo'));
        $this->assertTrue($options->has('bar'));
        $this->assertSame('baz', $options->foo);
        $this->assertSame('bar', $options->bar);

        $options->del('foo');
        $options->del('bar');

        $this->assertFalse($options->has('foo'));
        $this->assertFalse($options->has('bar'));
    }
}