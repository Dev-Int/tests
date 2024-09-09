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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageLabel;

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
final class ChangeZoneStorageLabelControllerTest extends WebTestCase
{
    private const CHANGE_LABEL_URI = '/admin/zone_storages/%s/change-label';

    public function testChangeLabelControllerWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $zoneStorageBuilder = new ZoneStorageDataBuilder();
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $zoneStorage = $zoneStorageBuilder->create('Réserve négative', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $zoneStorage->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Réserve négative"');

        $form = $crawler->selectButton('Change label')->form([
            'changeZoneStorageLabel[label]' => 'Réserve positive',
            'changeZoneStorageLabel[slug]' => 'reserve-negative',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('Zone storage updated', $flash);

        /** @var ZoneStorage $zoneStorageUpdated */
        $zoneStorageUpdated = $zoneStorageRepository->findOneBy(['slug' => 'reserve-positive']);
        self::assertSame('Réserve positive', $zoneStorageUpdated->label());
        self::assertEquals('Surgelé', $zoneStorageUpdated->familyLog()->label());
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);
    }

    public function testChangeLabelFailWithZoneStorageNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $zoneStorageBuilder = new ZoneStorageDataBuilder();
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $zoneStorage = $zoneStorageBuilder->create('Réserve négative', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
