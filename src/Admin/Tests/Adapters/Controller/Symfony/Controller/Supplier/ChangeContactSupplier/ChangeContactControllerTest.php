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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Supplier\ChangeContactSupplier;

use Admin\Entities\Repository\SupplierRepository;
use Admin\Entities\Supplier\Supplier;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Faker\Factory;
use Shared\Tests\BaseFunctionalTestCase;
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ChangeContactControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string CHANGE_CONTACT_SUPPLIER = '/admin/suppliers/%s/change-contact';

    public function testChangeContactSupplierWillSucceed(): void
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
        $suppliers = $supplierRepository->getAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_CONTACT_SUPPLIER, $supplier->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans(
                'admin.supplier.changeContact.titlePage',
                ['%supplierName%' => $supplier->_real()->name()]
            )
        );

        $form = $crawler->selectButton($translator->trans('admin.supplier.changeContact.button'))->form([
            'changeContactSupplier[contact]' => 'David',
            'changeContactSupplier[cellphone]' => '+33600000001',
            'changeContactSupplier[slug]' => 'supplier-1',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/suppliers');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertEquals($translator->trans('admin.supplier.changeContact.success'), $flash);

        /** @var Supplier $supplierUpdated */
        $supplierUpdated = $supplierRepository->getBySlug('supplier-1');
        self::assertSame('David', $supplierUpdated->contact());
        self::assertSame('+33600000001', $supplierUpdated->cellphone()->toNumber());
    }

    public function testChangeContactSupplierFailWithSupplierNotFound(): void
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
        $suppliers = $supplierRepository->getAllSuppliers();
        self::assertCount(1, $suppliers);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_CONTACT_SUPPLIER, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }

    protected function getProtectedUri(): string
    {
        return \sprintf(self::CHANGE_CONTACT_SUPPLIER, '00000000-0000-0000-0000-000000000000');
    }
}
