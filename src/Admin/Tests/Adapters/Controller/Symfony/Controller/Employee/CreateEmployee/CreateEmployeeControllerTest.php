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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Employee\CreateEmployee;

use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\Factory\CompanyFactory;
use Auth\Entities\Repository\UserRepository;
use Shared\Tests\BaseFunctionalTestCase;
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\Adapters\Controller\Symfony\Controller\Employee\CreateEmployee\CreateEmployeeController
 */
final class CreateEmployeeControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string CREATE_EMPLOYEE_URI = '/admin/employees/create';

    public function testCreateEmployeeWillSucceed(): void
    {
        // Arrange
        /** @var EmployeeRepository $employeeRepository */
        $employeeRepository = self::getContainer()->get(EmployeeRepository::class);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_EMPLOYEE_URI);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.employee.create.titlePage'));

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createEmployee[firstName]' => 'John',
            'createEmployee[lastName]' => 'Doe',
            'createEmployee[email]' => 'john.doe@example.com',
            'createEmployee[phone]' => '0612345678',
            'createEmployee[position]' => 'Developer',
            'createEmployee[department]' => 'IT',
            'createEmployee[hiredAt]' => '2024-01-15',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/employees');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();

        self::assertSame($translator->trans('admin.employee.create.success'), $flash);

        // Vérifier que l'Employee a été créé
        $employees = $employeeRepository->getAllEmployees();
        self::assertCount(1, $employees);

        $employee = $employees[0];
        self::assertSame('John', $employee->firstName()->toString());
        self::assertSame('Doe', $employee->lastName()->toString());
        self::assertSame('john.doe@example.com', $employee->contactInformation()->email->toString());
        self::assertSame('0612345678', $employee->contactInformation()->phone->toNumber());
        self::assertSame('Developer', $employee->position()->toString());
        self::assertSame('IT', $employee->department()->toString());
        self::assertTrue($employee->isActive());

        // Vérifier que le User a été créé automatiquement
        $user = $userRepository->getByEmail($employee->contactInformation()->email);
        self::assertSame($employee->userUuid()->toString(), $user->uuid()->toString());
        self::assertTrue($user->isActive());
    }

    protected function getProtectedUri(): string
    {
        return self::CREATE_EMPLOYEE_URI;
    }
}
