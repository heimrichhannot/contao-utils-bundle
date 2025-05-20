<?php

namespace HeimrichHannot\UtilsBundle\Tests\StaticUtil;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\StaticUtil\StaticArrayUtil;

class StaticArrayUtilTest extends ContaoTestCase
{
    public function testFilterByPrefixes()
    {
        $data = [
            'foo_one' => 1,
            'foo_two' => 2,
            'bar_one' => 3,
            'baz' => 4,
        ];

        // Test mit mehreren Präfixen
        $result = StaticArrayUtil::filterByPrefixes($data, ['foo_', 'bar_']);
        $expected = [
            'foo_one' => 1,
            'foo_two' => 2,
            'bar_one' => 3,
        ];
        $this->assertSame($expected, $result);

        // Test mit mehreren Präfixen
        $result = StaticArrayUtil::filterByPrefixes($data, ['foo_', 'foo_one', 'bar_']);
        $this->assertSame($expected, $result);

        // Test mit leerem Präfix-Array (soll Originaldaten zurückgeben)
        $result = StaticArrayUtil::filterByPrefixes($data, []);
        $this->assertSame($data, $result);

        // Test mit Präfix, das nicht existiert
        $result = StaticArrayUtil::filterByPrefixes($data, ['qux_']);
        $this->assertSame([], $result);
    }
}