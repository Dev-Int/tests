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
     * Crée un utilisateur et l'authentifie pour le client HTTP.
     *
     * @param string|null $email L'email de l'utilisateur (optionnel, génère un email aléatoire par défaut)
     *
     * @return User L'entité utilisateur créée
     */
    protected function authenticateUser(?string $email = null): User
    {
        $attributes = $email !== null ? ['email' => $email] : [];
        $user = UserFactory::createOne($attributes);

        $this->getHttpClient()->loginUser($user->_real());

        return $user->_real();
    }

    abstract protected function getHttpClient(): KernelBrowser;
}
