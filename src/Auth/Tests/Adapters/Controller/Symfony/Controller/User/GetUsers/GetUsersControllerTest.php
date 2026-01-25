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

namespace Auth\Tests\Adapters\Controller\Symfony\Controller\User\GetUsers;

use Auth\Tests\Factory\UserFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;

final class GetUsersControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const string USERS_URI = '/admin/users';

    public function testGetUsersPageIsAccessibleForAdmin(): void
    {
        // Act
        $this->client->request(Request::METHOD_GET, self::USERS_URI);

        // Assert
        self::assertResponseIsSuccessful('La page des utilisateurs doit être accessible pour un admin');
        self::assertSelectorExists('h1', 'Le titre de la page doit être affiché');
        self::assertSelectorExists('ul.table', 'La liste des utilisateurs doit être affichée');
        self::assertSelectorExists('turbo-frame#users_paginated', 'Le turbo-frame de pagination doit être présent');
        self::assertSelectorCount(
            1,
            'ul.table li.flex-between:not(.head)',
            'Doit afficher exactement 1 utilisateur (l\'admin connecté)'
        );
    }

    public function testGetUsersReturnsHttp403ForNonAdmin(): void
    {
        // Arrange
        $this->authenticateAsRoleUser();

        // Act
        $this->client->request(Request::METHOD_GET, self::USERS_URI);

        // Assert
        self::assertResponseStatusCodeSame(
            Response::HTTP_FORBIDDEN,
            'Un utilisateur sans ROLE_ADMIN ne doit pas pouvoir accéder à la page'
        );
    }

    public function testGetUsersDisplaysPaginatedResults(): void
    {
        // Arrange - Crée 25 utilisateurs + 1 admin par défaut = 26 total
        UserFactory::createMany(25);

        // Act - Page 1 avec 10 items par page
        $this->client->request(Request::METHOD_GET, self::USERS_URI . '?page=1&itemsPerPage=10');

        // Assert - Page 1
        self::assertResponseIsSuccessful('La page 1 doit se charger correctement');
        self::assertSelectorCount(
            10,
            'ul.table li.flex-between:not(.head)',
            'La page 1 doit afficher exactement 10 utilisateurs'
        );
        self::assertSelectorExists('turbo-frame#users_paginated', 'Le turbo-frame de pagination doit être présent');

        // Act - Page 2 avec 10 items par page
        $this->client->request(Request::METHOD_GET, self::USERS_URI . '?page=2&itemsPerPage=10');

        // Assert - Page 2
        self::assertResponseIsSuccessful('La page 2 doit se charger correctement');
        self::assertSelectorCount(
            10,
            'ul.table li.flex-between:not(.head)',
            'La page 2 doit afficher exactement 10 utilisateurs'
        );

        // Act - Page 3 avec 10 items par page
        $this->client->request(Request::METHOD_GET, self::USERS_URI . '?page=3&itemsPerPage=10');

        // Assert - Page 3
        self::assertResponseIsSuccessful('La page 3 doit se charger correctement');
        self::assertSelectorCount(
            6,
            'ul.table li.flex-between:not(.head)',
            'La page 3 doit afficher exactement 6 utilisateurs (26 total - 20 déjà affichés)'
        );
    }
}
