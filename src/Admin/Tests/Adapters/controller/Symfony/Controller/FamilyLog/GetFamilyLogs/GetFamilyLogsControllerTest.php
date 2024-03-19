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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\FamilyLog\GetFamilyLogs;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functionalTest
 */
final class GetFamilyLogsControllerTest extends WebTestCase
{
    private const GET_FAMILY_LOGS_URI = '/admin/family_logs/';

    public function testGetFamilyLogsWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $familyLog1 = $familyLogBuilder->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLog1);
        $familyLog2 = $familyLogBuilder->create('Surgelé')
            ->withUuid('fdfedfaa-9b1e-48e2-a689-1944a03a5927')
            ->build()
        ;
        $familyLogRepository->save($familyLog2);
        $familyLog3 = $familyLogBuilder->create('Viande')
            ->withParent($familyLog2)
            ->build()
        ;
        $familyLogRepository->save($familyLog3);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::GET_FAMILY_LOGS_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Logistics Families');

        $list = $crawler->filter('body > div.container > div.row > article > ul.w100')->children('li.li-unstyled');

        self::assertCount(3, $list);
    }
}
