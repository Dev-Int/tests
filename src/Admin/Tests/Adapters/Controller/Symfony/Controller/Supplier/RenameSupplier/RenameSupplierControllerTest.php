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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Supplier\RenameSupplier;

use Admin\Entities\Exception\Supplier\SupplierAlreadyExists;
use Admin\Entities\Supplier\Supplier as SupplierDomain;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Admin\UseCases\Gateway\SupplierRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class RenameSupplierControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const RENAME_SUPPLIER_URI = '/admin/suppliers/%s/rename';

    public function testRenameSupplierWillSucceed(): void
    {
        // Arrange
        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $supplier = SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        $suppliers = $supplierRepository->findAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_SUPPLIER_URI, $supplier->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.supplier.rename.titlePage', ['%supplierName%' => $supplier->_real()->name()])
        );

        $form = $crawler->selectButton($translator->trans('admin.supplier.rename.button'))->form([
            'renameSupplier[name]' => 'Supplier new',
            'renameSupplier[slug]' => 'supplier-1',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.supplier.rename.success'), $flash);

        /** @var SupplierDomain $supplierUpdated */
        $supplierUpdated = $supplierRepository->findBySlug('supplier-new');
        self::assertSame('Supplier new', $supplierUpdated->name()->toString());
        $suppliers = $supplierRepository->findAllSuppliers();
        self::assertCount(1, $suppliers->toArray());
    }

    public function testRenameSupplierFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $supplier1 = SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        SupplierFactory::createOne([
            'name' => 'Supplier new',
            'familyLog' => $familyLog->_real(),
        ]);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_SUPPLIER_URI, $supplier1->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.supplier.rename.titlePage', ['%supplierName%' => $supplier1->_real()->name()])
        );

        $form = $crawler->selectButton($translator->trans('admin.supplier.rename.button'))->form([
            'renameSupplier[name]' => 'Supplier new',
            'renameSupplier[slug]' => 'supplier-1',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(SupplierAlreadyExists::MESSAGE, $flash);
    }

    public function testRenameSupplierFailWithSupplierNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        SupplierFactory::createOne([
            'name' => 'Supplier 1',
            'familyLog' => $familyLog->_real(),
        ]);
        $suppliers = $supplierRepository->findAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::RENAME_SUPPLIER_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
