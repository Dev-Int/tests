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

use Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages\GetZoneStoragesController;
use Admin\Entities\Repository\ZoneStorageRepository;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Faker\Factory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ChangeZoneStorageLabelControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CHANGE_LABEL_URI = '/admin/zone_storages/%s/change-label';

    public function testChangeLabelControllerWillSucceed(): void
    {
        // Arrange
        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $zoneStorage = ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog->_real(),
        ]);
        $zoneStorages = $zoneStorageRepository->getAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $zoneStorage->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.zoneStorage.changeLabel.titlePage',
                ['%zoneLabel%' => $zoneStorage->_real()->label()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.zoneStorage.changeLabel.button'))->form([
            'changeZoneStorageLabel[label]' => 'Réserve positive',
            'changeZoneStorageLabel[slug]' => 'reserve-negative',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.zoneStorage.changeLabel.success'), $flash);

        /** @var ZoneStorage $zoneStorageUpdated */
        $zoneStorageUpdated = $zoneStorageRepository->getBySlug('reserve-positive');
        self::assertSame('Réserve positive', $zoneStorageUpdated->label()->toString());
        self::assertEquals('Surgelé', $zoneStorageUpdated->familyLog()->label()->toString());
        $zoneStorages = $zoneStorageRepository->getAllZones();
        self::assertCount(1, $zoneStorages);
    }

    public function testChangeLabelFailWithZoneStorageNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog->_real(),
        ]);
        $zoneStorages = $zoneStorageRepository->getAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }

    public function testCancelDuringZoneStorageLabelChange(): void
    {
        // Arrange
        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $zoneStorage = ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog->_real(),
        ]);
        $zoneStorages = $zoneStorageRepository->getAllZones();
        self::assertCount(1, $zoneStorages);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $zoneStorage->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.zoneStorage.changeLabel.titlePage',
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
        $zoneStorageAfterCancel = $zoneStorageRepository->getBySlug('reserve-negative');
        self::assertSame('Réserve négative', $zoneStorageAfterCancel->label()->toString());
        self::assertEquals('Surgelé', $zoneStorageAfterCancel->familyLog()->label()->toString());

        $zoneStorages = $zoneStorageRepository->getAllZones();
        self::assertCount(1, $zoneStorages);
    }
}
