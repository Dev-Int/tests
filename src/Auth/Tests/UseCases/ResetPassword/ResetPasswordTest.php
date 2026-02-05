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

namespace Auth\Tests\UseCases\ResetPassword;

use Auth\Entities\Repository\PasswordResetTokenRepository;
use Auth\Entities\Repository\UserRepository;
use Auth\Entities\ResetPassword as PasswordResetToken;
use Auth\Entities\VO\HashedPassword;
use Auth\Tests\DataBuilder\UserDataBuilder;
use Auth\UseCases\Gateway\PasswordHasherGateway;
use Auth\UseCases\ResetPassword\ResetPassword;
use Auth\UseCases\ResetPassword\ResetPasswordRequest;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Auth\UseCases\ResetPassword\ResetPassword
 */
final class ResetPasswordTest extends TestCase
{
    public function testResetPasswordSucceeds(): void
    {
        // Arrange
        $userRepository = $this->createMock(UserRepository::class);
        $resetTokenRepository = $this->createMock(PasswordResetTokenRepository::class);
        $passwordHasher = $this->createMock(PasswordHasherGateway::class);
        $useCase = new ResetPassword($userRepository, $resetTokenRepository, $passwordHasher);
        $request = $this->createMock(ResetPasswordRequest::class);

        $userId = ResourceUuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $tokenId = ResourceUuid::fromString('660e8400-e29b-41d4-a716-446655440001');
        $token = 'reset-token-abc123';

        $user = UserDataBuilder::aUser()
            ->withUuid($userId)
            ->withEmail('user@example.com')
            ->build()
        ;

        $passwordResetToken = new PasswordResetToken(
            id: $tokenId,
            userId: $userId,
            token: $token,
            isExpired: false,
            usedAt: null
        );

        // Assert
        $request->expects(self::once())->method('token')->willReturn($passwordResetToken);
        $request->expects(self::once())->method('plainPassword')->willReturn('NewSecurePassword123!');

        $userRepository->expects(self::once())
            ->method('getByUuid')
            ->with($userId)
            ->willReturn($user)
        ;
        $userRepository->expects(self::once())
            ->method('update')
            ->with($user)
        ;

        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with('NewSecurePassword123!')
            ->willReturn(HashedPassword::fromHash('$2y$13$newHashedPassword'))
        ;

        $resetTokenRepository->expects(self::once())
            ->method('save')
            ->with($passwordResetToken)
        ;

        // Act
        $useCase->execute($request);

        // Assert
        self::assertInstanceOf(
            \DateTimeImmutable::class,
            $passwordResetToken->usedAt()
        );
        self::assertSame('$2y$13$newHashedPassword', $user->password()->toString());
    }
}
