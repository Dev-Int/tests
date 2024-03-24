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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Unit\GetUnits;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functionalTest
 */
final class GetUnitsControllerTest extends WebTestCase
{
    private const GET_UNITS_URI = '/admin/units';

    public function testGetUnitsWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unitBuilder = new UnitDataBuilder();
        $unit1 = $unitBuilder->create('Kilogramme', 'kg')->build();
        $unit2 = $unitBuilder->create('Litre', 'L')->build();
        $unitRepository->save($unit1);
        $unitRepository->save($unit2);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::GET_UNITS_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Units');

        $list = $crawler->filter('body > div.container > div.row > article > ul.w100')->children('li.li-unstyled');

        self::assertCount(2, $list);
    }
}
