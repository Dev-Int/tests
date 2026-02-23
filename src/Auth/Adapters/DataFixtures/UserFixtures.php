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

namespace Auth\Adapters\DataFixtures;

use Auth\Tests\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Shared\Entities\Role;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures pour créer les utilisateurs par défaut (un par rôle).
 *
 * Utilisé pour :
 * - Développement local : connexion manuelle avec {email} / password
 * - Tests E2E (Panther) : connexion via le formulaire réel
 *
 * @see docs/testing.md pour la liste des comptes disponibles
 */
final class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un utilisateur temporaire pour hasher le mot de passe
        $tempUser = UserFactory::new()->withoutPersisting()->create();
        $hashedPassword = $this->passwordHasher->hashPassword($tempUser->_real(), 'password');

        // Admin - accès complet
        UserFactory::createOne([
            'email' => 'admin@tests.local',
            'password' => $hashedPassword,
            'roles' => [Role::ADMIN],
        ]);

        // Inventory Manager - gestion des stocks
        UserFactory::createOne([
            'email' => 'inventory_manager@tests.local',
            'password' => $hashedPassword,
            'roles' => [Role::INVENTORY_MANAGER],
        ]);

        // User standard - accès limité
        UserFactory::createOne([
            'email' => 'user@tests.local',
            'password' => $hashedPassword,
            'roles' => [Role::USER],
        ]);
    }
}
