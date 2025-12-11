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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\FamilyLog\ChangeLabelFamilyLog;

use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\GetFamilyLogs\GetFamilyLogsController;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\UseCases\Gateway\FamilyLogRepository;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Shared\Entities\ResourceUuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class ChangeLabelFamilyLogControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CHANGE_LABEL_FAMILY_LOG_URI = '/admin/family_logs/%s/change-label';

    public function testChangeLabelFamilyLogWillSucceed(): void
    {
        // Arrange
        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la hiérarchie: Surgelé (parent) -> Viande (enfant)
        $familyLogParent = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLog = FamilyLogFactory::createOne([
            'label' => 'Viande',
            'parent' => $familyLogParent->_real(),
        ]);

        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLog->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.changeLabel.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.changeLabel.button'))->form([
            'changeLabelFamilyLog[label]' => 'Viandes',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.changeLabel.success'), $flash);

        /** @var FamilyLog $familyLogUpdated */
        $familyLogUpdated = $familyLogRepository->findByUuid(
            ResourceUuid::fromString($familyLog->_real()->uuid())
        );
        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs->toArray());
        self::assertSame('Viandes', $familyLogUpdated->label()->toString());
        self::assertSame('surgele_viandes', $familyLogUpdated->slug());
        self::assertSame('Surgelé', $familyLogUpdated->parent()?->label()->toString());
    }

    public function testChangeLabelFamilyLogWithChildrenWillSucceed(): void
    {
        // Arrange
        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la hiérarchie: Surgelé (grandparent) -> Viande (parent) -> Paté (child)
        $familyLogParent = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLog = FamilyLogFactory::createOne([
            'label' => 'Viande',
            'parent' => $familyLogParent->_real(),
        ]);
        FamilyLogFactory::createOne([
            'label' => 'Paté',
            'parent' => $familyLog->_real(),
        ]);

        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(3, $familyLogs);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLogParent->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.changeLabel.titlePage', ['%familyLabel%' => 'Surgelé'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.changeLabel.button'))->form([
            'changeLabelFamilyLog[label]' => 'Surgelés',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.changeLabel.success'), $flash);

        /** @var FamilyLog $familyLogUpdated */
        $familyLogUpdated = $familyLogRepository->findByUuidWithChildren(
            uuid: ResourceUuid::fromString($familyLogParent->_real()->uuid())
        );
        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(3, $familyLogs);
        self::assertSame('Surgelés', $familyLogUpdated->label()->toString());
        self::assertSame('surgeles', $familyLogUpdated->slug());
        self::assertNull($familyLogUpdated->parent());
        $children = $familyLogUpdated->children();
        self::assertNotNull($children);
        self::assertCount(1, $children);

        /** @var FamilyLog $familyLogChild */
        $familyLogChild = $children[0];

        self::assertSame('surgeles_viande', $familyLogChild->slug());

        $grandChildren = $familyLogChild->children();
        self::assertNotNull($grandChildren);
        self::assertCount(1, $grandChildren);

        /** @var FamilyLog $grandChild */
        $grandChild = $grandChildren[0];

        self::assertSame('surgeles_viande_pate', $grandChild->slug());
    }

    public function testChangeLabelFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer un parent avec 2 enfants
        $familyLogParent = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        FamilyLogFactory::createOne([
            'label' => 'Produits carnés',
            'parent' => $familyLogParent->_real(),
        ]);
        $familyLog = FamilyLogFactory::createOne([
            'label' => 'Viande',
            'parent' => $familyLogParent->_real(),
        ]);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLog->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.changeLabel.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $form = $crawler->selectButton($translator->trans('admin.familyLog.changeLabel.button'))->form([
            'changeLabelFamilyLog[label]' => 'Produits carnés',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame('FamilyLog already exists.', $flash);
    }

    public function testChangeLabelFamilyLogFailWithFamilyLogNotFound(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        // Créer la hiérarchie mais on va utiliser un UUID qui n'existe pas
        $familyLogParent = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        FamilyLogFactory::createOne([
            'label' => 'Viande',
            'parent' => $familyLogParent->_real(),
        ]);

        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs->toArray());

        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $faker->uuid())
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();

        $title = $response->filter('h1')->text();

        self::assertEquals('Page non trouvée', $title);
    }

    public function testCancelDuringFamilyLogLabelChange(): void
    {
        // Arrange
        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer la hiérarchie: Surgelé (parent) -> Viande (enfant)
        $familyLogParent = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLog = FamilyLogFactory::createOne([
            'label' => 'Viande',
            'parent' => $familyLogParent->_real(),
        ]);

        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs->toArray());

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLog->_real()->uuid())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'h1',
            $translator->trans('admin.familyLog.changeLabel.titlePage', ['%familyLabel%' => 'Viande'])
        );

        $cancelLink = $crawler->selectLink($translator->trans('cancel'));
        self::assertCount(1, $cancelLink, 'Cancel link should exist');

        $this->client->click($cancelLink->link());

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame(GetFamilyLogsController::ROUTE_NAME);

        /** @var FamilyLog $familyLogAfterCancel */
        $familyLogAfterCancel = $familyLogRepository->findByUuid(
            ResourceUuid::fromString($familyLog->_real()->uuid())
        );
        self::assertSame('Viande', $familyLogAfterCancel->label()->toString());
        self::assertSame('surgele_viande', $familyLogAfterCancel->slug());

        $familyLogs = $familyLogRepository->findFamilyLogsOrderingBySlug();
        self::assertCount(2, $familyLogs->toArray());
    }
}
