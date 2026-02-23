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

namespace Shared\Tests;

use Auth\Adapters\Gateway\ORM\Entity\User;
use Auth\Tests\Factory\UserFactory;
use Shared\Entities\Role;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Trait pour authentifier un utilisateur dans les tests fonctionnels.
 *
 * Ce trait fournit une méthode pour créer et connecter un utilisateur
 * dans le contexte de tests fonctionnels Symfony.
 */
trait AuthenticatedFunctionalTestTrait
{
    /**
     * Crée un utilisateur admin et l'authentifie pour le client HTTP.
     *
     * @param string|null $email L'email de l'utilisateur (optionnel)
     * @param list<Role>  $roles Les rôles de l'utilisateur (défaut: ROLE_ADMIN)
     *
     * @return User L'entité utilisateur créée
     */
    protected function authenticateUser(?string $email = null, array $roles = [Role::ADMIN]): User
    {
        $attributes = ['roles' => $roles];
        if ($email !== null) {
            $attributes['email'] = $email;
        }
        $user = UserFactory::createOne($attributes);

        $this->getHttpClient()->loginUser($user->_real());

        return $user->_real();
    }

    /**
     * Authentifie un utilisateur avec seulement ROLE_USER (sans ROLE_ADMIN).
     * Utile pour tester le refus d'accès aux routes admin.
     */
    protected function authenticateAsRoleUser(): User
    {
        return $this->authenticateUser(roles: [Role::USER]);
    }

    /**
     * Déconnecte l'utilisateur actuel pour tester les accès non-authentifiés.
     * Réinitialise le client HTTP pour effacer la session.
     */
    protected function logoutUser(): void
    {
        static::ensureKernelShutdown();
        $this->setHttpClient(static::createClient());
    }

    abstract protected function getHttpClient(): KernelBrowser;

    abstract protected function setHttpClient(KernelBrowser $client): void;
}
