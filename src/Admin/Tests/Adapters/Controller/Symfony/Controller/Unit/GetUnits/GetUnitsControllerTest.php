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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Unit\GetUnits;

use Admin\Entities\Exception\Unit\NoUnitRegistered;
use Admin\Tests\Factory\UnitFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class GetUnitsControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string GET_UNITS_URI = '/admin/units';

    public function testGetUnitsWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        UnitFactory::createOne(['label' => 'Litre', 'abbreviation' => 'L']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_UNITS_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.unit.titlePage'));

        $list = $crawler
            ->filter('body > div.container > main > article > ul.table > turbo-frame')
            ->children('li.li-unstyled')
        ;
        self::assertCount(2, $list);
    }

    public function testGetUnitsFailWithNoUnitRegisteredException(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, self::GET_UNITS_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoUnitRegistered::MESSAGE, $flash);
    }

    protected function getProtectedUri(): string
    {
        return self::GET_UNITS_URI;
    }
}
