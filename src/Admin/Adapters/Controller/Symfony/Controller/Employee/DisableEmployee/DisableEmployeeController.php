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
use Psr\Log\LoggerInterface;
use Shared\Entities\ResourceUuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[RequireRole('ROLE_ADMIN')]
final class DisableEmployeeController extends AbstractController
{
    public function __construct(
        private readonly DisableEmployee $useCase,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(
        path: '/employees/{employee}/disable',
        name: 'admin_employees_disable',
        requirements: ['employee' => ResourceUuid::PATTERN],
        methods: ['POST']
    )]
    public function __invoke(Request $request, Employee $employee): Response
    {
        try {
            $employeeUuid = ResourceUuid::fromString($employee->uuid());
            $useCaseRequest = new DisableEmployeeApiRequest($employeeUuid);

            $response = $this->useCase->execute($useCaseRequest);

            $this->logger->info('Employee disabled successfully', [
                'employee_uuid' => $response->employee()->uuid()->toString(),
                'user_uuid' => $response->employee()->userUuid()->toString(),
                'email' => $response->employee()->contactInformation()->email()->toString(),
                'disabled_at' => $response->employee()->disabledAt()?->format('Y-m-d H:i:s'),
                'disabled_by_user_id' => $this->getUser()?->getUserIdentifier(),
                'ip_address' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]);

            $this->addFlash('success', $this->translator->trans('admin.employee.disable.success'));
        } catch (EmployeeAlreadyDisabled) {
            $this->addFlash('error', $this->translator->trans('admin.employee.error.alreadyDisabled'));
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_employees_index');
    }
}
