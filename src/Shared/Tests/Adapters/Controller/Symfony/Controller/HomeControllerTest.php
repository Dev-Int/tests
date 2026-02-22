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

namespace Shared\Tests\Adapters\Controller\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * @group functionalTest
 *
 * @covers \Shared\Adapters\Controller\Symfony\Controller\HomeController
 */
final class HomeControllerTest extends WebTestCase
{
    private const HOME_URI = '/';

    public function testHomePageWithSuccess(): void
    {
        // Arrange
        $client = self::createClient();
        $siteName = self::getContainer()->getParameter('site.name');
        Assert::string($siteName);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::HOME_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('home.welcome'));

        $header = $crawler->filter('body > header > nav')->children('ul');
        self::assertSame($siteName, $header->first()->text());
        self::assertSame($translator->trans('admin.titlePage'), $header->last()->children('li')->first()->text());
    }
}
