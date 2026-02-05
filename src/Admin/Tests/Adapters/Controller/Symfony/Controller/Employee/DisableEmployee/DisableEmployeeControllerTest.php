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

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Employee\DisableEmployee;

use Admin\Entities\Repository\EmployeeRepository;
use Admin\Tests\Factory\EmployeeFactory;
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
 * @covers \Admin\Adapters\Controller\Symfony\Controller\Employee\DisableEmployee\DisableEmployeeController
 */
final class DisableEmployeeControllerTest extends BaseFunctionalTestCase
{
    use Factories;
    use RedirectsToLoginTestTrait;

    private const string DISABLE_EMPLOYEE_URI = '/admin/employees/%s/disable';

    public function testDisableEmployeeWithSuccess(): void
    {
        // Arrange - Créer un User d'abord (requis pour désactiver l'Employee)
        $user = UserFactory::createOne([
            'email' => 'john.doe@example.com',
            'disabledAt' => null,
        ]);

        $employee = EmployeeFactory::createOne([
            'email' => 'john.doe@example.com',
            'position' => 'Developer',
            'department' => 'IT',
            'disabledAt' => null,
            'userUuid' => $user->toDomain()->uuid()->toString(),
        ]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);

        // Act
        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::DISABLE_EMPLOYEE_URI, $employee->toDomain()->uuid()->toString())
        );

        // Assert
        self::assertResponseRedirects('/admin/employees');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-success')->text();
        self::assertSame($translator->trans('admin.employee.disable.success'), $flash);

        // Vérifier que l'Employee a été désactivé
        /** @var EmployeeRepository $employeeRepository */
        $employeeRepository = self::getContainer()->get(EmployeeRepository::class);
        $disabledEmployee = $employeeRepository->getByUuid($employee->toDomain()->uuid());

        self::assertNotNull($disabledEmployee->disabledAt());
        self::assertFalse($disabledEmployee->isActive());

        // Vérifier que le User associé a aussi été désactivé
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $disabledUser = $userRepository->getByUuid($user->toDomain()->uuid());

        self::assertFalse($disabledUser->isActive());
    }

    public function testDisableEmployeeFailsWhenNotFound(): void
    {
        // Act
        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::DISABLE_EMPLOYEE_URI, '550e8400-e29b-41d4-a716-446655440000')
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = $this->client->getCrawler();
        $title = $response->filter('h1')->text();
        self::assertEquals('Page non trouvée', $title);
    }

    public function testDisableEmployeeRedirectsWhenAlreadyDisabled(): void
    {
        // Arrange - Créer un User déjà désactivé
        $user = UserFactory::createOne([
            'email' => 'jane.doe@example.com',
            'disabledAt' => new \DateTimeImmutable(),
        ]);

        $employee = EmployeeFactory::createOne([
            'email' => 'jane.doe@example.com',
            'position' => 'Developer',
            'department' => 'IT',
            'disabledAt' => new \DateTimeImmutable(),
            'userUuid' => $user->toDomain()->uuid()->toString(),
        ]);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);

        // Act
        $this->client->request(
            Request::METHOD_POST,
            \sprintf(self::DISABLE_EMPLOYEE_URI, $employee->toDomain()->uuid()->toString())
        );

        // Assert
        self::assertResponseRedirects('/admin/employees');

        $admin = $this->client->followRedirect();
        $flash = $admin->filter('body > div.container > div')->children('div.flash.flash-error')->text();
        self::assertSame($translator->trans('admin.employee.error.alreadyDisabled'), $flash);
    }

    protected function getProtectedUri(): string
    {
        // Créer un User pour l'Employee
        $user = UserFactory::createOne();

        $employee = EmployeeFactory::createOne([
            'userUuid' => $user->toDomain()->uuid()->toString(),
        ]);

        return \sprintf(self::DISABLE_EMPLOYEE_URI, $employee->toDomain()->uuid()->toString());
    }

    protected function getProtectedHttpMethod(): string
    {
        return Request::METHOD_POST;
    }
}
