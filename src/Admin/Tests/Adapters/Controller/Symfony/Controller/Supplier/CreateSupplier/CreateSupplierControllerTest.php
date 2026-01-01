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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Supplier\CreateSupplier;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Entities\Exception\Supplier\SupplierAlreadyExists;
use Admin\Entities\Exception\ZoneStorage\NoZoneStorageRegistered;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

use function PHPUnit\Framework\assertInstanceOf;

/**
 * @group functionalTest
 */
final class CreateSupplierControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CREATE_SUPPLIER_URI = '/admin/suppliers/create';

    public function testCreateSupplierWillSucceed(): void
    {
        // Arrange
        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLogOrm = $familyLogRepository->find($familyLog->_real()->uuid());
        assertInstanceOf(FamilyLog::class, $familyLogOrm);

        ZoneStorageFactory::createOne([
            'label' => 'Reserve négative',
            'familyLog' => $familyLog->_real(),
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_SUPPLIER_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createSupplier[name]' => 'Dev-Int Création',
            'createSupplier[streetAddress]' => '5, rue des Plantes',
            'createSupplier[postalCode]' => '75000',
            'createSupplier[city]' => 'Paris',
            'createSupplier[country]' => 'France',
            'createSupplier[phone]' => '+33297000000',
            'createSupplier[email]' => 'test@test.fr',
            'createSupplier[contact]' => 'Laurent',
            'createSupplier[cellphone]' => '+33600000000',
            'createSupplier[familyLog]' => $familyLogOrm->uuid(),
            'createSupplier[delayDelivery]' => 3,
            'createSupplier[orderDays][0]' => true,
            'createSupplier[orderDays][1]' => false,
            'createSupplier[orderDays][2]' => false,
            'createSupplier[orderDays][3]' => true,
            'createSupplier[orderDays][4]' => false,
            'createSupplier[orderDays][5]' => true,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.supplier.create.success'), $flash);

        $supplierCreated = $supplierRepository->findOneBy(['slug' => 'dev-int-creation']);
        self::assertInstanceOf(Supplier::class, $supplierCreated);
        self::assertSame('Dev-Int Création', $supplierCreated->name());
        self::assertSame('5, rue des Plantes', $supplierCreated->address());
        self::assertSame('75000', $supplierCreated->postalCode());
        self::assertSame('Paris', $supplierCreated->city());
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
        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLogOrm = $familyLogRepository->find($familyLog->_real()->uuid());
        assertInstanceOf(FamilyLog::class, $familyLogOrm);

        ZoneStorageFactory::createOne([
            'label' => 'Reserve négative',
            'familyLog' => $familyLog->_real(),
        ]);
        SupplierFactory::createOne([
            'name' => 'Dev-Int Création',
            'familyLog' => $familyLog->_real(),
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_SUPPLIER_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createSupplier[name]' => 'Dev-Int Création',
            'createSupplier[streetAddress]' => '5, rue des Plantes',
            'createSupplier[postalCode]' => '75000',
            'createSupplier[city]' => 'Paris',
            'createSupplier[country]' => 'France',
            'createSupplier[phone]' => '+33297000000',
            'createSupplier[email]' => 'test@test.fr',
            'createSupplier[contact]' => 'Laurent',
            'createSupplier[cellphone]' => '+33600000000',
            'createSupplier[familyLog]' => $familyLogOrm->uuid(),
            'createSupplier[delayDelivery]' => 3,
            'createSupplier[orderDays][0]' => false,
            'createSupplier[orderDays][1]' => true,
            'createSupplier[orderDays][2]' => false,
            'createSupplier[orderDays][3]' => false,
            'createSupplier[orderDays][4]' => true,
            'createSupplier[orderDays][5]' => false,
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(SupplierAlreadyExists::MESSAGE, $flash);
    }

    public function testCreateSupplierFailWithNoZoneStorageRegisteredException(): void
    {
        // Arrange
        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        FamilyLogFactory::createOne(['label' => 'Surgelé']);

        // Act
        $this->client->request(Request::METHOD_GET, self::CREATE_SUPPLIER_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(NoZoneStorageRegistered::MESSAGE, $flash);
    }
}
