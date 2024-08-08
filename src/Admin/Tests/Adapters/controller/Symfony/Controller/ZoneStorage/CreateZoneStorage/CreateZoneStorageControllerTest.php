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
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\Exception\NoFamilyLogRegisteredException;
use Admin\Entities\Exception\ZoneStorageAlreadyExistsException;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class CreateZoneStorageControllerTest extends WebTestCase
{
    private const CREATE_ZONE_STORAGE_URI = '/admin/zone_storages/create';

    public function testCreateZoneStorageWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogOrm = $familyLogRepository->find($familyLog->uuid()->toString());

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);

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
        self::assertResponseRedirects('/admin/zone_storages');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('Zone storage created', $flash);

        /** @var ZoneStorage $zoneStorageCreated */
        $zoneStorageCreated = $zoneStorageRepository->findOneBy(['slug' => 'reserve-negative']);
        self::assertSame('Réserve négative', $zoneStorageCreated->label());
        self::assertEquals('Surgelé', $zoneStorageCreated->familyLog()->label());
    }

    public function testCreateZoneStorageFailWithAlreadyExistsLabelException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);
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
        self::assertResponseRedirects('/admin/zone_storages');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(ZoneStorageAlreadyExistsException::MESSAGE, $flash);
    }

    public function testCreateZoneStorageFailWithNoFamilyLogRegisteredException(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $client->request(Request::METHOD_POST, self::CREATE_ZONE_STORAGE_URI);

        // Assert
        self::assertResponseRedirects('/admin/configure');

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoFamilyLogRegisteredException::MESSAGE, $flash);
    }
}
