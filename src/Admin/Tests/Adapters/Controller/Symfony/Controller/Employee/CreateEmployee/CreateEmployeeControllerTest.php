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

use Admin\Tests\Factory\CompanyFactory;
use Admin\Tests\Factory\EmployeeFactory;
use Admin\UseCases\Gateway\Finder\EmployeeFinder;
use Auth\Entities\Repository\UserRepository;
use Auth\Tests\Factory\UserFactory;
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
        /** @var EmployeeFinder $employeeFinder */
        $employeeFinder = self::getContainer()->get(EmployeeFinder::class);

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
        $employees = $employeeFinder->getAllEmployees();
        self::assertCount(1, $employees);

        $employeesArray = $employees->toArray();
        $employee = $employeesArray[0];
        self::assertSame('John', $employee->firstName()->toString());
        self::assertSame('Doe', $employee->lastName()->toString());
        self::assertSame('john.doe@example.com', $employee->contactInformation()->email()->toString());
        self::assertSame('0612345678', $employee->contactInformation()->phone()->toNumber());
        self::assertSame('Developer', $employee->position()->toString());
        self::assertSame('IT', $employee->department()->toString());
        self::assertTrue($employee->isActive());

        // Vérifier que le User a été créé automatiquement
        $user = $userRepository->getByEmail($employee->contactInformation()->email());
        self::assertSame($employee->userUuid()->toString(), $user->uuid()->toString());
        self::assertTrue($user->isActive());
    }

    public function testCreateEmployeeFailsWhenEmailAlreadyExistsInEmployeeRepository(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);

        // Créer un Employee existant avec cet email
        EmployeeFactory::createOne([
            'email' => 'john.doe@example.com',
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_EMPLOYEE_URI);

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createEmployee[firstName]' => 'Jane',
            'createEmployee[lastName]' => 'Smith',
            'createEmployee[email]' => 'john.doe@example.com', // ← Email existant
            'createEmployee[phone]' => '0612345679',
            'createEmployee[position]' => 'Manager',
            'createEmployee[department]' => 'Sales',
            'createEmployee[hiredAt]' => '2024-02-15',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects('/admin/employees');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(
            $translator->trans('admin.employee.create.error.employeeExists'),
            $flash
        );
    }

    public function testCreateEmployeeFailsWhenEmailAlreadyExistsInAuthBC(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);

        // Créer un User existant (sans Employee associé)
        UserFactory::createOne([
            'email' => 'existing.user@example.com',
        ]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_EMPLOYEE_URI);

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createEmployee[firstName]' => 'John',
            'createEmployee[lastName]' => 'Doe',
            'createEmployee[email]' => 'existing.user@example.com', // ← Email existe dans Auth
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
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();

        self::assertEquals(
            $translator->trans('admin.employee.create.error.employeeExists'),
            $flash
        );
    }

    public function testCreateEmployeeFailsWithInvalidEmail(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_EMPLOYEE_URI);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createEmployee[firstName]' => 'John',
            'createEmployee[lastName]' => 'Doe',
            'createEmployee[email]' => 'invalid-email',
            'createEmployee[phone]' => '0612345678',
            'createEmployee[position]' => 'Developer',
            'createEmployee[department]' => 'IT',
            'createEmployee[hiredAt]' => '2024-01-15',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Le formulaire devrait être rejeté avec un email invalide'
        );
        self::assertSelectorExists('.form-error', 'Un message d\'erreur de validation devrait être affiché');
    }

    public function testCreateEmployeeFailsWithMissingRequiredFields(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_EMPLOYEE_URI);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createEmployee[firstName]' => '',
            'createEmployee[lastName]' => '',
            'createEmployee[email]' => 'john@example.com',
            'createEmployee[phone]' => '0612345678',
            'createEmployee[position]' => 'Developer',
            'createEmployee[department]' => 'IT',
            'createEmployee[hiredAt]' => '2024-01-15',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Le formulaire devrait être rejeté avec des champs requis vides (firstName, lastName)'
        );
        self::assertSelectorExists('.form-error', 'Un message d\'erreur de validation devrait être affiché');
    }

    public function testCreateEmployeeFailsWithTooLongFields(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        CompanyFactory::createOne(['name' => 'Test company']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, self::CREATE_EMPLOYEE_URI);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton($translator->trans('add'))->form([
            'createEmployee[firstName]' => str_repeat('A', 256),
            'createEmployee[lastName]' => 'Doe',
            'createEmployee[email]' => 'john@example.com',
            'createEmployee[phone]' => '0612345678',
            'createEmployee[position]' => str_repeat('B', 101),
            'createEmployee[department]' => 'IT',
            'createEmployee[hiredAt]' => '2024-01-15',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseStatusCodeSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Le formulaire devrait être rejeté avec des champs trop longs (firstName > 255, position > 100)'
        );
        self::assertSelectorExists('.form-error', 'Un message d\'erreur de validation devrait être affiché');
    }

    public function testCreateEmployeeReturns404ForInvalidRoute(): void
    {
        // Arrange
        CompanyFactory::createOne(['name' => 'Test company']);

        // Act
        $this->client->request(
            Request::METHOD_GET,
            '/admin/employees/create/invalid-path'
        );

        // Assert
        self::assertResponseStatusCodeSame(
            Response::HTTP_NOT_FOUND,
            'Une route inexistante devrait retourner une erreur 404'
        );
    }

    protected function getProtectedUri(): string
    {
        return self::CREATE_EMPLOYEE_URI;
    }
}
