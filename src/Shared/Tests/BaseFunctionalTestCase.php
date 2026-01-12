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

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Shared\Entities\Clock\ClockFactory;
use Shared\Entities\Clock\SystemClock;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Classe de base pour les tests fonctionnels.
 * Utilise LiipTestFixturesBundle pour réinitialiser la base de données avant chaque test.
 * Cela garantit que les données sont visibles pour les requêtes HTTP (contrairement à l'isolation par transaction).
 */
abstract class BaseFunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected ?AbstractDatabaseTool $databaseTool = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Réinitialise l'horloge à l'heure système avant chaque test
        ClockFactory::initialize(new SystemClock());

        // Crée le client en premier pour démarrer le kernel
        $this->client = static::createClient();

        // Récupère l'outil de base de données pour la réinitialisation
        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $databaseToolCollection->get();

        // Réinitialise la base de données avant chaque test
        $this->databaseTool->loadFixtures([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
    }
}
