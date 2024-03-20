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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\ZoneStorage\GetZoneStorages;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class GetZoneStoragesControllerTest extends WebTestCase
{
    private const GET_ZONE_STORAGES_URI = '/admin/zone_storages/';

    public function testGetZoneStoragesWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $zoneStorageBuilder = new ZoneStorageDataBuilder();
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $zoneStorage1 = $zoneStorageBuilder->create('Réserve négative', $familyLog)->build();
        $zoneStorage2 = $zoneStorageBuilder->create('Réserve positive', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage1);
        $zoneStorageRepository->save($zoneStorage2);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::GET_ZONE_STORAGES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Storage Zones');

        $list = $crawler->filter('body > div.container > div.row > article > ul.w100')->children('li.li-unstyled');
        self::assertCount(2, $list);
    }
}
