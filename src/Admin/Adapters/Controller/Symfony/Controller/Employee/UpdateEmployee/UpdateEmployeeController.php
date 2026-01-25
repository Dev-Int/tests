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

namespace Admin\Adapters\Controller\Symfony\Controller\Employee\UpdateEmployee;

use Admin\Adapters\Form\Type\Employee\UpdateEmployeeType;
use Admin\Adapters\Gateway\ORM\Entity\Employee;
use Admin\Entities\VO\EmployeeStatus;
use Admin\UseCases\Employee\UpdateEmployee\UpdateEmployee;
use Auth\Contracts\Attribute\RequireRole;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\EmailField;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\PhoneField;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[RequireRole('ROLE_ADMIN')]
final class UpdateEmployeeController extends AbstractController
{
    public const string ROUTE_NAME = 'admin_employees_update';

    public function __construct(
        private readonly UpdateEmployee $useCase,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        path: 'employees/{employee}/edit',
        name: self::ROUTE_NAME,
        requirements: ['employee' => ResourceUuid::PATTERN],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Employee $employee): Response
    {
        $form = $this->createForm(
            type: UpdateEmployeeType::class,
            data: new UpdateEmployeeInput(
                firstName: $employee->firstName(),
                lastName: $employee->lastName(),
                hiredAt: $employee->hiredAt(),
                email: $employee->email(),
                phone: $employee->phone(),
                position: $employee->position(),
                department: $employee->department(),
                status: EmployeeStatus::from($employee->status()),
            ),
            options: [
                'action' => $this->generateUrl(self::ROUTE_NAME, ['employee' => $employee->uuid()]),
                'method' => 'POST',
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UpdateEmployeeInput $validatedInput */
            $validatedInput = $form->getData();

            try {
                $this->useCase->execute(
                    new UpdateEmployeeApiRequest(
                        ResourceUuid::fromString($employee->uuid()),
                        EmailField::fromString($validatedInput->email),
                        PhoneField::fromString($validatedInput->phone),
                        NameField::fromString($validatedInput->position),
                        NameField::fromString($validatedInput->department),
                        $validatedInput->status,
                    )
                );

                $this->addFlash('success', $this->translator->trans('admin.employee.update.success'));

                return $this->redirectToRoute('admin_employees_index');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('@admin/employees/update.html.twig', [
            'form' => $form,
            'employee' => $employee,
        ]);
    }
}
