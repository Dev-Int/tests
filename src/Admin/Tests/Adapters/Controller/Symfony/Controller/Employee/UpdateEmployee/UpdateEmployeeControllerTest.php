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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Employee\UpdateEmployee;

use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\Factory\EmployeeFactory;
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
 * @covers \Admin\Adapters\Controller\Symfony\Controller\Employee\UpdateEmployee\UpdateEmployeeController
 */
final class UpdateEmployeeControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string UPDATE_EMPLOYEE_URI = '/admin/employees/%s/edit';

    public function testUpdateEmployeeWithSuccess(): void
    {
        // Arrange - Créer un User pour l'Employee
        $user = UserFactory::createOne([
            'email' => 'john.doe@example.com',
        ]);

        $employee = EmployeeFactory::createOne([
            'email' => 'john.doe@example.com',
            'position' => 'Developer',
            'department' => 'IT',
            'userUuid' => $user->toDomain()->uuid()->toString(),
        ]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);

        // Act
        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::UPDATE_EMPLOYEE_URI, $employee->uuid())
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'update_employee[phone]' => '0687654321',
            'update_employee[position]' => 'Senior Developer',
            'update_employee[department]' => 'Engineering',
        ]);
        $this->client->submit($form);

        // Assert
        self::assertResponseRedirects('/admin/employees');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();
        self::assertSame($translator->trans('admin.employee.update.success'), $flash);

        /** @var EmployeeRepository $employeeRepository */
        $employeeRepository = self::getContainer()->get(EmployeeRepository::class);
        $updatedEmployee = $employeeRepository->getByUuid($employee->toDomain()->uuid());

        self::assertSame('0687654321', $updatedEmployee->contactInformation()->phone()->toNumber());
        self::assertSame('Senior Developer', $updatedEmployee->position()->toString());
        self::assertSame('Engineering', $updatedEmployee->department()->toString());
    }

    public function testUpdateEmployeeFailsWhenNotFound(): void
    {
        // Act
        $this->client->request(
            Request::METHOD_GET,
            \sprintf(self::UPDATE_EMPLOYEE_URI, '550e8400-e29b-41d4-a716-446655440000')
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();
        $title = $response->filter('h1')->text();
        self::assertEquals('Page non trouvée', $title);
    }

    protected function getProtectedUri(): string
    {
        // Créer un User pour l'Employee
        $user = UserFactory::createOne();

        $employee = EmployeeFactory::createOne([
            'userUuid' => $user->toDomain()->uuid()->toString(),
        ]);

        return \sprintf(self::UPDATE_EMPLOYEE_URI, $employee->toDomain()->uuid()->toString());
    }
}
