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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\ZoneStorage\CreateZoneStorage;

use Admin\Adapters\Gateway\ORM\Entity\ZoneStorage;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\Exception\ZoneStorageAlreadyExistsException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateZoneStorageControllerTest extends WebTestCase
{
    private const CREATE_ZONE_STORAGE_URI = '/admin/zone_storages/create';

    public function testCreateZoneStorageWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogOrm = $familyLogRepository->find($familyLog->uuid()->toString());

        // Act
        $crawler = $client->request(Request::METHOD_POST, self::CREATE_ZONE_STORAGE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Zone Storage');

        $form = $crawler->selectButton('Create')->form([
            'createZoneStorage[label]' => 'Réserve négative',
            'createZoneStorage[familyLog]' => $familyLogOrm?->uuid(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('Zone storage created', $flash);

        /** @var ZoneStorage $zoneStorageCreated */
        $zoneStorageCreated = $zoneStorageRepository->findOneBy(['slug' => 'reserve-negative']);
        self::assertSame('Réserve négative', $zoneStorageCreated->label());
        self::assertEquals('Surgelé', $zoneStorageCreated->familyLog()->label());
    }

    public function testCreateZoneStorageFailWithAlreadyExistsLabel(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Réserve négative', $familyLog)
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $zoneStorageRepository->save($zoneStorage);

        $familyLogOrm = $familyLogRepository->find($familyLog->uuid()->toString());

        // Act
        $crawler = $client->request(Request::METHOD_POST, self::CREATE_ZONE_STORAGE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Zone Storage');

        $form = $crawler->selectButton('Create')->form([
            'createZoneStorage[label]' => 'Réserve négative',
            'createZoneStorage[familyLog]' => $familyLogOrm?->uuid(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame(ZoneStorageAlreadyExistsException::MESSAGE, $flash);
    }
}
