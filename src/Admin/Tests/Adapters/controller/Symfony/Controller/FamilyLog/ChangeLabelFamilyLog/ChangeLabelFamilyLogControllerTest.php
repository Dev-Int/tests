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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\FamilyLog\ChangeLabelFamilyLog;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class ChangeLabelFamilyLogControllerTest extends WebTestCase
{
    private const CHANGE_LABEL_FAMILY_LOG_URI = '/admin/family_logs/%s/change-label';

    public function testChangeLabelFamilyLogWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLogParent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);

        $familyLog = $familyLogBuilder->create('Viande')
            ->withParent($familyLogParent)
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(2, $familyLogs);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Viande"');

        $form = $crawler->selectButton('Update')->form([
            'changeLabelFamilyLog[label]' => 'Viandes',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog label changed.', $flash);

        /** @var FamilyLog $familyLogUpdated */
        $familyLogUpdated = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(2, $familyLogs);
        self::assertSame('Viandes', $familyLogUpdated->label());
        self::assertSame('surgele-viandes', $familyLogUpdated->slug());
        self::assertSame('Surgelé', $familyLogUpdated->parent()?->label());
    }

    public function testChangeLabelFamilyLogWithChildrenWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLogParent = $familyLogBuilder->create('Surgelé')
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);

        $familyLog = $familyLogBuilder->create('Viande')
            ->withUuid($faker->uuid())
            ->withParent($familyLogParent)
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogChild = $familyLogBuilder
            ->create('Paté')
            ->withUuid($faker->uuid())
            ->withParent($familyLog)
            ->build()
        ;
        $familyLogRepository->save($familyLogChild);
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(3, $familyLogs);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLogParent->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Surgelé"');

        $form = $crawler->selectButton('Update')->form([
            'changeLabelFamilyLog[label]' => 'Surgelés',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog label changed.', $flash);

        /** @var FamilyLog $familyLogUpdated */
        $familyLogUpdated = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(3, $familyLogs);
        self::assertSame('Surgelés', $familyLogUpdated->label());
        self::assertSame('surgeles', $familyLogUpdated->slug());
        self::assertNull($familyLogUpdated->parent());
        self::assertCount(1, $familyLogUpdated->children());

        /** @var FamilyLog $familyLogChild */
        $familyLogChild = $familyLogUpdated->children()->first();

        self::assertSame('surgeles-viande', $familyLogChild->slug());

        self::assertCount(1, $familyLogChild->children());

        /** @var FamilyLog $grandChild */
        $grandChild = $familyLogChild->children()->first();

        self::assertSame('surgeles-viande-pate', $grandChild->slug());
    }

    public function testChangeLabelFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLogParent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);
        $familyLog2 = $familyLogBuilder->create('Produits carnés')
            ->withUuid($faker->uuid())
            ->withParent($familyLogParent)
            ->build()
        ;
        $familyLogRepository->save($familyLog2);

        $familyLog = $familyLogBuilder->create('Viande')
            ->withParent($familyLogParent)
            ->build()
        ;
        $familyLogRepository->save($familyLog);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Viande"');

        $form = $crawler->selectButton('Update')->form([
            'changeLabelFamilyLog[label]' => 'Produits carnés',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame('FamilyLog already exists.', $flash);
    }
}
