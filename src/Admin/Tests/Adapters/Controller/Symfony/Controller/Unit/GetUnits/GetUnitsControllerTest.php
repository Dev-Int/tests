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

use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Entities\Exception\Unit\NoUnitRegisteredException;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class GetUnitsControllerTest extends BaseFunctionalTestCase
{
    private const GET_UNITS_URI = '/admin/units';

    public function testGetUnitsWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $unitBuilder = new UnitDataBuilder();
        $unit1 = $unitBuilder->create('Kilogramme', 'kg')->build();
        $unit2 = $unitBuilder->create('Litre', 'L')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $unitRepository->save($unit1);
        $unitRepository->save($unit2);

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

        self::assertSame(NoUnitRegisteredException::MESSAGE, $flash);
    }
}
