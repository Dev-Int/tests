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

namespace Admin\Tests\UseCases\Company\UpdateCompany;

use Admin\Entities\Exception\CompanyNotFoundException;
use Admin\Tests\Builder\CompanyBuilder;
use Admin\UseCases\Company\UpdateCompany\UpdateCompany;
use Admin\UseCases\Company\UpdateCompany\UpdateCompanyRequest;
use Admin\UseCases\Gateway\CompanyRepository;
use PHPUnit\Framework\TestCase;

final class UpdateCompanyTest extends TestCase
{
    public function testUpdateCompanySucceed(): void
    {
        // Arrange
        $companyRepository = $this->createMock(CompanyRepository::class);
        $useCase = new UpdateCompany($companyRepository);
        $request = $this->createMock(UpdateCompanyRequest::class);
        $companyBuilder = new CompanyBuilder();

        $company = $companyBuilder->create('Dev-Int Création')->build();
        $companyToUpdate = $companyBuilder
            ->create('Dev-Int Création')
            ->withAddress('12, rue des Singes')
            ->withPostalCode('56000')
            ->withTown('Vannes')
            ->build()
        ;

        $request->expects(self::once())->method('name')->willReturn('Dev-Int Création');
        $companyRepository->expects(self::once())->method('findByName')->with('Dev-Int Création')->willReturn($company);

        $request->expects(self::once())->method('address')->willReturn('12, rue des Singes');
        $request->expects(self::once())->method('postalCode')->willReturn('56000');
        $request->expects(self::once())->method('town')->willReturn('Vannes');
        $request->expects(self::once())->method('country')->willReturn('France');
        $request->expects(self::once())->method('phone')->willReturn('+33297000000');
        $request->expects(self::once())->method('email')->willReturn('test@test.fr');
        $request->expects(self::once())->method('contact')->willReturn('Laurent');

        $companyRepository->expects(self::once())->method('update')->with($companyToUpdate);

        // Act
        $response = $useCase->execute($request);
        $companyUpdated = $response->company;

        // Assert
        self::assertSame('Dev-Int Création', $companyUpdated->name()->toString());
        self::assertSame("12, rue des Singes\n56000 Vannes, France", $companyUpdated->address()->getFullAddress());
        self::assertSame('+33297000000', $companyUpdated->phone()->toNumber());
        self::assertSame('test@test.fr', $companyUpdated->email()->toString());
        self::assertSame('Laurent', $companyUpdated->contact());
    }

    public function testUpdateCompanyFailWithNotFoundCompanyException(): void
    {
        // Arrange
        $companyRepository = $this->createMock(CompanyRepository::class);
        $useCase = new UpdateCompany($companyRepository);
        $request = $this->createMock(UpdateCompanyRequest::class);
        $companyBuilder = new CompanyBuilder();

        $companyName = 'Dev-Int Création';
        $companyToUpdate = $companyBuilder
            ->create($companyName)
            ->withAddress('12, rue des Singes')
            ->withPostalCode('56000')
            ->withTown('Vannes')
            ->build()
        ;

        $request->expects(self::once())->method('name')->willReturn($companyName);
        $companyRepository->expects(self::once())
            ->method('findByName')
            ->with($companyName)
            ->willThrowException(new CompanyNotFoundException($companyName))
        ;

        $request->expects(self::never())->method('address')->willReturn('12, rue des Singes');
        $request->expects(self::never())->method('postalCode')->willReturn('56000');
        $request->expects(self::never())->method('town')->willReturn('Vannes');
        $request->expects(self::never())->method('country')->willReturn('France');
        $request->expects(self::never())->method('phone')->willReturn('+33297000000');
        $request->expects(self::never())->method('email')->willReturn('test@test.fr');
        $request->expects(self::never())->method('contact')->willReturn('Laurent');

        $companyRepository->expects(self::never())->method('update')->with($companyToUpdate);

        // Act && Assert
        $this->expectException(CompanyNotFoundException::class);
        $useCase->execute($request);
    }
}
