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
use Admin\Entities\VO\EmployeeStatus;
use Admin\Tests\Factory\EmployeeFactory;
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
        // Arrange
        $employee = EmployeeFactory::createOne([
            'email' => 'john.doe@example.com',
            'position' => 'Developer',
            'department' => 'IT',
            'status' => EmployeeStatus::ACTIVE,
            'disabledAt' => null,
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
        // Arrange
        $employee = EmployeeFactory::createOne([
            'email' => 'jane.doe@example.com',
            'position' => 'Developer',
            'department' => 'IT',
            'status' => EmployeeStatus::INACTIVE,
            'disabledAt' => new \DateTimeImmutable(),
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
        $employee = EmployeeFactory::createOne();

        return \sprintf(self::DISABLE_EMPLOYEE_URI, $employee->toDomain()->uuid()->toString());
    }

    protected function getProtectedHttpMethod(): string
    {
        return Request::METHOD_POST;
    }
}
