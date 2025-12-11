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

use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages\GetZoneStoragesController;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ChangeZoneStorageFamilyLogControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CHANGE_FAMILY_LOG_URI = '/admin/zone_storages/%s/change-family_log';

    public function testChangeFamilyLogWillSucceed(): void
    {
        // Arrange
        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog1 = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLog2 = FamilyLogFactory::createOne(['label' => 'Frais']);
        $zoneStorage = ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog1->_real(),
        ]);
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_FAMILY_LOG_URI, $zoneStorage->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.zoneStorage.changeFamilyLog.titlePage',
                ['%zoneLabel%' => $zoneStorage->_real()->label()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.zoneStorage.changeFamilyLog.button'))->form([
            'changeZoneStorageFamilyLog[familyLog]' => $familyLog2->_real()->uuid(),
            'changeZoneStorageFamilyLog[slug]' => $zoneStorage->_real()->slug(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages');

        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.zoneStorage.changeFamilyLog.success'), $flash);

        /** @var ZoneStorage $zoneStorageUpdated */
        $zoneStorageUpdated = $zoneStorageRepository->findBySlug('reserve-negative');
        self::assertSame('Réserve négative', $zoneStorageUpdated->label()->toString());
        self::assertEquals(
            'Frais',
            $zoneStorageUpdated->familyLog()->label()->toString()
        );
    }

    public function testChangeFamilyLogFailWithZoneStorageNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        FamilyLogFactory::createOne(['label' => 'Frais']);
        ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog->_real(),
        ]);
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_FAMILY_LOG_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }

    public function testCancelDuringZoneStorageFamilyLogChange(): void
    {
        // Arrange
        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog1 = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        FamilyLogFactory::createOne(['label' => 'Frais']);
        $zoneStorage = ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog1->_real(),
        ]);
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_FAMILY_LOG_URI, $zoneStorage->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.zoneStorage.changeFamilyLog.titlePage',
                ['%zoneLabel%' => $zoneStorage->_real()->label()]
            )
        );

        $cancelLink = $crawler->selectLink($translator->trans('cancel'));
        self::assertCount(1, $cancelLink, 'Cancel link should exist');

        $this->client->click($cancelLink->link());

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame(GetZoneStoragesController::ROUTE_NAME);

        /** @var ZoneStorage $zoneStorageAfterCancel */
        $zoneStorageAfterCancel = $zoneStorageRepository->findBySlug('reserve-negative');
        self::assertSame('Réserve négative', $zoneStorageAfterCancel->label()->toString());
        self::assertEquals('Surgelé', $zoneStorageAfterCancel->familyLog()->label()->toString());

        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);
    }
}
