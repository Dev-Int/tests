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

namespace Admin\Adapters\Gateway;

use Admin\UseCases\Gateway\EmailPayload;
use Admin\UseCases\Gateway\EmailType;
use Admin\UseCases\Gateway\NotificationGateway;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsAlias(NotificationGateway::class)]
final readonly class NotificationProvider implements NotificationGateway
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail,
        private string $fromName,
        private LoggerInterface $logger,
    ) {
    }

    public function sendEmail(EmailPayload $payload): void
    {
        $templateName = $this->getTemplateForType($payload->type);
        $context = [
            'type' => $payload->type->name,
            'to' => $payload->to->toString(),
            'subject' => $payload->subject,
        ];
        $this->logger->info('Envoi email', $context);

        try {
            $message = (new TemplatedEmail())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to(new Address($payload->to->toString()))
                ->subject($payload->subject)
                ->htmlTemplate($templateName)
                ->context($payload->context)
            ;

            $this->mailer->send($message);

            $this->logger->info('Email envoyé avec succès', $context);
        } catch (\Throwable $exception) {
            $this->logger->error('Échec envoi email', $context + [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    private function getTemplateForType(EmailType $type): string
    {
        return match ($type) {
            EmailType::EMPLOYEE_WELCOME => '@auth/email/employee_welcome.html.twig',
        };
    }
}
