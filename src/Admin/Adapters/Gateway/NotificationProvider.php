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

use Admin\UseCases\Gateway\NotificationGateway;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsAlias(NotificationGateway::class)]
final readonly class NotificationProvider implements NotificationGateway
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private string $fromEmail,
        private string $fromName,
    ) {
    }

    public function sendEmployeeWelcomeEmail(
        EmailField $email,
        NameField $firstName,
        string $resetToken
    ): void {
        // Générer l'URL de reset (à adapter selon les routes du projet)
        $resetUrl = $this->urlGenerator->generate(
            'auth_password_reset',
            ['token' => $resetToken],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to(new Address($email->toString()))
            ->subject('Bienvenue - Créez votre mot de passe')
            ->htmlTemplate('@auth/email/employee_welcome.html.twig')
            ->context([
                'firstName' => $firstName->toString(),
                'userEmail' => $email->toString(),
                'resetUrl' => $resetUrl,
            ])
        ;

        $this->mailer->send($message);
    }
}
