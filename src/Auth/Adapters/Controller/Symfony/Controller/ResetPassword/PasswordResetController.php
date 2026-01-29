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

namespace Auth\Adapters\Controller\Symfony\Controller\ResetPassword;

use Auth\Adapters\Form\Type\PasswordResetType;
use Auth\Adapters\Gateway\ORM\Entity\PasswordResetToken;
use Auth\Adapters\Gateway\ORM\Repository\DoctrinePasswordResetTokenRepository;
use Auth\UseCases\ResetPassword\ResetPassword;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class PasswordResetController extends AbstractController
{
    public const string ROUTE_NAME = 'auth_password_reset';

    public function __construct(
        private readonly DoctrinePasswordResetTokenRepository $repository,
        private readonly ResetPassword $useCase
    ) {
    }

    #[Route(path: '/password-reset/{token}', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function __invoke(string $token, Request $request): Response
    {
        $tokenEntity = $this->repository->findByToken($token);

        if (!$tokenEntity instanceof PasswordResetToken || $tokenEntity->isExpired() || $tokenEntity->isUsed()) {
            $this->addFlash('error', 'Le token de réinitialisation de mot de passe est invalide ou a expiré.');

            return $this->redirectToRoute('auth_login');
        }

        $form = $this->createForm(PasswordResetType::class, [], [
            'action' => $this->generateUrl(self::ROUTE_NAME, ['token' => $token]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{password: string} $data */
            $data = $form->getData();

            try {
                $this->useCase->execute(
                    new ResetPasswordHttpRequest(
                        $tokenEntity->toDomain(),
                        $data['password']
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Exception) {
                $this->addFlash(
                    'error',
                    'Une erreur inattendue est survenue lors du réinitialisation du mot de passe.'
                );
            }
            $this->addFlash('success', 'Mot de passe réinitialisé avec succès.');

            return $this->redirectToRoute('auth_login');
        }

        return $this->render('@auth/password_reset.html.twig', [
            'form' => $form,
        ]);
    }
}
