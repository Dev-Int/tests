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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\FamilyLog\CreateFamilyLog;

use Admin\Entities\Exception\FamilyLog\FamilyLogAlreadyExists;
use Admin\Entities\Exception\Tax\NoTaxRegistered;
use Admin\Entities\FamilyLog\FamilyLog as FamilyLogDomain;
use Admin\Entities\Repository\FamilyLogRepository;
use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\FamilyLogFactory;
use Admin\Tests\Factory\TaxFactory;
use Admin\Tests\Factory\UnitFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 */
final class CreateFamilyLogControllerTest extends BaseFunctionalTestCase
{
    use Factories;

    private const CREATE_FAMILY_LOG_URI = '/admin/family_logs/create';

    public function testCreateFamilyLogWithoutParentWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createFamilyLog[label]' => 'Surgelé',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.create.success'), $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->getBySlug('surgele');
        self::assertSame('Surgelé', $familyCreated->label()->toString());
    }

    public function testCreateFamilyLogWithParentWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);

        // Créer le parent avec Foundry
        $familyLogParent = FamilyLogFactory::createOne(['label' => 'Surgelé']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createFamilyLog[label]' => 'Viande',
            'createFamilyLog[parent]' => $familyLogParent->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.create.success'), $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->getBySlug('surgele_viande');
        self::assertSame('Viande', $familyCreated->label()->toString());
        self::assertSame('surgele_viande', $familyCreated->slug());
        self::assertSame('Surgelé', $familyCreated->parent()?->label()->toString());
    }

    public function testCreateFamilyLogWithGrandParentsWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var FamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(FamilyLogRepository::class);

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);

        // Créer la hiérarchie: Surgelé (grandparent) -> Viande (parent)
        $familyLogGrandParent = FamilyLogFactory::createOne(['label' => 'Surgelé']);
        $familyLogParent = FamilyLogFactory::createOne([
            'label' => 'Viande',
            'parent' => $familyLogGrandParent->_real(),
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createFamilyLog[label]' => 'Poulet',
            'createFamilyLog[parent]' => $familyLogParent->_real()->uuid(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.create.success'), $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->getBySlug('surgele_viande_poulet');
        self::assertSame('Poulet', $familyCreated->label()->toString());
        self::assertSame('surgele_viande_poulet', $familyCreated->slug());
        self::assertSame('Viande', $familyCreated->parent()?->label()->toString());
    }

    public function testCreateFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);
        TaxFactory::createOne(['name' => 'TVA taux normal', 'rate' => 20.0]);

        FamilyLogFactory::createOne(['label' => 'Surgelé']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createFamilyLog[label]' => 'Surgelé',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(FamilyLogAlreadyExists::MESSAGE, $flash);
    }

    public function testCreateUnitFailWithNoCompanyRegisteredException(): void
    {
        // Arrange
        CompanyFactory::createOne(['name' => 'Test company']);
        UnitFactory::createOne(['label' => 'Kilogramme', 'abbreviation' => 'kg']);

        // Act
        $this->client->request(Request::METHOD_POST, self::CREATE_FAMILY_LOG_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoTaxRegistered::MESSAGE, $flash);
    }
}
