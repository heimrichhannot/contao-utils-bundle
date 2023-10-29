<?php

namespace HeimrichHannot\UtilsBundle\tests\Util\DatabaseUtil;

use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil\CreateWhereForSerializedBlobResult;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class DatabaseUtilTest extends AbstractUtilsTestCase
{
    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        return new DatabaseUtil();
    }

    public function testCreateWhereForSerializedBlob()
    {
        $result = $this->getTestInstance()->createWhereForSerializedBlob('elements', ['texts', 'headline']);
        static::assertInstanceOf(CreateWhereForSerializedBlobResult::class, $result);
        static::assertSame('(elements REGEXP (?) OR elements REGEXP (?))', $result->createOrWhere());
        static::assertSame('(elements REGEXP (?) AND elements REGEXP (?))', $result->createAndWhere());
        static::assertSame('(elements REGEXP (:"texts") OR elements REGEXP (:"headline"))', $result->createInlineOrWhere());
        static::assertSame('(elements REGEXP (:"texts") AND elements REGEXP (:"headline"))', $result->createInlineAndWhere());
        static::assertCount(2, $result->values);
    }


}
