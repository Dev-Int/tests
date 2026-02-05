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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Employee\ListEmployees;

use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\Factory\EmployeeFactory;
use Shared\Tests\BaseFunctionalTestCase;
use Shared\Tests\RedirectsToLoginTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

/**
 * @group functionalTest
 *
 * @covers \Admin\Adapters\Controller\Symfony\Controller\Employee\ListEmployees\ListEmployeesController
 */
final class ListEmployeesControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string LIST_EMPLOYEES_URI = '/admin/employees';

    public function testListEmployeesWithSuccess(): void
    {
        // Arrange
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');

        EmployeeFactory::createOne([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'position' => 'Developer',
            'department' => 'IT',
        ]);
        EmployeeFactory::createOne([
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'position' => 'Manager',
            'department' => 'HR',
        ]);

        // Act
        $this->client->request(Request::METHOD_GET, self::LIST_EMPLOYEES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $translator->trans('admin.employee.index.titlePage'));

        // Vérifier que les 2 employés sont affichés
        self::assertSelectorTextContains('body', 'John');
        self::assertSelectorTextContains('body', 'Doe');
        self::assertSelectorTextContains('body', 'john.doe@example.com');
        self::assertSelectorTextContains('body', 'Jane');
        self::assertSelectorTextContains('body', 'Smith');
        self::assertSelectorTextContains('body', 'jane.smith@example.com');
    }

    public function testListEmployeesDisplaysEmptyStateWhenNoEmployees(): void
    {
        // Arrange
        /** @var EmployeeRepository $employeeRepository */
        $employeeRepository = self::getContainer()->get(EmployeeRepository::class);
        self::assertFalse($employeeRepository->hasEmployees());

        // Act
        $this->client->request(Request::METHOD_GET, self::LIST_EMPLOYEES_URI);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Aucun employé enregistré');
    }

    protected function getProtectedUri(): string
    {
        return self::LIST_EMPLOYEES_URI;
    }
}
