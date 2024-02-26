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

namespace Admin\Tests\Adapters\controller\Symfony\Controller\FamilyLog\CreateFamilyLog;

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\Entities\FamilyLog as FamilyLogDomain;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateFamilyLogControllerTest extends WebTestCase
{
    private const CREATE_FAMILY_LOG_URI = '/admin/family_logs/create';

    public function testCreateFamilyLogWithoutParentWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Logistic Family');

        $form = $crawler->selectButton('Create')->form([
            'createFamilyLog[label]' => 'Surgelé',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog created', $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->findBySlug('surgele');
        self::assertSame('Surgelé', $familyCreated->label()->toString());
    }

    public function testCreateFamilyLogWithParentWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogParent = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);
        $familyLogParentOrm = $familyLogRepository->findByUuid($familyLogParent->uuid());

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Logistic Family');

        $form = $crawler->selectButton('Create')->form([
            'createFamilyLog[label]' => 'Viande',
            'createFamilyLog[parent]' => $familyLogParentOrm->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog created', $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->findBySlug('surgele-viande');
        self::assertSame('Viande', $familyCreated->label()->toString());
        self::assertSame('surgele-viande', $familyCreated->slug());
        self::assertSame('Surgelé', $familyCreated->parent()?->label()->toString());
    }

    public function testCreateFamilyLogWithGrandParentsWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $familyLogGrandParent = $familyLogBuilder->create('Surgelé')
            ->withUuid('99282a8d-f344-456c-bbd3-37fe89f3876c')
            ->build()
        ;
        $familyLogRepository->save($familyLogGrandParent);
        $familyLogParent = $familyLogBuilder->create('Viande')
            ->withParent($familyLogGrandParent)
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);
        $familyLogParentOrm = $familyLogRepository->findBySlug('surgele-viande');

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Logistic Family');

        $form = $crawler->selectButton('Create')->form([
            'createFamilyLog[label]' => 'Poulet',
            'createFamilyLog[parent]' => $familyLogParentOrm->uuid()->toString(),
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog created', $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->findBySlug('surgele-viande-poulet');
        self::assertSame('Poulet', $familyCreated->label()->toString());
        self::assertSame('surgele-viande-poulet', $familyCreated->slug());
        self::assertSame('Viande', $familyCreated->parent()?->label()->toString());
    }

    public function testCreateFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogBuilder = new FamilyLogDataBuilder();
        $familyLog = $familyLogBuilder->create('Surgelé')->build();
        $familyLogRepository->save($familyLog);

        /** @var FamilyLog $familyCreated */
        $familyCreated = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Surgelé', $familyCreated->label());
        self::assertNull($familyCreated->parent());

        // Act
        $crawler = $client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Logistic Family');

        $form = $crawler->selectButton('Create')->form([
            'createFamilyLog[label]' => 'Surgelé',
        ]);
        $client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs/');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container')->children('div.flash.flash-error')->text();

        self::assertSame(FamilyLogAlreadyExistsException::MESSAGE, $flash);
    }
}
