<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\User;

use Contao\Model;
use Contao\Model\Collection;
use Contao\UserGroupModel;
use Contao\UserModel;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\User\UserUtil;
use PHPUnit\Framework\MockObject\MockBuilder;

class UserUtilTest extends AbstractUtilsTestCase
{
    use ModelMockTrait;

    public function getTestInstance(array $parameters = [], ?MockBuilder $mockBuilder = null)
    {
        $modelUtil = $parameters['modelUtil'] ?? $this->createMock(ModelUtil::class);
        $databaseUtil = $parameters['databaseUtil'] ?? $this->createMock(DatabaseUtil::class);
        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework();

        if (!$mockBuilder) {
            return new UserUtil($modelUtil, $databaseUtil, $contaoFramework);
        }
        $mockBuilder->setConstructorArgs([$modelUtil, $databaseUtil, $contaoFramework]);

        return $mockBuilder->getMock();
    }

    public function testGetActiveGroups()
    {
        $userModel = $this->mockClassWithProperties(UserModel::class, [
            'groups' => serialize(['2', '5']),
        ]);

        $groupCollection = new Collection([
            $this->mockClassWithProperties(UserGroupModel::class, ['id' => 2]),
            $this->mockClassWithProperties(UserGroupModel::class, ['id' => 5]),
        ], UserGroupModel::getTable());
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstancesBy')->willReturn($groupCollection);

        $instance = $this->getTestInstance();

        $activeGroups = $instance->getActiveGroups($userModel);
        $this->assertInstanceOf(Collection::class, $activeGroups);
        $this->assertCount(2, $activeGroups);


//        $parameters['modelUtil'] = $this->createMock(ModelUtil::class);
//        $parameters['modelUtil']->method('findModelInstanceByPk')->willReturnCallback(
//            function (string $table, int $id, array $options = []) {
//                switch ($id) {
//                    case 4:
//                    case 3:
//                        return $this->mockClassWithProperties(UserModel::class, [
//                            'groups' => serialize(['2', '5']),
//                        ]);
//
//                    case 2:
//                        return $this->mockClassWithProperties(UserModel::class, [
//                            'groups' => null,
//                        ]);
//
//                    case 1:
//                    default:
//                        return null;
//                }
//            }
//        );
//        $groupCollection = $this->createMock(Collection::class);
//        $parameters['modelUtil']->method('findModelInstancesBy')
//            ->willReturnOnConsecutiveCalls(null, $groupCollection);
//
//        $instance = $this->getTestInstance($parameters);
//
//        $this->assertNull($instance->getActiveGroups(1));
//        $this->assertNull($instance->getActiveGroups(2));
//        $this->assertNull($instance->getActiveGroups(3));
//
//        /** @var Collection $result */
//        $result = $instance->getActiveGroups(4);
//
//        $this->assertInstanceOf(Collection::class, $result);
    }

    public function testHasActiveGroup()
    {
        $builder = $this->getMockBuilder(UserUtil::class)
            ->setMethods(['getActiveGroups']);

        $instance = $this->getTestInstance([], $builder);

        $instance->method('getActiveGroups')->willReturnCallback(function (int $userId) {
            switch ($userId) {
                case 2:
                    return null;

                case 1:
                    return new Collection([
                        $this->mockClassWithProperties(UserGroupModel::class, ['id' => 1]),
                        $this->mockClassWithProperties(UserGroupModel::class, ['id' => 2]),
                    ], 'tl_user_group');

                default:
                    return null;
            }
        });

        $this->assertTrue($instance->hasActiveGroup(1, 1));
        $this->assertFalse($instance->hasActiveGroup(1, 3));
        $this->assertFalse($instance->hasActiveGroup(2, 1));
        $this->assertFalse($instance->hasActiveGroup(3, 1));
    }

    public function testFindActiveUsersByGroup()
    {
        $userModelAdapterMock = $this->mockAdapter(['findBy']);
        $userModelAdapterMock = $this->mockAdapter(['findBy']);
        $userModelAdapterMock->method('findBy')->willReturnCallback(function ($columns, $values, $options) {
            $users = [];
            $i = 1;

            foreach ($values as $value) {
                $users[] = $this->mockModelObject(UserModel::class, ['id' => $i, 'groups' => serialize($value)]);
                ++$i;
            }

            return new Collection($users, UserModel::getTable());
        });

        $parameters['contaoFramework'] = $this->mockContaoFramework([
            Model::class => $this->mockModelAdapter(),
            UserModel::class => $userModelAdapterMock,
        ]);
        $parameters['databaseUtil'] = $this->createMock(DatabaseUtil::class);
        $parameters['databaseUtil']->method('createWhereForSerializedBlob')->willReturnCallback(function (string $field, array $values) {
            return [$field, $values];
        });

        $instance = $this->getTestInstance($parameters);
        $this->assertNull($instance->findActiveUsersByGroup([]));

        $this->assertInstanceOf(Collection::class, $instance->findActiveUsersByGroup([1]));
        $this->assertCount(2, $instance->findActiveUsersByGroup([1, 5]));

        $this->assertCount(3, $instance->findActiveUsersByGroup([1, 2, '1 or true', '5']));
    }
}
