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
use Admin\Entities\Exception\NoSupplierRegisteredException;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\SupplierDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetSuppliersControllerTest extends WebTestCase
{
    private const GET_SUPPLIERS_URI = '/admin/suppliers';

    public function testGetSuppliersWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineSupplierRepository $supplierRepository */
        $supplierRepository = self::getContainer()->get(DoctrineSupplierRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $supplierBuilder = new SupplierDataBuilder();
        $familyLog = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $supplier1 = $supplierBuilder->create('supplier1', $familyLog)->build();
        $supplier2 = $supplierBuilder->create('supplier2', $familyLog)
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $supplierRepository->save($supplier1);
        $supplierRepository->save($supplier2);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::GET_SUPPLIERS_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Suppliers');

        $list = $crawler->filter('body > div.container > div.row > article > ul.w100')->children('li.li-unstyled');
        self::assertCount(2, $list);
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
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame(NoSupplierRegisteredException::MESSAGE, $flash);
    }
}
