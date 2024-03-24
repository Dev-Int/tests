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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Unit\ChangeUnitLabel;

use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ChangeUnitLabelControllerTest extends WebTestCase
{
    private const CHANGE_LABEL_URI = '/admin/units/%s/change-label';

    public function testChangeLabelWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);
        $units = $unitRepository->findAllUnits();
        self::assertCount(1, $units);

        // Act
        $crawler = $client->request(Request::METHOD_GET, sprintf(self::CHANGE_LABEL_URI, $unit->slug()));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Kilogramme"');

        $form = $crawler->selectButton('Change label')->form([
            'changeUnitLabel[label]' => 'Kilogrammes',
            'changeUnitLabel[abbreviation]' => 'kg',
            'changeUnitLabel[slug]' => 'kilogramme',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('Unit updated', $flash);

        /** @var Unit $unitUpdated */
        $unitUpdated = $unitRepository->findOneBy(['slug' => 'kilogrammes']);
        self::assertSame('Kilogrammes', $unitUpdated->label());
        self::assertSame('kg', $unitUpdated->abbreviation());
        $units = $unitRepository->findAllUnits();
        self::assertCount(1, $units);
    }

    public function testChangeAbbreviationWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);
        $units = $unitRepository->findAllUnits();
        self::assertCount(1, $units);

        // Act
        $crawler = $client->request(Request::METHOD_GET, sprintf(self::CHANGE_LABEL_URI, $unit->slug()));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Kilogramme"');

        $form = $crawler->selectButton('Change label')->form([
            'changeUnitLabel[label]' => 'Kilogramme',
            'changeUnitLabel[abbreviation]' => 'KG',
            'changeUnitLabel[slug]' => 'kilogramme',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('Unit updated', $flash);

        /** @var Unit $unitUpdated */
        $unitUpdated = $unitRepository->findOneBy(['slug' => 'kilogramme']);
        self::assertSame('Kilogramme', $unitUpdated->label());
        self::assertSame('KG', $unitUpdated->abbreviation());
        $units = $unitRepository->findAllUnits();
        self::assertCount(1, $units);
    }

    public function testChangeLabelFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unitBuilder = new UnitDataBuilder();
        $unit1 = $unitBuilder->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit1);
        $unit2 = $unitBuilder->create('Litre', 'L')
            ->withUuid('30eca73f-dc2b-4abf-885a-ae1b7022e816')
            ->build()
        ;
        $unitRepository->save($unit2);
        $units = $unitRepository->findAllUnits();
        self::assertCount(2, $units);

        // Act
        $crawler = $client->request(Request::METHOD_GET, sprintf(self::CHANGE_LABEL_URI, $unit1->slug()));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Kilogramme"');

        $form = $crawler->selectButton('Change label')->form([
            'changeUnitLabel[label]' => 'Litre',
            'changeUnitLabel[abbreviation]' => 'kg',
            'changeUnitLabel[slug]' => 'kilogramme',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame('Unit already exists.', $flash);

        $units = $unitRepository->findAllUnits();
        self::assertCount(2, $units);
    }

    public function testChangeLabelFailWithBadRequestException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        // Act
        $crawler = $client->request(Request::METHOD_GET, sprintf(self::CHANGE_LABEL_URI, $unit->slug()));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Kilogramme"');

        $form = $crawler->selectButton('Change label')->form([
            'changeUnitLabel[label]' => '',
            'changeUnitLabel[abbreviation]' => '',
            'changeUnitLabel[slug]' => 'kilogramme',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response = $client->getCrawler();

        $labelField = $response->filter('form')->children('div')->first();
        $abbreviationField = $labelField->siblings();

        self::assertSame('Intitulé de l\'unité', $labelField->children('label')->text());
        self::assertSame('This value should not be blank.', $labelField->children('ul > li')->text());

        self::assertSame('Abréviation de l\'unité', $abbreviationField->children('label')->text());
        self::assertSame('This value should not be blank.', $abbreviationField->children('ul > li')->text());

        $units = $unitRepository->findAllUnits();
        self::assertCount(1, $units);
    }
}
