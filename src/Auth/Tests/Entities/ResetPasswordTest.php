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

namespace Auth\Tests\Entities;

use Auth\Entities\ResetPassword;
use Auth\Tests\DataBuilder\UserDataBuilder;
use PHPUnit\Framework\TestCase;
use Shared\Entities\ResourceUuid;

/**
 * @group unitTest
 *
 * @covers \Auth\Entities\ResetPassword
 */
final class ResetPasswordTest extends TestCase
{
    /**
     * @return iterable<string, array<mixed>>
     */
    public static function provideCanBeUsedCases(): iterable
    {
        yield 'can be used when user is active and token is valid' => [
            'expected' => true,
            'isUserDisabled' => false,
            'isExpired' => false,
            'isUsed' => false,
            'scenario' => 'User active, token valid, not used',
        ];

        yield 'cannot be used if user is disabled' => [
            'expected' => false,
            'isUserDisabled' => true,
            'isExpired' => false,
            'isUsed' => false,
            'scenario' => 'User disabled',
        ];

        yield 'cannot be used if token is expired' => [
            'expected' => false,
            'isUserDisabled' => false,
            'isExpired' => true,
            'isUsed' => false,
            'scenario' => 'Token expired',
        ];

        yield 'cannot be used if token is already used' => [
            'expected' => false,
            'isUserDisabled' => false,
            'isExpired' => false,
            'isUsed' => true,
            'scenario' => 'Token already used',
        ];
    }

    /**
     * @dataProvider provideCanBeUsedCases
     */
    public function testCanBeUsed(
        bool $expected,
        bool $isUserDisabled,
        bool $isExpired,
        bool $isUsed,
        string $scenario
    ): void {
        // Arrange
        $user = UserDataBuilder::aUser()->build();
        if ($isUserDisabled) {
            $user->disable();
        }

        $token = new ResetPassword(
            id: ResourceUuid::generate(),
            user: $user,
            token: 'valid-token-abc123',
            isExpired: $isExpired,
            usedAt: null,
        );

        if ($isUsed) {
            $token->used();
        }

        // Act & Assert
        self::assertSame($expected, $token->canBeUsed(), $scenario);
    }

    public function testUserGetterReturnsAssociatedUser(): void
    {
        // Arrange
        $user = UserDataBuilder::aUser()->build();
        $token = new ResetPassword(
            id: ResourceUuid::generate(),
            user: $user,
            token: 'valid-token-abc123',
            isExpired: false,
            usedAt: null,
        );

        // Act & Assert
        self::assertSame($user, $token->user());
    }
}
