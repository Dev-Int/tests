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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages;

use Admin\Entities\Exception\ZoneStorage\NoZoneStorageRegistered;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class GetZoneStoragesControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const GET_ZONE_STORAGES_URI = '/admin/zone_storages';

    public function testGetZoneStoragesWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog->_real(),
        ]);
        ZoneStorageFactory::createOne([
            'label' => 'Réserve positive',
            'familyLog' => $familyLog->_real(),
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_ZONE_STORAGES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.titlePage'));

        $list = $crawler->filter('body > div.container > main > article > ul.table > turbo-frame')
            ->children('li.li-unstyled')
        ;
        self::assertCount(2, $list);
    }

    public function testGetZoneStoragesFailWithNoZoneStorageRegisteredException(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, self::GET_ZONE_STORAGES_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoZoneStorageRegistered::MESSAGE, $flash);
    }
}
