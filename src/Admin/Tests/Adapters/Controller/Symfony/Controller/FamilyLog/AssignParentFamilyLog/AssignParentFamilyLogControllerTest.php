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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\FamilyLog\AssignParentFamilyLog;

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
final class AssignParentFamilyLogControllerTest extends WebTestCase
{
    private const ASSIGN_PARENT_FAMILY_LOG_URI = '/admin/family_logs/%s/assign-parent';

    public function testAssignParentWithoutParentWithoutChildrenWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLog = $familyLogBuilder->create('Viande')->build();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($parent);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Assign parent to "Viande"');

        $form = $crawler->selectButton('Assign')->form([
            'assignParentFamilyLog[parent]' => $parent->uuid()->toString(),
            'assignParentFamilyLog[uuid]' => $familyLog->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog parent assigned.', $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Viande', $familyLogAssigned->label());
        self::assertNotNull($familyLogAssigned->parent());
        self::assertSame('surgele_viande', $familyLogAssigned->slug());
    }

    public function testAssignParentWithoutParentWithChildrenWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLog = $familyLogBuilder->create('Viande')->build();
        $child = $familyLogBuilder->create('Poulet')
            ->withUuid($faker->uuid())
            ->withParent($familyLog)
            ->build()
        ;

        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($parent);
        $familyLogRepository->save($child);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Assign parent to "Viande"');

        $form = $crawler->selectButton('Assign')->form([
            'assignParentFamilyLog[parent]' => $parent->uuid()->toString(),
            'assignParentFamilyLog[uuid]' => $familyLog->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog parent assigned.', $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Viande', $familyLogAssigned->label());
        self::assertNotNull($familyLogAssigned->parent());
        self::assertSame('surgele_viande', $familyLogAssigned->slug());
        self::assertCount(1, $familyLogAssigned->children());
        $familyLogChild = $familyLogAssigned->children()->current();
        self::assertInstanceOf(FamilyLog::class, $familyLogChild);
        self::assertSame('surgele_viande_poulet', $familyLogChild->slug());
        self::assertSame('surgele_viande_poulet', $familyLogChild->path());
    }

    public function testAssignParentFailWithAlreadyExistsException(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLog = $familyLogBuilder->create('Viande')->build();
        $familyLogRepository->save($familyLog);
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($parent);
        $otherFamilyLog = $familyLogBuilder->create('Viande')
            ->withUuid($faker->uuid())
            ->withParent($parent)
            ->build()
        ;
        $familyLogRepository->save($otherFamilyLog);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Assign parent to "Viande"');

        $form = $crawler->selectButton('Assign')->form([
            'assignParentFamilyLog[parent]' => $parent->uuid()->toString(),
            'assignParentFamilyLog[uuid]' => $familyLog->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame('FamilyLog already exists.', $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Viande', $familyLogAssigned->label());
        self::assertNull($familyLogAssigned->parent());
        self::assertSame('viande', $familyLogAssigned->slug());
    }

    public function testAssignParentFailWithFamilyLogNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLog = $familyLogBuilder->create('Viande')->build();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($parent);

        // Act
        $client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }
}
