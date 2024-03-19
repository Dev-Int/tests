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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\FamilyLog\AssignParentFamilyLog;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
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
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLog = $familyLogBuilder->create('Viande')->build();
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLog);
        $familyLogRepository->save($parent);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Assign parent to "Viande"');

        $form = $crawler->selectButton('Assign')->form([
            'assignParentFamilyLog[parent]' => $parent->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog parent assigned.', $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Viande', $familyLogAssigned->label());
        self::assertNotNull($familyLogAssigned->parent());
        self::assertSame('surgele-viande', $familyLogAssigned->slug());
    }

    public function testAssignParentFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLog = $familyLogBuilder->create('Viande')->build();
        $familyLogRepository->save($familyLog);
        $parent = $familyLogBuilder->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($parent);
        $otherFamilyLog = $familyLogBuilder->create('Viande')
            ->withUuid('a07b8ad8-128c-4c0d-875f-a4bee7654169')
            ->withParent($parent)
            ->build()
        ;
        $familyLogRepository->save($otherFamilyLog);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::ASSIGN_PARENT_FAMILY_LOG_URI, $familyLog->uuid()->toString())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Assign parent to "Viande"');

        $form = $crawler->selectButton('Assign')->form([
            'assignParentFamilyLog[parent]' => $parent->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame('FamilyLog already exists.', $flash);

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Viande', $familyLogAssigned->label());
        self::assertNull($familyLogAssigned->parent());
        self::assertSame('viande', $familyLogAssigned->slug());
    }
}
