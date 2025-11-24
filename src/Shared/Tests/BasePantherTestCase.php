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

namespace App\Shared\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\Panther\PantherTestCase;

class BasePantherTestCase extends PantherTestCase
{
    protected ?AbstractDatabaseTool $databaseTool = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        self::stopWebServer();
        parent::setUp();

        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $databaseToolCollection->get();

        // Purge the database before each test for E2E tests
        $this->databaseTool->loadFixtures([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
    }

    /**
     * Force flush and clear entity manager so the Panther server can see the data.
     */
    protected function flushAndClearEntityManager(): void
    {
        /** @var Registry $doctrineService */
        $doctrineService = static::getContainer()->get('doctrine');
        $entityManager = $doctrineService->getManager();
        $entityManager->flush();
        $entityManager->clear();
    }
}
