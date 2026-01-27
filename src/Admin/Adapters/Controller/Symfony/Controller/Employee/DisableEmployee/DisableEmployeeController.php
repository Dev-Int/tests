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

namespace Admin\Adapters\Controller\Symfony\Controller\Employee\DisableEmployee;

use Admin\Adapters\Gateway\ORM\Entity\Employee;
use Admin\Entities\Exception\Employee\EmployeeAlreadyDisabled;
use Admin\UseCases\Employee\DisableEmployee\DisableEmployee;
use Auth\Contracts\Attribute\RequireRole;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[RequireRole('ROLE_ADMIN')]
final class DisableEmployeeController extends AbstractController
{
    public function __construct(
        private readonly DisableEmployee $useCase,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/employees/{employee}/disable', name: 'admin_employees_disable', requirements: ['employee' => ResourceUuid::PATTERN], methods: ['POST'])]
    public function __invoke(Employee $employee): Response
    {
        try {
            $employeeUuid = ResourceUuid::fromString($employee->uuid());
            $request = new DisableEmployeeApiRequest($employeeUuid);

            $this->useCase->execute($request);

            $this->addFlash('success', $this->translator->trans('admin.employee.disable.success'));
        } catch (EmployeeAlreadyDisabled) {
            $this->addFlash('error', $this->translator->trans('admin.employee.error.alreadyDisabled'));
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_employees_index');
    }
}
