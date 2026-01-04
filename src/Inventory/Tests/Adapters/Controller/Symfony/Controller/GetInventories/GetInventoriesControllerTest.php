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

namespace Inventory\Tests\Adapters\Controller\Symfony\Controller\GetInventories;

use Admin\Contracts\Services\Provider\Exception\NoArticleRegistered;
use Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController;
use Inventory\Tests\Story\InventoryStory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Inventory\Adapters\Controller\Symfony\Controller\GetInventories\GetInventoriesController
 */
final class GetInventoriesControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const string GET_INVENTORIES_URI = '/inventories';

    public function testGetInventoriesDisplaysListWhenConfigured(): void
    {
        // Arrange - InventoryStory charge toute la config Admin + Articles
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        InventoryStory::load();

        // Act
        $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('inventory.titlePage'));
    }

    public function testGetInventoriesRedirectsToConfigurationWhenNoArticles(): void
    {
        // Arrange - Base vide = pas d'articles configurés

        // Act
        $this->client->request(Request::METHOD_GET, self::GET_INVENTORIES_URI);

        // Assert - Doit rediriger vers /admin/configure
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        // Suivre la redirection et vérifier le flash
        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('.flash-error')->text();

        self::assertSame(NoArticleRegistered::MESSAGE, $flash);
    }

    public function testGetInventoriesRouteNameConstantExists(): void
    {
        // Assert
        self::assertTrue(\defined(GetInventoriesController::class . '::ROUTE_NAME'));
        self::assertSame('inventory_index', GetInventoriesController::ROUTE_NAME);
    }
}
