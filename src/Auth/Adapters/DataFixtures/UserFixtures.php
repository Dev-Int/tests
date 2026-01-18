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

use Auth\Entities\Role;
use Auth\Tests\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures pour créer un utilisateur admin par défaut.
 *
 * Utilisé pour :
 * - Développement local : connexion manuelle avec admin@tests.local / password
 * - Tests E2E (Panther) : connexion via le formulaire réel
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

        UserFactory::createOne([
            'email' => 'admin@tests.local',
            'password' => $hashedPassword,
            'roles' => [Role::ADMIN],
        ]);
    }
}
