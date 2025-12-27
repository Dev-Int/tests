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
 * Base class for functional tests.
 * Uses LiipTestFixturesBundle to reset database before each test.
 * This ensures data is visible to HTTP requests (unlike transaction-based isolation).
 */
abstract class BaseFunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected ?AbstractDatabaseTool $databaseTool = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset the clock to system time before each test
        ClockFactory::initialize(new SystemClock());

        // Create the client first to boot the kernel
        $this->client = static::createClient();

        // Get a database tool for resetting the database
        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $databaseToolCollection->get();

        // Reset database before each test
        $this->databaseTool->loadFixtures([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
    }
}
