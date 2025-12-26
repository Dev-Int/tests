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
use Admin\Entities\Exception\Supplier\NoSupplierRegistered;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\SupplierFactory;
use Faker\Factory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class GetSuppliersControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const GET_SUPPLIERS_URI = '/admin/suppliers';

    public function testGetSuppliersWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);

        for ($i = 0; $i < 30; $i++) {
            SupplierFactory::createOne([
                'name' => $faker->company(),
                'familyLog' => $familyLog->_real(),
            ]);
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

        self::assertSame(NoSupplierRegistered::MESSAGE, $flash);
    }
}
