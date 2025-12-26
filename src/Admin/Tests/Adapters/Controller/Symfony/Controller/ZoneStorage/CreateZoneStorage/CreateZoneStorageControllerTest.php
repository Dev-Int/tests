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

use Admin\Entities\Exception\FamilyLog\NoFamilyLogRegistered;
use Admin\Entities\Exception\ZoneStorage\ZoneStorageAlreadyExists;
use Admin\Entities\Repository\ZoneStorageRepository;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Admin\Tests\Factory\ZoneStorageFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class CreateZoneStorageControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CREATE_ZONE_STORAGE_URI = '/admin/zone_storages/create';

    public function testCreateZoneStorageWillSucceed(): void
    {
        // Arrange
        /** @var ZoneStorageRepository $zoneStorageRepository */
        $zoneStorageRepository = self::getContainer()->get(ZoneStorageRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_ZONE_STORAGE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createZoneStorage[label]' => 'Réserve négative',
            'createZoneStorage[familyLog]' => $familyLog->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.zoneStorage.create.success'), $flash);

        /** @var ZoneStorage $zoneStorageCreated */
        $zoneStorageCreated = $zoneStorageRepository->getBySlug('reserve-negative');
        self::assertSame('Réserve négative', $zoneStorageCreated->label()->toString());
        self::assertEquals('Surgelé', $zoneStorageCreated->familyLog()->label()->toString());
    }

    public function testCreateZoneStorageFailWithAlreadyExistsLabelException(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        ZoneStorageFactory::createOne([
            'label' => 'Réserve négative',
            'familyLog' => $familyLog->_real(),
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_POST, self::CREATE_ZONE_STORAGE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.zoneStorage.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createZoneStorage[label]' => 'Réserve négative',
            'createZoneStorage[familyLog]' => $familyLog->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/zone_storages');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(ZoneStorageAlreadyExists::MESSAGE, $flash);
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

        self::assertSame(NoFamilyLogRegistered::MESSAGE, $flash);
    }
}
