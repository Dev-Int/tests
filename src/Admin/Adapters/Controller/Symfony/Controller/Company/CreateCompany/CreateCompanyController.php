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

namespace Admin\Adapters\Controller\Symfony\Controller\Company\CreateCompany;

use Admin\Adapters\Form\Type\CompanyType;
use Admin\Entities\Exception\CompanyAlreadyExistsException;
use Admin\UseCases\Company\CreateCompany\CreateCompany;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CreateCompanyController extends AbstractController
{
    public function __construct(private readonly CreateCompany $useCase)
    {
    }

    #[Route(path: '/company/create', name: 'admin_company_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(CompanyType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<string, string> $company */
            $company = $form->getData();

            try {
                $this->useCase->execute(new CreateCompanyApiRequest(
                    $company['name'],
                    $company['address'],
                    $company['postalCode'],
                    $company['town'],
                    $company['country'],
                    $company['phone'],
                    $company['email'],
                    $company['contact']
                ));
            } catch (CompanyAlreadyExistsException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_index');
            }
            $this->addFlash('success', 'Company created');

            return $this->redirectToRoute('admin_index', [], Response::HTTP_FOUND);
        }

        return $this->render('@admin/company/create.html.twig', [
            'form' => $form,
        ]);
    }
}
