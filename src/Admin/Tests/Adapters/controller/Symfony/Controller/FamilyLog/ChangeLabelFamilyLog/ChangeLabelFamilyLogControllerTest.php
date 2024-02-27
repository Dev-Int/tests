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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
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
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLogParent = $familyLogBuilder->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
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
        self::assertResponseRedirects('/admin/family_logs/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog label changed.', $flash);

        /** @var FamilyLog $familyLogUpdated */
        $familyLogUpdated = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(2, $familyLogs);
        self::assertSame('Viandes', $familyLogUpdated->label());
        self::assertSame('surgele-viande', $familyLogUpdated->slug());
        self::assertSame('Surgelé', $familyLogUpdated->parent()?->label());
    }

    public function testChangeLabelFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLogParent = $familyLogBuilder->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);
        $familyLog2 = $familyLogBuilder->create('Produits carnés')
            ->withUuid('6cb08670-4b03-43a3-875d-8f6bfb4d5eb0')
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
        self::assertResponseRedirects('/admin/family_logs/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame('FamilyLog already exists.', $flash);
    }
}
