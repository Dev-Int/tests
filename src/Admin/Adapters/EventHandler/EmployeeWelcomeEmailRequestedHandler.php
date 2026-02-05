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

namespace Admin\Adapters\EventHandler;

use Admin\Entities\Event\EmployeeWelcomeEmailRequested;
use Admin\UseCases\Gateway\EmailPayload;
use Admin\UseCases\Gateway\EmailType;
use Admin\UseCases\Gateway\NotificationGateway;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EmployeeWelcomeEmailRequestedHandler
{
    public function __construct(
        private NotificationGateway $notificationGateway,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(EmployeeWelcomeEmailRequested $event): void
    {
        try {
            $this->notificationGateway->sendEmail(
                new EmailPayload(
                    to: $event->employeeEmail,
                    type: EmailType::EMPLOYEE_WELCOME,
                    subject: 'Bienvenue - Créez votre mot de passe',
                    context: [
                        'firstName' => $event->firstName,
                        'userEmail' => $event->employeeEmail->toString(),
                        'resetUrl' => $event->resetUrl,
                    ],
                )
            );

            $this->logger->info('Email de bienvenue envoyé avec succès', [
                'employeeEmail' => $event->employeeEmail->toString(),
                'employeeUuid' => $event->employeeUuid->toString(),
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error('Échec envoi email de bienvenue - retry programmé', [
                'employeeEmail' => $event->employeeEmail->toString(),
                'employeeUuid' => $event->employeeUuid->toString(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
