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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageFamilyLog;

use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class ChangeZoneStorageFamilyLogControllerTest extends WebTestCase
{
    private const CHANGE_FAMILY_LOG_URI = '/admin/zone_storages/%s/change-family_log';

    public function testChangeFamilyLogWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $zoneStorageBuilder = new ZoneStorageDataBuilder();
        $familyLog1 = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLog2 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog1);
        $familyLogRepository->save($familyLog2);
        $zoneStorage = $zoneStorageBuilder->create('Réserve négative', $familyLog1)->build();
        $zoneStorageRepository->save($zoneStorage);
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        $familyLogOrm = $familyLogRepository->find($familyLog2->uuid()->toString());

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_FAMILY_LOG_URI, $zoneStorage->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change FamilyLog "Réserve négative"');

        $form = $crawler->selectButton('Change FamilyLog')->form([
            'changeZoneStorageFamilyLog[familyLog]' => $familyLogOrm?->uuid(),
            'changeZoneStorageFamilyLog[slug]' => $zoneStorage->slug(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages');

        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('Zone storage updated', $flash);

        /** @var ZoneStorage $zoneStorageUpdated */
        $zoneStorageUpdated = $zoneStorageRepository->findOneBy(['slug' => 'reserve-negative']);
        self::assertSame('Réserve négative', $zoneStorageUpdated->label());
        self::assertEquals('Frais', $zoneStorageUpdated->familyLog()->label());
    }

    public function testChangeFamilyLogFailWithZoneStorageNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $zoneStorageBuilder = new ZoneStorageDataBuilder();
        $familyLog1 = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLog2 = (new FamilyLogDataBuilder())->create('Frais')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog1);
        $familyLogRepository->save($familyLog2);
        $zoneStorage = $zoneStorageBuilder->create('Réserve négative', $familyLog1)->build();
        $zoneStorageRepository->save($zoneStorage);
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_FAMILY_LOG_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
