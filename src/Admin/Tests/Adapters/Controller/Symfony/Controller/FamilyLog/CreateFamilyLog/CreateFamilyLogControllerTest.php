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
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionalTest
 */
final class CreateFamilyLogControllerTest extends WebTestCase
{
    private const CREATE_FAMILY_LOG_URI = '/admin/family_logs/create';

    public function testCreateFamilyLogWithoutParentWillSucceed(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

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
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog created', $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->findBySlug('surgele');
        self::assertSame('Surgelé', $familyCreated->label()->toString());
    }

    public function testCreateFamilyLogWithParentWillSucceed(): void
    {
        // Arrange
        $faker = Factory::create('fr_FR');
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
        $familyLogParent = (new FamilyLogDataBuilder())->create('Surgelé')
            ->withUuid($faker->uuid())
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
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog created', $flash);

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
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

        /** @var DoctrineFamilyLogRepository $familyLogRepository */
        $familyLogRepository = self::getContainer()->get(DoctrineFamilyLogRepository::class);
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
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame('FamilyLog created', $flash);

        /** @var FamilyLogDomain $familyCreated */
        $familyCreated = $familyLogRepository->findBySlug('surgele_viande_poulet');
        self::assertSame('Poulet', $familyCreated->label()->toString());
        self::assertSame('surgele_viande_poulet', $familyCreated->slug());
        self::assertSame('Viande', $familyCreated->parent()?->label()->toString());
    }

    public function testCreateFamilyLogFailWithAlreadyExistsException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        /** @var DoctrineTaxRepository $taxRepository */
        $taxRepository = self::getContainer()->get(DoctrineTaxRepository::class);
        $tax = (new TaxDataBuilder())->create('TVA taux normal', 20.0)->build();
        $taxRepository->save($tax);

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
        self::assertResponseRedirects('/admin/family_logs');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(FamilyLogAlreadyExistsException::MESSAGE, $flash);
    }

    public function testCreateUnitFailWithNoCompanyRegisteredException(): void
    {
        // Arrange
        $client = self::createClient();

        /** @var DoctrineCompanyRepository $companyRepository */
        $companyRepository = self::getContainer()->get(DoctrineCompanyRepository::class);
        $company = (new CompanyDataBuilder())->create('Test company')->build();
        $companyRepository->save($company);

        /** @var DoctrineUnitRepository $unitRepository */
        $unitRepository = self::getContainer()->get(DoctrineUnitRepository::class);
        $unit = (new UnitDataBuilder())->create('Kilogramme', 'kg')->build();
        $unitRepository->save($unit);

        // Act
        $client->request(Request::METHOD_POST, self::CREATE_FAMILY_LOG_URI);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/configure');

        $admin = $client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertSame(NoTaxRegisteredException::MESSAGE, $flash);
    }
}
