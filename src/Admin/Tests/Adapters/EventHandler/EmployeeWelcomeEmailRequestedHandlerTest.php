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

namespace Admin\Tests\Adapters\EventHandler;

use Admin\Adapters\EventHandler\EmployeeWelcomeEmailRequestedHandler;
use Admin\Entities\Event\EmployeeWelcomeEmailRequested;
use Admin\UseCases\Gateway\EmailPayload;
use Admin\UseCases\Gateway\EmailType;
use Admin\UseCases\Gateway\NotificationGateway;
use Admin\UseCases\Gateway\PasswordResetGateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EmployeeWelcomeEmailRequestedHandlerTest extends TestCase
{
    private MockObject&NotificationGateway $notificationGateway;
    private MockObject&PasswordResetGateway $passwordResetGateway;
    private LoggerInterface&MockObject $logger;
    private MockObject&UrlGeneratorInterface $urlGenerator;
    private EmployeeWelcomeEmailRequestedHandler $handler;

    protected function setUp(): void
    {
        $this->notificationGateway = $this->createMock(NotificationGateway::class);
        $this->passwordResetGateway = $this->createMock(PasswordResetGateway::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->handler = new EmployeeWelcomeEmailRequestedHandler(
            $this->notificationGateway,
            $this->passwordResetGateway,
            $this->logger,
            $this->urlGenerator,
        );
    }

    public function testItSendsWelcomeEmailWithCorrectPayload(): void
    {
        // Arrange
        $userUuid = ResourceUuid::fromString('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d');
        $event = new EmployeeWelcomeEmailRequested(
            employeeUuid: ResourceUuid::generate(),
            employeeEmail: EmailField::fromString('john.doe@example.com'),
            firstName: 'John',
            userUuid: $userUuid,
        );

        $expectedPayload = new EmailPayload(
            to: $event->employeeEmail,
            type: EmailType::EMPLOYEE_WELCOME,
            subject: 'Bienvenue - Créez votre mot de passe',
            context: [
                'firstName' => 'John',
                'userEmail' => 'john.doe@example.com',
                'resetUrl' => 'https://example.com/reset/token123',
            ],
        );

        // Assert
        $this->passwordResetGateway->expects(self::once())
            ->method('createResetToken')
            ->with(self::callback(static fn (ResourceUuid $uuid): bool => $uuid->toString() === $userUuid->toString()))
            ->willReturn('token123')
        ;
        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('auth_password_reset', ['token' => 'token123'])
            ->willReturn('https://example.com/reset/token123')
        ;
        $this->notificationGateway
            ->expects(self::once())
            ->method('sendEmail')
            ->with(self::equalTo($expectedPayload))
        ;

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'Email de bienvenue envoyé avec succès',
                self::callback(static function (array $context) use ($event): bool {
                    return $context['employeeEmail'] === $event->employeeEmail->toString()
                        && $context['employeeUuid'] === $event->employeeUuid->toString();
                })
            )
        ;

        // Act
        $this->handler->__invoke($event);
    }

    public function testItLogsAndRethrowsExceptionForMessengerRetry(): void
    {
        // Arrange
        $userUuid = ResourceUuid::generate();
        $event = new EmployeeWelcomeEmailRequested(
            employeeUuid: ResourceUuid::generate(),
            employeeEmail: EmailField::fromString('john.doe@example.com'),
            firstName: 'John',
            userUuid: $userUuid,
        );

        $exception = new \RuntimeException('SMTP server unavailable');

        // Assert
        $this->passwordResetGateway->expects(self::once())
            ->method('createResetToken')
            ->with(self::callback(static fn (ResourceUuid $uuid): bool => $uuid->toString() === $userUuid->toString()))
            ->willReturn('token123')
        ;
        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('auth_password_reset', ['token' => 'token123'])
            ->willReturn('https://example.com/reset/token123')
        ;
        $this->notificationGateway
            ->expects(self::once())
            ->method('sendEmail')
            ->willThrowException($exception)
        ;

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Échec envoi email de bienvenue - retry programmé',
                self::callback(static function (array $context) use ($event, $exception): bool {
                    return $context['employeeEmail'] === $event->employeeEmail->toString()
                        && $context['employeeUuid'] === $event->employeeUuid->toString()
                        && $context['exception'] === $exception;
                })
            )
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SMTP server unavailable');

        // Act
        $this->handler->__invoke($event);
    }

    public function testItRethrowsWhenTokenGenerationFails(): void
    {
        // Arrange
        $userUuid = ResourceUuid::generate();
        $event = new EmployeeWelcomeEmailRequested(
            employeeUuid: ResourceUuid::generate(),
            employeeEmail: EmailField::fromString('john.doe@example.com'),
            firstName: 'John',
            userUuid: $userUuid,
        );

        $exception = new \RuntimeException('Token generation failed');

        // Assert — token failure triggers retry via re-throw
        $this->passwordResetGateway->expects(self::once())
            ->method('createResetToken')
            ->willThrowException($exception)
        ;
        $this->urlGenerator->expects(self::never())->method('generate');
        $this->notificationGateway->expects(self::never())->method('sendEmail');
        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Échec envoi email de bienvenue - retry programmé',
                self::callback(static function (array $context) use ($event, $exception): bool {
                    return $context['employeeEmail'] === $event->employeeEmail->toString()
                        && $context['employeeUuid'] === $event->employeeUuid->toString()
                        && $context['exception'] === $exception;
                })
            )
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token generation failed');

        // Act
        $this->handler->__invoke($event);
    }
}
