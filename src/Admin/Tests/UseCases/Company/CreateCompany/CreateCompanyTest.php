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

namespace Admin\Tests\UseCases\Company\CreateCompany;

use Admin\Entities\Exception\CompanyAlreadyExistsException;
use Admin\UseCases\Company\CreateCompany\CreateCompany;
use Admin\UseCases\Company\CreateCompany\CreateCompanyRequest;
use Admin\UseCases\Gateway\CompanyRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class CreateCompanyTest extends TestCase
{
    public function testCreateCompanySucceed(): void
    {
        // Arrange
        $companyRepository = $this->createMock(CompanyRepository::class);
        $useCase = new CreateCompany($companyRepository);
        $request = $this->createMock(CreateCompanyRequest::class);

        $request->expects(self::once())->method('name')->willReturn('Dev-Int Création');
        $request->expects(self::once())->method('address')->willReturn('5, rue des Plantes');
        $request->expects(self::once())->method('postalCode')->willReturn('75000');
        $request->expects(self::once())->method('town')->willReturn('Paris');
        $request->expects(self::once())->method('country')->willReturn('France');
        $request->expects(self::once())->method('phone')->willReturn('+33297000000');
        $request->expects(self::once())->method('email')->willReturn('test@test.fr');
        $request->expects(self::once())->method('contact')->willReturn('Laurent');

        $companyRepository->expects(self::once())->method('hasCompany')->willReturn(false);
        $companyRepository->expects(self::once())->method('save');

        // Act
        $response = $useCase->execute($request);
        $company = $response->company;

        // Assert
        self::assertSame('Dev-Int Création', $company->name()->toString());
        self::assertSame("5, rue des Plantes\n75000 Paris, France", $company->address()->getFullAddress());
        self::assertSame('+33297000000', $company->phone()->toNumber());
        self::assertSame('test@test.fr', $company->email()->toString());
        self::assertSame('Laurent', $company->contact());
    }

    public function testCreateCompanyThrowAlreadyExistsException(): void
    {
        $companyRepository = $this->createMock(CompanyRepository::class);
        $useCase = new CreateCompany($companyRepository);
        $request = $this->createMock(CreateCompanyRequest::class);

        $request->expects(self::once())->method('name')->willReturn('Dev-Int Création');
        $request->expects(self::never())->method('address')->willReturn('5, rue des Plantes');
        $request->expects(self::never())->method('postalCode')->willReturn('75000');
        $request->expects(self::never())->method('town')->willReturn('Paris');
        $request->expects(self::never())->method('country')->willReturn('France');
        $request->expects(self::never())->method('phone')->willReturn('+33297000000');
        $request->expects(self::never())->method('email')->willReturn('test@test.fr');
        $request->expects(self::never())->method('contact')->willReturn('Laurent');

        $companyRepository->expects(self::once())->method('hasCompany')->willReturn(true);
        $companyRepository->expects(self::never())->method('save');

        $this->expectException(CompanyAlreadyExistsException::class);

        // Act
        $useCase->execute($request);
    }
}
