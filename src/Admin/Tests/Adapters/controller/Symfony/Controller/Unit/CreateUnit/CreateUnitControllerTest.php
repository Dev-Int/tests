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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\Unit\CreateUnit;

use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class CreateUnitControllerTest extends WebTestCase
{
    private const CREATE_UNIT_URI = '/admin/units/create';

    public function testCreateUnitWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        // Act
        $crawler = $client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Unit');

        $form = $crawler->selectButton('Create')->form([
            'createUnit[label]' => 'Kilogramme',
            'createUnit[abbreviation]' => 'kg',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('Unit created', $flash);

        /** @var Unit $unitCreated */
        $unitCreated = $unitRepository->findOneBy(['slug' => 'kilogramme']);
        self::assertSame('Kilogramme', $unitCreated->label());
        self::assertSame('kilogramme', $unitCreated->slug());
        self::assertSame('kg', $unitCreated->abbreviation());
    }

    public function testCreateUnitFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        // Act
        $crawler = $client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Unit');

        $form = $crawler->selectButton('Create')->form([
            'createUnit[label]' => 'Kilogramme',
            'createUnit[abbreviation]' => 'kg',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/units');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame('Unit already exists.', $flash);

        /** @var Unit $unitCreated */
        $unitCreated = $unitRepository->findOneBy(['slug' => 'kilogramme']);
        self::assertSame('Kilogramme', $unitCreated->label());
        self::assertSame('kg', $unitCreated->abbreviation());
    }

    public function testCreateUnitFailWithBadRequestException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        // Act
        $crawler = $client->request(Request::METHOD_POST, self::CREATE_UNIT_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Unit');

        $form = $crawler->selectButton('Create')->form([
            'createUnit[label]' => '',
            'createUnit[abbreviation]' => '',
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

        /** @var Unit $unitCreated */
        $unitCreated = $unitRepository->findOneBy(['slug' => 'kilogramme']);
        self::assertSame('Kilogramme', $unitCreated->label());
        self::assertSame('kg', $unitCreated->abbreviation());
    }
}
