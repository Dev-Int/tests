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
use Admin\Adapters\Gateway\ORM\Repository\DoctrineArticleRepository;
use Admin\Tests\DataBuilder\ArticleDataBuilder;
use Faker\Factory;
use Shared\Tests\BasePantherTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group e2eTest
 */
final class ArticlesPaginationTest extends BasePantherTestCase
{
    private const int DEFAULT_ITEMS_PER_PAGE = 25;
    private const int ARTICLES_FOR_THREE_PAGES = 75;
    private const int ARTICLES_WITH_PARTIAL_LAST_PAGE = 65;

    public function testNavigateToSecondPage(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME));
        $client->waitForElementToContain('h1', $translator->trans('admin.article.titlePage'));

        self::assertSelectorExists('a.page-link.active[aria-label="Current"]');
        self::assertSelectorTextContains('a.page-link.active', '1');

        $client->clickLink('2');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        $crawler = $client->getCrawler();
        self::assertSame(
            '2',
            trim($crawler->filter('a.page-link.active')->text()),
            'La page active devrait être la page 2'
        );
    }

    public function testNavigateToThirdPage(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME));
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Le lien '3' n'est pas visible depuis la page 1, il faut d'abord aller à la page 2
        $client->clickLink('>');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        self::assertSelectorTextContains('a.page-link.active', '2');

        // Maintenant on peut cliquer sur '3'
        $client->clickLink('3');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        $crawler = $client->getCrawler();
        self::assertSame(
            '3',
            trim($crawler->filter('a.page-link.active')->text()),
            'La page active devrait être la page 3'
        );
    }

    public function testNavigateToLastPage(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME));
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        $client->clickLink('»');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        $crawler = $client->getCrawler();
        self::assertSame(
            '3',
            trim($crawler->filter('a.page-link.active')->text()),
            'La page active devrait être la dernière page (3)'
        );
    }

    public function testNavigateBackToFirstPage(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME, ['page' => 2]));
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        self::assertSelectorTextContains('a.page-link.active', '2');

        $client->clickLink('1');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        $crawler = $client->getCrawler();
        self::assertSame(
            '1',
            trim($crawler->filter('a.page-link.active')->text()),
            'La page active devrait être la page 1'
        );
    }

    public function testCorrectNumberOfItemsPerPage(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act - Page 1
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME, ['page' => 1]));
        $client->waitForVisibility('ul.table > turbo-frame[id^="article_"]');

        // Assert
        $crawler = $client->getCrawler();
        $articles = $crawler->filter('ul.table > turbo-frame[id^="article_"]');
        self::assertCount(
            self::DEFAULT_ITEMS_PER_PAGE,
            $articles,
            \sprintf('La première page devrait afficher %d articles', self::DEFAULT_ITEMS_PER_PAGE)
        );

        // Act - Page 2
        $client->clickLink('2');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        $crawler = $client->getCrawler();
        $articles = $crawler->filter('ul.table > turbo-frame[id^="article_"]');
        self::assertCount(
            self::DEFAULT_ITEMS_PER_PAGE,
            $articles,
            \sprintf('La deuxième page devrait afficher %d articles', self::DEFAULT_ITEMS_PER_PAGE)
        );

        // Act - Page 3
        $client->clickLink('3');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        $crawler = $client->getCrawler();
        $articles = $crawler->filter('ul.table > turbo-frame[id^="article_"]');
        self::assertCount(
            self::DEFAULT_ITEMS_PER_PAGE,
            $articles,
            \sprintf('La troisième page devrait afficher %d articles', self::DEFAULT_ITEMS_PER_PAGE)
        );
    }

    public function testLastPageWithPartialItems(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_WITH_PARTIAL_LAST_PAGE);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME, ['page' => 3]));
        $client->waitForVisibility('ul.table > turbo-frame[id^="article_"]');

        // Assert
        $expectedItemsOnLastPage = self::ARTICLES_WITH_PARTIAL_LAST_PAGE - (2 * self::DEFAULT_ITEMS_PER_PAGE);
        $articles = $client->getCrawler()->filter('ul.table > turbo-frame[id^="article_"]');
        self::assertCount(
            $expectedItemsOnLastPage,
            $articles,
            \sprintf(
                'La dernière page devrait afficher %d articles (%d total / %d par page)',
                $expectedItemsOnLastPage,
                self::ARTICLES_WITH_PARTIAL_LAST_PAGE,
                self::DEFAULT_ITEMS_PER_PAGE
            )
        );

        self::assertSelectorTextContains('a.page-link.active', '3');
    }

    public function testNextButtonNavigation(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act - Page 1 -> Page 2
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME));
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        self::assertSelectorTextContains('a.page-link.active', '1');

        $client->clickLink('>');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert - On page 2
        $crawler = $client->getCrawler();
        self::assertSame(
            '2',
            trim($crawler->filter('a.page-link.active')->text()),
            'Après avoir cliqué sur ">", on devrait être sur la page 2'
        );

        // Act - Page 2 -> Page 3
        $client->clickLink('>');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert - On page 3
        $crawler = $client->getCrawler();
        self::assertSame(
            '3',
            trim($crawler->filter('a.page-link.active')->text()),
            'Après avoir cliqué à nouveau sur ">", on devrait être sur la page 3'
        );
    }

    public function testPreviousButtonNavigation(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act - Start on page 3
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME, ['page' => 3]));
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        self::assertSelectorTextContains('a.page-link.active', '3');

        // Act - Page 3 -> Page 2
        $client->clickLink('<');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert - On page 2
        $crawler = $client->getCrawler();
        self::assertSame(
            '2',
            trim($crawler->filter('a.page-link.active')->text()),
            'Après avoir cliqué sur "<", on devrait être sur la page 2'
        );

        // Act - Page 2 -> Page 1
        $client->clickLink('<');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert - On page 1
        $crawler = $client->getCrawler();
        self::assertSame(
            '1',
            trim($crawler->filter('a.page-link.active')->text()),
            'Après avoir cliqué à nouveau sur "<", on devrait être sur la page 1'
        );
    }

    public function testNextButtonDisabledOnLastPage(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act - Go to the last page
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME, ['page' => 3]));
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        self::assertSelectorTextContains('a.page-link.active', '3');

        $crawler = $client->getCrawler();
        $nextLink = $crawler->filter('a[aria-label="Next"]')->first();
        $class = $nextLink->attr('class');

        self::assertNotNull($class, 'Le bouton "Next" devrait avoir un attribut class');
        self::assertStringContainsString(
            'disable-link',
            $class,
            'Le bouton "Next" sur la dernière page devrait avoir la classe "disable-link"'
        );

        // Vérifier que le lien pointe vers page=4 (qui n'existe pas)
        $href = $nextLink->attr('href');
        self::assertStringContainsString(
            'page=4',
            $href ?? '',
            'Le bouton "Next" sur la dernière page devrait pointer vers page 4 (non existante)'
        );
    }

    public function testPreviousButtonDisabledOnFirstPage(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act - Go to first page
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME));
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        self::assertSelectorTextContains('a.page-link.active', '1');

        // Le bouton "Previous" devrait avoir la classe "disable-link"
        $crawler = $client->getCrawler();
        $prevLink = $crawler->filter('a[aria-label="Previous"]')->first();
        $class = $prevLink->attr('class');

        self::assertNotNull($class, 'Le bouton "Previous" devrait avoir un attribut class');
        self::assertStringContainsString(
            'disable-link',
            $class,
            'Le bouton "Previous" sur la première page devrait avoir la classe "disable-link"'
        );

        // Vérifier que le lien pointe vers page=0
        $href = $prevLink->attr('href');
        self::assertStringContainsString(
            'page=0',
            $href ?? '',
            'Le bouton "Previous" sur la première page devrait pointer vers page 0'
        );
    }

    public function testChangeItemsPerPageTo50(): void
    {
        // Arrange - Créer 75 articles pour avoir 3 pages avec 25 items, mais 2 pages avec 50 items
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME));
        $client->waitForVisibility('ul.table > turbo-frame[id^="article_"]');

        $crawler = $client->getCrawler();
        $articles = $crawler->filter('ul.table > turbo-frame[id^="article_"]');
        self::assertCount(self::DEFAULT_ITEMS_PER_PAGE, $articles, 'Par défaut, on devrait avoir 25 articles');

        $form = $crawler->selectButton('Pagination')->form();

        /** @var ChoiceFormField $itemsPerPageSelect */
        $itemsPerPageSelect = $form->get('itemsPerPage');
        $itemsPerPageSelect->select('50');
        $client->submit($form);
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        $crawler = $client->getCrawler();
        $articles = $crawler->filter('ul.table > turbo-frame[id^="article_"]');
        self::assertCount(
            50,
            $articles,
            'Après avoir sélectionné 50 items par page, on devrait voir 50 articles'
        );

        self::assertStringContainsString('itemsPerPage=50', $client->getCurrentURL());

        $page3Links = $crawler->filter('a')->reduce(static function ($node) {
            return trim($node->text()) === '3';
        });
        self::assertCount(0, $page3Links, 'Il ne devrait pas y avoir de lien vers la page 3');
    }

    public function testItemsPerPagePersistsAcrossPages(): void
    {
        // Arrange
        $this->createArticlesForPagination(self::ARTICLES_FOR_THREE_PAGES);

        $client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        // Act - Changer pour 50 items par page
        $client->request('GET', $router->generate(GetArticlesController::ROUTE_NAME));
        $client->waitForVisibility('ul.table > turbo-frame[id^="article_"]');

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Pagination')->form();

        /** @var ChoiceFormField $itemsPerPageSelect */
        $itemsPerPageSelect = $form->get('itemsPerPage');
        $itemsPerPageSelect->select('50');
        $client->submit($form);
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        self::assertStringContainsString('itemsPerPage=50', $client->getCurrentURL());

        $client->clickLink('2');
        $client->waitForVisibility('a.page-link.active[aria-label="Current"]');

        // Assert
        self::assertStringContainsString('itemsPerPage=50', $client->getCurrentURL());

        $selectedOption = $client->getCrawler()->filter('select[name="itemsPerPage"] option[selected]')->text();
        self::assertSame('50', trim($selectedOption), 'Le sélecteur devrait toujours afficher 50 items par page');

        $crawler = $client->getCrawler();
        $articles = $crawler->filter('ul.table > turbo-frame[id^="article_"]');
        self::assertCount(
            25,
            $articles,
            'La page 2 devrait afficher les 25 articles restants'
        );
    }

    private function createArticlesForPagination(int $count): void
    {
        $faker = Factory::create('fr_FR');
        $config = $this->createMinimalConfiguration();
        $supplier = $config['supplier'];
        $tax = $config['tax'];
        $unit = $config['unit'];
        $zoneStorage = $config['zoneStorage'];
        $familyLog = $config['familyLog'];

        /** @var DoctrineArticleRepository $articleRepository */
        $articleRepository = self::getContainer()->get(DoctrineArticleRepository::class);

        // createMinimalConfiguration() crée déjà 1 article, on en ajoute (count - 1)
        for ($i = 2; $i <= $count; $i++) {
            $article = (new ArticleDataBuilder())->create(
                \sprintf('Article %03d', $i),
                $supplier,
                $tax,
                [$zoneStorage],
                $familyLog,
                [[$unit, 1.0], null, null]
            )
                ->withUuid($faker->uuid())
                ->build()
            ;
            $articleRepository->save($article);
        }

        $this->flushAndClearEntityManager();
    }
}
