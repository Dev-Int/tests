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
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class ChangeLabelFamilyLogControllerTest extends BaseFunctionalTestCase
{
    private const CHANGE_LABEL_FAMILY_LOG_URI = '/admin/family_logs/%s/change-label';

    public function testChangeLabelFamilyLogWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
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
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLog->uuid()->toString())
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
        $familyLogUpdated = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(2, $familyLogs);
        self::assertSame('Viandes', $familyLogUpdated->label());
        self::assertSame('surgele_viandes', $familyLogUpdated->slug());
        self::assertSame('Surgelé', $familyLogUpdated->parent()?->label());
    }

    public function testChangeLabelFamilyLogWithChildrenWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
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
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLogParent->uuid()->toString())
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
        $familyLogUpdated = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(3, $familyLogs);
        self::assertSame('Surgelés', $familyLogUpdated->label());
        self::assertSame('surgeles', $familyLogUpdated->slug());
        self::assertNull($familyLogUpdated->parent());
        self::assertCount(1, $familyLogUpdated->children());

        /** @var FamilyLog $familyLogChild */
        $familyLogChild = $familyLogUpdated->children()->first();

        self::assertSame('surgeles_viande', $familyLogChild->slug());

        self::assertCount(1, $familyLogChild->children());

        /** @var FamilyLog $grandChild */
        $grandChild = $familyLogChild->children()->first();

        self::assertSame('surgeles_viande_pate', $grandChild->slug());
    }

    public function testChangeLabelFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
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
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLog->uuid()->toString())
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
        $faker = Factory::create('fr_FR');

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
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
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLog->uuid()->toString())
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
        $familyLogAfterCancel = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Viande', $familyLogAfterCancel->label());
        self::assertSame('surgele_viande', $familyLogAfterCancel->slug());

        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(2, $familyLogs);
    }
}
