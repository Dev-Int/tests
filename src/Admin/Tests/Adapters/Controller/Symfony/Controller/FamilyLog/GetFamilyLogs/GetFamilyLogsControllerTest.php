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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\FamilyLog\GetFamilyLogs;

use Admin\Entities\Exception\FamilyLog\NoFamilyLogRegistered;
use Admin\Tests\Factory\FamilyLogFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class GetFamilyLogsControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const GET_FAMILY_LOGS_URI = '/admin/family_logs';

    public function testGetFamilyLogsWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        // Créer 3 FamilyLog : 2 parents "Surgelé" et 1 enfant "Viande"
        FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLog2 = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        FamilyLogFactory::createOne(['label' => 'Viande', 'parent' => $familyLog2->_real()]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::GET_FAMILY_LOGS_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.titlePage'));

        $list = $crawler->filter('body > div.container > main > article > ul.table > turbo-frame')
            ->children('li.li-unstyled')
        ;
        self::assertCount(3, $list);
    }

    public function testGetFamilyLogsFailWithNoFamilyLogRegisteredException(): void
    {
        // Arrange && Act
        $this->client->request(Request::METHOD_GET, self::GET_FAMILY_LOGS_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoFamilyLogRegistered::MESSAGE, $flash);
    }
}
