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

namespace Auth\Tests\UseCases\PasswordReset\CreatePasswordResetToken;

use Auth\Entities\Exception\UserNotFoundById;
use Auth\Entities\Repository\PasswordResetTokenRepository;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\ResetPassword;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Auth\UseCases\Gateway\TransactionGateway;
use Auth\UseCases\PasswordReset\CreatePasswordResetToken\CreatePasswordResetToken;
use Auth\UseCases\PasswordReset\CreatePasswordResetToken\CreatePasswordResetTokenRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Auth\UseCases\PasswordReset\CreatePasswordResetToken\CreatePasswordResetToken
 */
final class CreatePasswordResetTokenTest extends TestCase
{
    public function testCreateResetTokenSucceeds(): void
    {
        // Arrange
        $repository = $this->createMock(PasswordResetTokenRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $useCase = new CreatePasswordResetToken($repository, $userRepository, $transactionGateway);
        $request = $this->createMock(CreatePasswordResetTokenRequest::class);

        $userUuid = ResourceUuid::generate();
        $user = UserDataBuilder::aUser()->build();

        // Assert
        $request->expects(self::once())->method('userUuid')->willReturn($userUuid);

        $userRepository->expects(self::once())
            ->method('getByUuid')
            ->with($userUuid)
            ->willReturn($user)
        ;

        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $func) => $func())
        ;

        $repository->expects(self::once())
            ->method('deleteByUserUuid')
            ->with($userUuid)
        ;

        $repository->expects(self::once())
            ->method('create')
            ->with(self::isInstanceOf(ResetPassword::class))
        ;

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame(64, \strlen($response->token));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $response->token);
    }

    public function testCreateResetTokenThrowsWhenUserNotFound(): void
    {
        // Arrange
        $repository = $this->createMock(PasswordResetTokenRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $useCase = new CreatePasswordResetToken($repository, $userRepository, $transactionGateway);
        $request = $this->createMock(CreatePasswordResetTokenRequest::class);

        $userUuid = ResourceUuid::generate();

        // Assert
        $request->expects(self::once())->method('userUuid')->willReturn($userUuid);

        $userRepository->expects(self::once())
            ->method('getByUuid')
            ->with($userUuid)
            ->willThrowException(new UserNotFoundById($userUuid))
        ;

        $transactionGateway->expects(self::never())->method('wrapInTransaction');
        $repository->expects(self::never())->method('deleteByUserUuid');
        $repository->expects(self::never())->method('create');

        $this->expectException(UserNotFoundById::class);

        // Act
        $useCase->execute($request);
    }

    public function testDeleteIsCalledBeforeCreateInsideTransaction(): void
    {
        // Arrange
        $repository = $this->createMock(PasswordResetTokenRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $transactionGateway = $this->createMock(TransactionGateway::class);
        $useCase = new CreatePasswordResetToken($repository, $userRepository, $transactionGateway);
        $request = $this->createMock(CreatePasswordResetTokenRequest::class);

        $userUuid = ResourceUuid::generate();
        $user = UserDataBuilder::aUser()->build();
        $callOrder = [];

        // Assert
        $request->expects(self::once())->method('userUuid')->willReturn($userUuid);
        $userRepository->expects(self::once())->method('getByUuid')->willReturn($user);

        $transactionGateway->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static function (callable $func) use (&$callOrder): void {
                $callOrder[] = 'transaction_start';
                $func();
                $callOrder[] = 'transaction_end';
            })
        ;

        $repository->expects(self::once())
            ->method('deleteByUserUuid')
            ->willReturnCallback(static function () use (&$callOrder): void {
                $callOrder[] = 'delete';
            })
        ;

        $repository->expects(self::once())
            ->method('create')
            ->willReturnCallback(static function () use (&$callOrder): void {
                $callOrder[] = 'create';
            })
        ;

        // Act
        $useCase->execute($request);

        // Assert
        self::assertSame(
            ['transaction_start', 'delete', 'create', 'transaction_end'],
            $callOrder,
            'delete doit précéder create, les deux à l\'intérieur de la transaction'
        );
    }
}
