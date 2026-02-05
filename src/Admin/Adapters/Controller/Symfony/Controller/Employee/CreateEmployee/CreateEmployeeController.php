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

namespace Admin\Adapters\Controller\Symfony\Controller\Employee\CreateEmployee;

use Admin\Adapters\Form\Type\Employee\EmployeeType;
use Admin\Entities\Exception\Employee\EmployeeAlreadyExists;
use Admin\UseCases\Employee\CreateEmployee\CreateEmployee;
use Admin\UseCases\Employee\Exception\UserEmailAlreadyExists;
use Auth\Contracts\Attribute\RequireRole;
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
final class CreateEmployeeController extends AbstractController
{
    public const string ROUTE_NAME = 'admin_employees_create';

    public function __construct(
        private readonly CreateEmployee $useCase,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: 'employees/create', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(EmployeeType::class, new CreateEmployeeInput(), [
            'action' => $this->generateUrl(self::ROUTE_NAME),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateEmployeeInput $employeeInput */
            $employeeInput = $form->getData();

            if ($employeeInput->hiredAt === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('hiredAt is required');
                // @codeCoverageIgnoreEnd
            }

            try {
                $this->useCase->execute(
                    new CreateEmployeeApiRequest(
                        NameField::fromString($employeeInput->firstName),
                        NameField::fromString($employeeInput->lastName),
                        EmailField::fromString($employeeInput->email),
                        PhoneField::fromString($employeeInput->phone),
                        NameField::fromString($employeeInput->position),
                        NameField::fromString($employeeInput->department),
                        $employeeInput->hiredAt,
                    )
                );
            } catch (EmployeeAlreadyExists) {
                $this->addFlash('error', $this->translator->trans('admin.employee.create.error.employeeExists'));

                return $this->redirectToRoute('admin_employees_index');
            } catch (UserEmailAlreadyExists) {
                $this->addFlash('error', $this->translator->trans('admin.employee.create.error.userEmailExists'));

                return $this->redirectToRoute('admin_employees_index');
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_employees_index');
            }
            $this->addFlash('success', $this->translator->trans('admin.employee.create.success'));

            return $this->redirectToRoute('admin_employees_index');
        }

        return $this->render('@admin/employees/create.html.twig', [
            'form' => $form,
        ]);
    }
}
