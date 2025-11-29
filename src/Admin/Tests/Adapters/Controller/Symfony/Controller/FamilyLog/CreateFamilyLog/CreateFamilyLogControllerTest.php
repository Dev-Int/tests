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

use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineTaxRepository;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineUnitRepository;
use Admin\Entities\Exception\FamilyLog\FamilyLogAlreadyExistsException;
use Admin\Entities\Exception\Tax\NoTaxRegisteredException;
use Admin\Entities\FamilyLog\FamilyLog as FamilyLogDomain;
use Admin\Tests\DataBuilder\CompanyDataBuilder;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use Admin\Tests\DataBuilder\TaxDataBuilder;
use Admin\Tests\DataBuilder\UnitDataBuilder;
use App\Shared\Tests\BaseFunctionalTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group functionalTest
 */
final class CreateFamilyLogControllerTest extends BaseFunctionalTestCase
{
    private const CREATE_FAMILY_LOG_URI = '/admin/family_logs/create';

    public function testCreateFamilyLogWithoutParentWillSucceed(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $form = $crawler->selectButton('Create')->form([
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
        $familyCreated = $familyLogRepository->findBySlug('surgele');
        self::assertSame('Surgelé', $familyCreated->label()->toString());
    }

    public function testCreateFamilyLogWithParentWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        $familyLogParent = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);
        $familyLogParentOrm = $familyLogRepository->findByUuid($familyLogParent->uuid());

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $form = $crawler->selectButton('Create')->form([
            'createFamilyLog[label]' => 'Viande',
            'createFamilyLog[parent]' => $familyLogParentOrm->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.create.success'), $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->findBySlug('surgele_viande');
        self::assertSame('Viande', $familyCreated->label()->toString());
        self::assertSame('surgele_viande', $familyCreated->slug());
        self::assertSame('Surgelé', $familyCreated->parent()?->label()->toString());
    }

    public function testCreateFamilyLogWithGrandParentsWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        $familyLogBuilder = new FamilyLogDataBuilder();
        $familyLogGrandParent = $familyLogBuilder->create('Surgelé')
            ->withUuid($faker->uuid())
            ->build()
        ;
        $familyLogRepository->save($familyLogGrandParent);
        $familyLogParent = $familyLogBuilder->create('Viande')
            ->withParent($familyLogGrandParent)
            ->build()
        ;
        $familyLogRepository->save($familyLogParent);
        $familyLogParentOrm = $familyLogRepository->findBySlug('surgele_viande');

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $form = $crawler->selectButton('Create')->form([
            'createFamilyLog[label]' => 'Poulet',
            'createFamilyLog[parent]' => $familyLogParentOrm->uuid()->toString(),
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.familyLog.create.success'), $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->findBySlug('surgele_viande_poulet');
        self::assertSame('Poulet', $familyCreated->label()->toString());
        self::assertSame('surgele_viande_poulet', $familyCreated->slug());
        self::assertSame('Viande', $familyCreated->parent()?->label()->toString());
    }

    public function testCreateFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        $familyLogBuilder = new FamilyLogDataBuilder();
        $familyLog = $familyLogBuilder->create('Surgelé')->build();
        $familyLogRepository->save($familyLog);

        /** @var FamilyLog $familyCreated */
        $familyCreated = $familyLogRepository->find(FamilyLogDataBuilder::VALID_UUID);
        self::assertSame('Surgelé', $familyCreated->label());
        self::assertNull($familyCreated->parent());

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_FAMILY_LOG_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.familyLog.create.titlePage'));

        $form = $crawler->selectButton('Create')->form([
            'createFamilyLog[label]' => 'Surgelé',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(FamilyLogAlreadyExistsException::MESSAGE, $flash);
    }

    public function testCreateUnitFailWithNoCompanyRegisteredException(): void
    {
        // Arrange
        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);

        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        // Act
        $this->client->request(Request::METHOD_POST, self::CREATE_FAMILY_LOG_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoTaxRegisteredException::MESSAGE, $flash);
    }
}
