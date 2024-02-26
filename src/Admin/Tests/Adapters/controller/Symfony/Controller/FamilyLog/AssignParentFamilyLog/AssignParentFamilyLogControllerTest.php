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

final class AssignParentFamilyLogControllerTest extends WebTestCase
{
    private const ASSIGN_PARENT_FAMILY_LOG_URI = '/admin/family_logs/%s/assign-parent';

    public function testAssignParentWithoutParentWithoutChildren(): void
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

        /** @var FamilyLog $familyLogAssigned */
        $familyLogAssigned = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Viande', $familyLogAssigned->label());
        self::assertNotNull($familyLogAssigned->parent());
        self::assertSame('surgele-viande', $familyLogAssigned->slug());
    }
}
