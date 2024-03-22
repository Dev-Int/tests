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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\ZoneStorage\ChangeZoneStorageLabel;

use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ChangeZoneStorageLabelControllerTest extends WebTestCase
{
    private const CHANGE_LABEL_URI = '/admin/zone_storages/%s/change-label';

    public function testChangeLabelControllerWillSucceed(): void
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
        $zoneStorageRepository->save($zoneStorage1);
        $zoneStorages = $zoneStorageRepository->findAllZone();
        self::assertCount(1, $zoneStorages);

        // Act
        $crawler = $client->request(Request::METHOD_GET, sprintf(self::CHANGE_LABEL_URI, 'reserve-negative'));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Réserve négative"');

        $form = $crawler->selectButton('Change label')->form([
            'changeLabelZoneStorage[label]' => 'Réserve positive',
            'changeLabelZoneStorage[slug]' => 'reserve-negative',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('Zone storage updated', $flash);

        /** @var ZoneStorage $zoneStorageUpdated */
        $zoneStorageUpdated = $zoneStorageRepository->findOneBy(['slug' => 'reserve-positive']);
        self::assertSame('Réserve positive', $zoneStorageUpdated->label());
        self::assertEquals('Surgelé', $zoneStorageUpdated->familyLog()->label());
        $zoneStorages = $zoneStorageRepository->findAllZone();
        self::assertCount(1, $zoneStorages);
    }
}
