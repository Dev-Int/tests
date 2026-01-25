<?php

declare(strict_types=1);

/*
 * This file is part of the Tests package.
 *
 * (c) Dev-Int Création <info@developpement-interessant.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Auth\Tests\UseCases\User\GetUsers;

use Auth\Entities\UserCollection;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Auth\UseCases\Gateway\Finder\UserFinder;
use Auth\UseCases\User\GetUsers\GetUsers;
use Auth\UseCases\User\GetUsers\GetUsersRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 *
 * @covers \Auth\UseCases\User\GetUsers\GetUsers
 * @covers \Auth\UseCases\User\GetUsers\GetUsersRequest
 */
final class GetUsersTest extends TestCase
{
    public function testGetUsersSucceeds(): void
    {
        // Arrange
        $userFinder = $this->createMock(UserFinder::class);
        $useCase = new GetUsers($userFinder);
        $request = $this->createMock(GetUsersRequest::class);

        $user1 = UserDataBuilder::aUser()->withEmail('user1@example.com')->build();
        $user2 = UserDataBuilder::aUser()->withEmail('user2@example.com')->build();

        $usersCollection = new UserCollection(totalItems: 2);
        $usersCollection->add($user1);
        $usersCollection->add($user2);

        $request->expects(self::once())->method('page')->willReturn(1);
        $request->expects(self::once())->method('itemsPerPage')->willReturn(10);

        $userFinder->expects(self::once())
            ->method('findAllUsersPaginated')
            ->with(1, 10)
            ->willReturn($usersCollection)
        ;

        // Act
        $response = $useCase->execute($request);
        $getUsers = $response->users;

        // Assert
        self::assertCount(2, $getUsers);
        $getUser1 = $getUsers->current();
        self::assertSame('user1@example.com', $getUser1->email()->toString());
        $getUsers->next();
        $getUser2 = $getUsers->current();
        self::assertSame('user2@example.com', $getUser2->email()->toString());
    }

    public function testGetUsersReturnsEmptyCollection(): void
    {
        // Arrange
        $userFinder = $this->createMock(UserFinder::class);
        $useCase = new GetUsers($userFinder);
        $request = $this->createMock(GetUsersRequest::class);

        $emptyCollection = new UserCollection(totalItems: 0);

        $request->expects(self::once())->method('page')->willReturn(1);
        $request->expects(self::once())->method('itemsPerPage')->willReturn(10);

        $userFinder->expects(self::once())
            ->method('findAllUsersPaginated')
            ->with(1, 10)
            ->willReturn($emptyCollection)
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertCount(0, $response->users);
    }
}
