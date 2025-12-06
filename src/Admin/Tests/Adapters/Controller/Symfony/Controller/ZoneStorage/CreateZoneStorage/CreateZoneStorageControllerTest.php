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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\ZoneStorage\CreateZoneStorage;

use Admin\Entities\Exception\FamilyLog\NoFamilyLogRegisteredException;
use Admin\Entities\Exception\ZoneStorage\ZoneStorageAlreadyExistsException;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Admin\UseCases\Gateway\CompanyRepository;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\TaxRepository;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class CreateZoneStorageControllerTest extends BaseFunctionalTestCase
{
    private const CREATE_ZONE_STORAGE_URI = '/admin/zone_storages/create';

    public function testCreateZoneStorageWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_ZONE_STORAGE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createZoneStorage[label]' => 'Réserve négative',
            'createZoneStorage[familyLog]' => $familyLog->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.zoneStorage.create.success'), $flash);

        /** @var ZoneStorage $zoneStorageCreated */
        $zoneStorageCreated = $zoneStorageRepository->findBySlug('reserve-negative');
        self::assertSame('Réserve négative', $zoneStorageCreated->label()->toString());
        self::assertEquals('Surgelé', $zoneStorageCreated->familyLog()->label()->toString());
    }

    public function testCreateZoneStorageFailWithAlreadyExistsLabelException(): void
    {
        // Arrange

        /** @var CompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(CompanyRepository::class);

        /** @var UnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(UnitRepository::class);

        /** @var TaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(TaxRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')->build();
        $familyLogRepository->save($familyLog);

        $zoneStorage = (new ZoneStorageDataBuilder())
            ->create('Réserve négative', $familyLog)
            ->build()
        ;
        $zoneStorageRepository->save($zoneStorage);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_ZONE_STORAGE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createZoneStorage[label]' => 'Réserve négative',
            'createZoneStorage[familyLog]' => $familyLog->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(ZoneStorageAlreadyExistsException::MESSAGE, $flash);
    }

    public function testCreateZoneStorageFailWithNoFamilyLogRegisteredException(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_POST, self::CREATE_ZONE_STORAGE_URI);

        // Assert
        self::assertResponseRedirects('/admin/configure');

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoFamilyLogRegisteredException::MESSAGE, $flash);
    }
}
