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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Supplier\GetSuppliers;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineSupplierRepository;
use Admin\Adapters\Gateway\Pagination\Pagination;
use Admin\Entities\Exception\NoSupplierRegisteredException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class GetSuppliersControllerTest extends WebTestCase
{
    private const GET_SUPPLIERS_URI = '/admin/suppliers';

    public function testGetSuppliersWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();
        $faker = Factory::create('fr_FR');

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
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
        $crawler = $client->request(Request::METHOD_GET, self::GET_SUPPLIERS_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Suppliers');

        $list = $crawler->filter('body > div.container > main > article > turbo-frame > ul.table > turbo-frame')
            ->children('li.li-unstyled')
        ;
        self::assertCount(Pagination::DEFAULT_ITEMS_PER_PAGE, $list);
    }

    public function testGetSuppliersFailWithNoSupplierRegisteredException(): void
    {
        // Arrange
        $client = self::createClient();

        // Act
        $client->request(Request::METHOD_GET, self::GET_SUPPLIERS_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoSupplierRegisteredException::MESSAGE, $flash);
    }
}
