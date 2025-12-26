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
use Admin\Entities\Repository\FamilyLogRepository;
use Admin\Tests\Factory\FamilyLogFactory;
use Faker\Factory;
use Shared\Entities\ResourceUuid;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class AssignParentFamilyLogControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const ASSIGN_PARENT_FAMILY_LOG_URI = '/admin/family_logs/%s/assign-parent';

    public function testAssignParentWithoutParentWithoutChildrenWillSucceed(): void
    {
        // Arrange
        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Viande']);
        $parent = FamilyLogFactory::createOne(['label' => 'Surgelé']);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.assignParent.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.assignParent.button'))->form([
            'assignParentFamilyLog[parent]' => $parent->_real()->uuid(),
            'assignParentFamilyLog[uuid]' => $familyLog->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.assignParent.success'), $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->getByUuid(
            ResourceUuid::fromString($familyLog->_real()->uuid())
        );
        self::assertSame('Viande', $familyLogAssigned->label()->toString());
        self::assertNotNull($familyLogAssigned->parent());
        self::assertSame('surgele_viande', $familyLogAssigned->slug());
    }

    public function testAssignParentWithoutParentWithChildrenWillSucceed(): void
    {
        // Arrange
        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $parent = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLog = FamilyLogFactory::createOne(['label' => 'Viande']);
        FamilyLogFactory::createOne([
            'label' => 'Poulet',
            'parent' => $familyLog->_real(),
        ]);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.assignParent.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.assignParent.button'))->form([
            'assignParentFamilyLog[parent]' => $parent->_real()->uuid(),
            'assignParentFamilyLog[uuid]' => $familyLog->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.assignParent.success'), $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->getByUuidWithChildren(
            uuid: ResourceUuid::fromString($familyLog->_real()->uuid())
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
        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Viande']);
        $parent = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        FamilyLogFactory::createOne([
            'label' => 'Viande',
            'parent' => $parent->_real(),
        ]);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.assignParent.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.assignParent.button'))->form([
            'assignParentFamilyLog[parent]' => $parent->_real()->uuid(),
            'assignParentFamilyLog[uuid]' => $familyLog->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame('FamilyLog already exists.', $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->getByUuid(
            ResourceUuid::fromString($familyLog->_real()->uuid())
        );
        self::assertSame('Viande', $familyLogAssigned->label()->toString());
        self::assertNull($familyLogAssigned->parent());
        self::assertSame('viande', $familyLogAssigned->slug());
    }

    public function testAssignParentFailWithFamilyLogNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        FamilyLogFactory::createOne(['label' => 'Viande']);
        FamilyLogFactory::createOne(['label' => 'Surgelé']);

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
        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        $familyLog = FamilyLogFactory::createOne(['label' => 'Viande']);
        FamilyLogFactory::createOne(['label' => 'Surgelé']);

        $familyLogs = $familyLogRepository->getFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs->toArray());

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->_real()->uuid())
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
        $familyLogAfterCancel = $familyLogRepository->getByUuid(
            ResourceUuid::fromString($familyLog->_real()->uuid())
        );
        self::assertSame('Viande', $familyLogAfterCancel->label()->toString());
        self::assertNull($familyLogAfterCancel->parent());
        self::assertSame('viande', $familyLogAfterCancel->slug());

        $familyLogs = $familyLogRepository->getFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs->toArray());
    }
}
