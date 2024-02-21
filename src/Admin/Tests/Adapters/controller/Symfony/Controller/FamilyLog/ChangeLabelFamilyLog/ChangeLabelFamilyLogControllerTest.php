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

final class ChangeLabelFamilyLogControllerTest extends WebTestCase
{
    private const CHANGE_LABEL_FAMILY_LOG_URI = '/admin/family_logs/%s/change-label';

    public function testUpdateFamilyLogWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();

        $familyLogGrandParent = $familyLogBuilder->create('Surgelé')->build();
        $familyLogRepository->save($familyLogGrandParent);

        $familyLogParent = $familyLogBuilder->create('Viande')
            ->withParent($familyLogGrandParent)
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(2, $familyLogs);

        // Act
        $crawler = $client->request(
            Request::METHOD_GET,
            sprintf(self::CHANGE_LABEL_FAMILY_LOG_URI, $familyLogParent->slug())
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change label "Viande"');

        $form = $crawler->selectButton('Update')->form([
            'changeLabelFamilyLog[label]' => 'Viandes',
            'changeLabelFamilyLog[slug]' => $familyLogParent->slug(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs/');

        /** @var FamilyLog $familyCreated */
        $familyCreated = $familyLogRepository->find('surgele-viande');
        $familyLogs = $familyLogRepository->findAll();
        self::assertCount(2, $familyLogs);
        self::assertSame('Viandes', $familyCreated->label());
        self::assertSame('surgele-viande', $familyCreated->slug());
        self::assertSame('Surgelé', $familyCreated->parent()?->label());
    }
}
