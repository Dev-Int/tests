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

namespace Admin\Tests\EndToEnd\Article;

use Admin\Adapters\Controller\Symfony\Controller\Article\CreateArticle\CreateArticleController;
use Admin\Adapters\Controller\Symfony\Controller\Article\GetArticles\GetArticlesController;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class CreateAnotherArticleTest extends BasePantherTestCase
{
    public function testCreateAnotherArticleSuccessfully(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $config = $this->createMinimalConfiguration();
        $supplier = $config['supplier'];
        $tax = $config['tax'];
        $unit = $config['unit'];
        $zoneStorage = $config['zoneStorage'];
        $familyLog = $config['familyLog'];

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.article.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.article.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));

        $client->clickLink($translator->trans('admin.article.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));
        $client->waitForVisibility('turbo-frame#article_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#article_create h3',
            $translator->trans('admin.article.create.titlePage')
        );

        $client->waitForVisibility('button[type="submit"]');

        $articleName = 'Nouvel Article';

        // Cliquer sur la famille logistique pour la sélectionner dans le select custom (liste accordéon)
        $client->getCrawler()->filter('#createArticle_familyLog0')->first()->click();

        $client->submitForm($translator->trans('add'), [
            'createArticle[name]' => $articleName,
            'createArticle[supplier]' => $supplier->uuid()->toString(),
            'createArticle[packaging][parcel][unit]' => $unit->uuid()->toString(),
            'createArticle[packaging][parcel][quantity]' => 1,
            'createArticle[packaging][subPackage][unit]' => $unit->uuid()->toString(),
            'createArticle[packaging][subPackage][quantity]' => 2,
            'createArticle[packaging][consumeUnit][unit]' => $unit->uuid()->toString(),
            'createArticle[packaging][consumeUnit][quantity]' => 3.5,
            'createArticle[unitPrice]' => 12.50,
            'createArticle[tax]' => $tax->uuid()->toString(),
            'createArticle[minStock]' => 5.0,
            'createArticle[zoneStorages]' => [$zoneStorage->uuid()->toString()],
            'createArticle[familyLog]' => $familyLog->uuid()->toString(),
            'createArticle[quantity]' => 10.0,
        ]);

        $client->wait(2);
        $getArticlesUrl = $router->generate(GetArticlesController::ROUTE_NAME);
        self::assertStringContainsString($getArticlesUrl, $client->getCurrentURL());

        $client->wait(1);
        self::assertSelectorTextContains('ul.table', $articleName);

        // Vérifier qu'on a quitté la page de création
        self::assertStringNotContainsString(
            $router->generate(CreateArticleController::ROUTE_NAME),
            $client->getCurrentURL()
        );
    }

    public function testCancelDuringAnotherArticleCreation(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $this->createMinimalConfiguration();

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.article.titlePage'));

        $client->wait(1);
        $client->waitForElementToContain('h1', $translator->trans('admin.article.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));

        $client->clickLink($translator->trans('admin.article.create.titleShort'));

        $client->wait(1);
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));
        $client->waitForVisibility('turbo-frame#article_create h3');
        self::assertSelectorTextContains(
            'turbo-frame#article_create h3',
            $translator->trans('admin.article.create.titlePage')
        );

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));

        $client->wait(2);
        $getArticlesUrl = $router->generate(GetArticlesController::ROUTE_NAME);
        self::assertStringContainsString($getArticlesUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));
    }
}
