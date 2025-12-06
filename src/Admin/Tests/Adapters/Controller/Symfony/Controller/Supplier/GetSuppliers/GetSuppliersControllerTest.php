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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Supplier\GetSuppliers;

use Admin\Adapters\Gateway\Pagination\Pagination;
use Admin\Entities\Exception\Supplier\NoSupplierRegisteredException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\SupplierRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class GetSuppliersControllerTest extends BaseFunctionalTestCase
{
    private const GET_SUPPLIERS_URI = '/admin/suppliers';

    public function testGetSuppliersWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(SupplierRepository::class);

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $supplierBuilder = new SupplierDataBuilder();
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        for ($i = 0; $i < 30; $i++) {
            $supplier = $supplierBuilder->create($faker->company(), $familyLog)
                ->withUuid($faker->uuid())
                ->build()
            ;
            $supplierRepository->save($supplier);
        }

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_SUPPLIERS_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.supplier.titlePage'));

        $list = $crawler->filter('body > div.container > main > article > turbo-frame > ul.table > turbo-frame')
            ->children('li.li-unstyled')
        ;
        self::assertCount(Pagination::DEFAULT_ITEMS_PER_PAGE, $list);
    }

    public function testGetSuppliersFailWithNoSupplierRegisteredException(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, self::GET_SUPPLIERS_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoSupplierRegisteredException::MESSAGE, $flash);
    }
}
