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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Supplier\CreateSupplier;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineZoneStorageRepository;
use Admin\Entities\Exception\NoZoneStorageRegisteredException;
use Admin\Entities\Exception\SupplierAlreadyExists;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Admin\Tests\DataBuilder\ZoneStorageDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\assertInstanceOf;

/**
 * @group functionalTest
 */
final class CreateSupplierControllerTest extends WebTestCase
{
    private const CREATE_SUPPLIER_URI = '/admin/suppliers/create';

    public function testCreateSupplierWillSucceed(): void
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
        assertInstanceOf(FamilyLog::class, $familyLogOrm);

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Reserve négative', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_SUPPLIER_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Supplier');

        $form = $crawler->selectButton('Create')->form([
            'createSupplier[name]' => 'Dev-Int Création',
            'createSupplier[address]' => '5, rue des Plantes',
            'createSupplier[postalCode]' => '75000',
            'createSupplier[town]' => 'Paris',
            'createSupplier[country]' => 'France',
            'createSupplier[phone]' => '+33297000000',
            'createSupplier[email]' => 'test@test.fr',
            'createSupplier[contact]' => 'Laurent',
            'createSupplier[cellphone]' => '+33600000000',
            'createSupplier[familyLog]' => $familyLogOrm->uuid(),
            'createSupplier[delayDelivery]' => 3,
            'createSupplier[orderDays][0]' => 0,
            'createSupplier[orderDays][3]' => 3,
            'createSupplier[orderDays][5]' => 5,
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertEquals('Supplier created', $flash);

        $supplierCreated = $supplierRepository->findOneBy(['slug' => 'dev-int-creation']);
        self::assertInstanceOf(Supplier::class, $supplierCreated);
        self::assertSame('Dev-Int Création', $supplierCreated->name());
        self::assertSame('5, rue des Plantes', $supplierCreated->address());
        self::assertSame('75000', $supplierCreated->postalCode());
        self::assertSame('Paris', $supplierCreated->town());
        self::assertSame('France', $supplierCreated->country());
        self::assertSame('+33297000000', $supplierCreated->phone());
        self::assertSame('test@test.fr', $supplierCreated->email());
        self::assertSame('Laurent', $supplierCreated->contact());
        self::assertSame('+33600000000', $supplierCreated->cellphone());
        self::assertSame('Surgelé', $supplierCreated->familyLog()->label());
        self::assertSame(3, $supplierCreated->delayDelivery());
        self::assertSame([0, 3, 5], $supplierCreated->orderDays());
        self::assertTrue($supplierCreated->active());
        self::assertSame('dev-int-creation', $supplierCreated->slug());
    }

    public function testCreateSupplierFailWithAlreadyExistsException(): void
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
        assertInstanceOf(FamilyLog::class, $familyLogOrm);

        /** @var DoctrineZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(DoctrineZoneStorageRepository::class);
        $zoneStorage = (new ZoneStorageDataBuilder())->create('Reserve négative', $familyLog)->build();
        $zoneStorageRepository->save($zoneStorage);

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);
        $supplier = (new SupplierDataBuilder())->create('Dev-Int Création', $familyLog)->build();
        $supplierRepository->save($supplier);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_SUPPLIER_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Supplier');

        $form = $crawler->selectButton('Create')->form([
            'createSupplier[name]' => 'Dev-Int Création',
            'createSupplier[address]' => '5, rue des Plantes',
            'createSupplier[postalCode]' => '75000',
            'createSupplier[town]' => 'Paris',
            'createSupplier[country]' => 'France',
            'createSupplier[phone]' => '+33297000000',
            'createSupplier[email]' => 'test@test.fr',
            'createSupplier[contact]' => 'Laurent',
            'createSupplier[cellphone]' => '+33600000000',
            'createSupplier[familyLog]' => $familyLogOrm->uuid(),
            'createSupplier[delayDelivery]' => 3,
            'createSupplier[orderDays][1]' => 1,
            'createSupplier[orderDays][4]' => 4,
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertEquals(SupplierAlreadyExists::MESSAGE, $flash);
    }

    public function testCreateSupplierFailWithNoZoneStorageRegisteredException(): void
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

        // Act
        $client->request(Request::METHOD_GET, self::CREATE_SUPPLIER_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertEquals(NoZoneStorageRegisteredException::MESSAGE, $flash);
    }
}
