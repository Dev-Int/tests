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
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class ChangeZoneStorageLabelControllerTest extends BaseFunctionalTestCase
{
    private const CHANGE_LABEL_URI = '/admin/zone_storages/%s/change-label';

    public function testChangeLabelControllerWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

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
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $zoneStorage->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.zoneStorage.changeLabel.titlePage',
                ['%zoneLabel%' => $zoneStorage->label()->toString()]
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

        /** @var \Admin\Entities\ZoneStorage\ZoneStorage $zoneStorageUpdated */
        $zoneStorageUpdated = $zoneStorageRepository->findBySlug('reserve-positive');
        self::assertSame('Réserve positive', $zoneStorageUpdated->label()->toString());
        self::assertEquals('Surgelé', $zoneStorageUpdated->familyLog()->label()->toString());
        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);
    }

    public function testChangeLabelFailWithZoneStorageNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);
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
        $faker = Factory::create('fr_FR');

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

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
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_URI, $zoneStorage->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.zoneStorage.changeLabel.titlePage',
                ['%zoneLabel%' => $zoneStorage->label()->toString()]
            )
        );

        $cancelLink = $crawler->selectLink($translator->trans('cancel'));
        self::assertCount(1, $cancelLink, 'Cancel link should exist');

        $this->client->click($cancelLink->link());

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame(GetZoneStoragesController::ROUTE_NAME);

        /** @var \Admin\Entities\ZoneStorage\ZoneStorage $zoneStorageAfterCancel */
        $zoneStorageAfterCancel = $zoneStorageRepository->findBySlug('reserve-negative');
        self::assertSame('Réserve négative', $zoneStorageAfterCancel->label()->toString());
        self::assertEquals('Surgelé', $zoneStorageAfterCancel->familyLog()->label()->toString());

        $zoneStorages = $zoneStorageRepository->findAllZones();
        self::assertCount(1, $zoneStorages);
    }
}
