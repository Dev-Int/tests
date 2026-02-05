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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;

final class EmployeeWelcomeEmailRequestedHandlerTest extends TestCase
{
    private MockObject&NotificationGateway $notificationGateway;
    private LoggerInterface&MockObject $logger;
    private EmployeeWelcomeEmailRequestedHandler $handler;

    protected function setUp(): void
    {
        $this->notificationGateway = $this->createMock(NotificationGateway::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new EmployeeWelcomeEmailRequestedHandler(
            $this->notificationGateway,
            $this->logger
        );
    }

    public function testItSendsWelcomeEmailWithCorrectPayload(): void
    {
        // Arrange
        $event = new EmployeeWelcomeEmailRequested(
            employeeEmail: EmailField::fromString('john.doe@example.com'),
            firstName: 'John',
            resetUrl: 'https://example.com/reset/token123',
            employeeUuid: ResourceUuid::generate(),
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
        $event = new EmployeeWelcomeEmailRequested(
            employeeEmail: EmailField::fromString('john.doe@example.com'),
            firstName: 'John',
            resetUrl: 'https://example.com/reset/token123',
            employeeUuid: ResourceUuid::generate(),
        );

        $exception = new \RuntimeException('SMTP server unavailable');

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

        // Assert & Act
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SMTP server unavailable');

        $this->handler->__invoke($event);
    }
}
