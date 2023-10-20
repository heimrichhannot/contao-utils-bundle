<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\Util\User;

use Contao\MemberModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\UserGroupModel;
use Contao\UserModel;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Tests\AbstractUtilsTestCase;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil\CreateWhereForSerializedBlobResult;
use HeimrichHannot\UtilsBundle\Util\DatabaseUtil\DatabaseUtil;
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
        // Test no user groups

        $userModel = $this->mockClassWithProperties(UserModel::class);

        $framework = $this->mockContaoFramework([
            Model::class => $this->mockModelAdapter(),
        ]);

        $instance = $this->getTestInstance([
            'contaoFramework' => $framework,
        ]);

        $activeGroups = $instance->getActiveGroups($userModel);
        self::assertNull($activeGroups);

        $userModel = $this->mockClassWithProperties(UserModel::class, [
            'groups' => serialize([]),
        ]);

        self::assertNull($instance->getActiveGroups($userModel));

        $userModel = $this->mockClassWithProperties(UserModel::class, [
            'groups' => serialize(['2', '5']),
        ]);

        $groupCollection = new Collection([
            $this->mockClassWithProperties(UserGroupModel::class, ['id' => 2]),
            $this->mockClassWithProperties(UserGroupModel::class, ['id' => 5]),
        ], UserGroupModel::getTable());
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstancesBy')
            ->with(self::callback(function ($parameter) {
                return 'tl_user_group' === $parameter;
            }))
            ->willReturn($groupCollection);

        $instance = $this->getTestInstance([
            'modelUtil' => $modelUtil,
            'contaoFramework' => $framework,
        ]);

        $activeGroups = $instance->getActiveGroups($userModel);
        $this->assertInstanceOf(Collection::class, $activeGroups);
        $this->assertCount(2, $activeGroups);

        $memberModel = $this->mockClassWithProperties(MemberModel::class, [
            'groups' => serialize(['2', '5']),
        ]);

        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstancesBy')
            ->with(self::callback(function ($parameter) {
                return 'tl_member_group' === $parameter;
            }))
            ->willReturn($groupCollection);

        $instance = $this->getTestInstance([
            'modelUtil' => $modelUtil,
            'contaoFramework' => $framework,
        ]);

        $activeGroups = $instance->getActiveGroups($memberModel);

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
        $userModel = $this->mockClassWithProperties(UserModel::class);
        $instance = $this->getTestInstance();
        $this->assertFalse($instance->hasActiveGroup($userModel, 1));

        $userModel = $this->mockClassWithProperties(UserModel::class, [
            'groups' => serialize(['2', '5']),
        ]);

        $userModelAdapter = $this->mockAdapter(['getTable']);
        $userModelAdapter->method('getTable')->willReturn('tl_user');

        $groupCollection = new Collection([
            $this->mockClassWithProperties(UserGroupModel::class, ['id' => 2]),
            $this->mockClassWithProperties(UserGroupModel::class, ['id' => 5]),
        ], UserGroupModel::getTable());
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstancesBy')->willReturn($groupCollection);

        $framework = $this->mockContaoFramework([
            $userModelAdapter::class => $userModelAdapter,
        ]);

        $instance = $this->getTestInstance([
            'modelUtil' => $modelUtil,
            'contaoFramework' => $framework,
        ]);

        $this->assertTrue($instance->hasActiveGroup($userModel, 2));
        $this->assertFalse($instance->hasActiveGroup($userModel, 1));


//        $builder = $this->getMockBuilder(UserUtil::class)
//            ->setMethods(['getActiveGroups']);

//        $instance = $this->getTestInstance([], $builder);
//
//        $instance->method('getActiveGroups')->willReturnCallback(function (int $userId) {
//            return match ($userId) {
//                1 => new Collection([
//                    $this->mockClassWithProperties(UserGroupModel::class, ['id' => 1]),
//                    $this->mockClassWithProperties(UserGroupModel::class, ['id' => 2]),
//                ], 'tl_user_group'),
//                default => null,
//            };
//        });
//
//        $this->assertTrue($instance->hasActiveGroup(1, 1));
//        $this->assertFalse($instance->hasActiveGroup(1, 3));
//        $this->assertFalse($instance->hasActiveGroup(2, 1));
//        $this->assertFalse($instance->hasActiveGroup(3, 1));
    }

    public function testFindActiveUsersByGroup()
    {
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
            return new CreateWhereForSerializedBlobResult($field, $values);
        });

        $instance = $this->getTestInstance($parameters);
        $this->assertNull($instance->findActiveUsersByGroup([]));

        $this->assertInstanceOf(Collection::class, $instance->findActiveUsersByGroup([1]));
        $this->assertCount(2, $instance->findActiveUsersByGroup([1, 5]));

        $this->assertCount(3, $instance->findActiveUsersByGroup([1, 2, '1 or true', '5']));
    }
}
