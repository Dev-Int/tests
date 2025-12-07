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

use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\GetFamilyLogs\GetFamilyLogsController;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\UseCases\Gateway\FamilyLogRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Shared\Entities\ResourceUuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class AssignParentFamilyLogControllerTest extends BaseFunctionalTestCase
{
    private const ASSIGN_PARENT_FAMILY_LOG_URI = '/admin/family_logs/%s/assign-parent';

    public function testAssignParentWithoutParentWithoutChildrenWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLog = $familyLogBuilder->create('Viande')->build();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($parent);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.assignParent.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.assignParent.button'))->form([
            'assignParentFamilyLog[parent]' => $parent->uuid()->toString(),
            'assignParentFamilyLog[uuid]' => $familyLog->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.assignParent.success'), $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->findByUuid(
            ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID)
        );
        self::assertSame('Viande', $familyLogAssigned->label()->toString());
        self::assertNotNull($familyLogAssigned->parent());
        self::assertSame('surgele_viande', $familyLogAssigned->slug());
    }

    public function testAssignParentWithoutParentWithChildrenWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
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
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.assignParent.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.assignParent.button'))->form([
            'assignParentFamilyLog[parent]' => $parent->uuid()->toString(),
            'assignParentFamilyLog[uuid]' => $familyLog->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.assignParent.success'), $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->findByUuidWithChildren(
            uuid: ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID)
        );
        self::assertSame('Viande', $familyLogAssigned->label()->toString());
        self::assertNotNull($familyLogAssigned->parent());
        self::assertSame('surgele_viande', $familyLogAssigned->slug());
        $children = $familyLogAssigned->children();
        self::assertNotNull($children);
        self::assertCount(1, $children);
        $familyLogChild = $children[0];
        self::assertSame('surgele_viande_poulet', $familyLogChild->slug());
        self::assertSame('surgele_viande_poulet', $familyLogChild->path());
    }

    public function testAssignParentFailWithAlreadyExistsException(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
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
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.assignParent.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.assignParent.button'))->form([
            'assignParentFamilyLog[parent]' => $parent->uuid()->toString(),
            'assignParentFamilyLog[uuid]' => $familyLog->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame('FamilyLog already exists.', $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->findByUuid(
            ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID)
        );
        self::assertSame('Viande', $familyLogAssigned->label()->toString());
        self::assertNull($familyLogAssigned->parent());
        self::assertSame('viande', $familyLogAssigned->slug());
    }

    public function testAssignParentFailWithFamilyLogNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLog = $familyLogBuilder->create('Viande')->build();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($parent);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }

    public function testCancelDuringFamilyLogParentAssignment(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLog = $familyLogBuilder->create('Viande')->build();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($parent);
        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs->toArray());

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.assignParent.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $cancelLink = $crawler->selectLink($translator->trans('cancel'));
        self::assertCount(1, $cancelLink, 'Cancel link should exist');

        $this->client->click($cancelLink->link());

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame(GetFamilyLogsController::ROUTE_NAME);

        /** @var FamilyLog $familyLogAfterCancel */
        $familyLogAfterCancel = $familyLogRepository->findByUuid(
            ResourceUuid::fromString(FamilyLogDataBuilder::VALID_UUID)
        );
        self::assertSame('Viande', $familyLogAfterCancel->label()->toString());
        self::assertNull($familyLogAfterCancel->parent());
        self::assertSame('viande', $familyLogAfterCancel->slug());

        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs->toArray());
    }
}
