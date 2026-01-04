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

use Admin\Adapters\Controller\Symfony\Controller\Article\GetArticles\GetArticlesController;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class ChangeFinancialInformationArticleCancelTest extends BasePantherTestCase
{
    public function testCancelDuringArticleChangeFinancialInformation(): void
    {
        // Arrange
        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $config = $this->createMinimalConfiguration();
        $article = $config['article'];

        // Act && Assert
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $client->clickLink($translator->trans('admin.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.titlePage'));

        $client->clickLink($translator->trans('admin.article.titlePage'));
        $client->waitForElementToContain('h1', $translator->trans('admin.article.titlePage'));
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));

        $changeFinancialInfoButtonText = $translator->trans('admin.article.changeFinancialInformation.button');
        $client->clickLink($changeFinancialInfoButtonText);
        $client->waitForVisibility(\sprintf('turbo-frame#article_%s h3', $article->uuid()->toString()));
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));
        self::assertSelectorTextContains(
            \sprintf('turbo-frame#article_%s h3', $article->uuid()->toString()),
            $translator->trans(
                'admin.article.changeFinancialInformation.titlePage',
                ['%articleName%' => $article->name()->toString()]
            )
        );

        $client->waitForVisibility('a[role="button"][aria-label="Cancel"]');

        $cancelButtonSelector = 'a[role="button"][aria-label="Cancel"]';
        self::assertSelectorExists($cancelButtonSelector);
        self::assertSelectorTextContains($cancelButtonSelector, $translator->trans('cancel'));

        $client->clickLink($translator->trans('cancel'));
        $client->waitForElementToContain('h1', $translator->trans('admin.article.titlePage'));
        $getArticlesUrl = $router->generate(GetArticlesController::ROUTE_NAME);
        self::assertStringContainsString($getArticlesUrl, $client->getCurrentURL());
        self::assertSelectorTextContains('h1', $translator->trans('admin.article.titlePage'));
    }
}
