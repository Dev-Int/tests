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

namespace Admin\Adapters\Controller\Symfony\Controller\Company\UpdateCompany;

use Admin\Adapters\Form\Type\CompanyUpdateType;
use Admin\Adapters\Gateway\ORM\Entity\Company;
use Admin\Entities\Exception\CompanyNotFoundException;
use Admin\UseCases\Company\UpdateCompany\UpdateCompany;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class UpdateCompanyController extends AbstractController
{
    public function __construct(private readonly UpdateCompany $useCase)
    {
    }

    #[Route(path: '/company/{slug}/update', name: 'admin_company_update', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, Company $companyToUpdate): Response
    {
        $form = $this->createForm(CompanyUpdateType::class, $companyToUpdate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Company $company */
            $company = $form->getData();

            try {
                $this->useCase->execute(new UpdateCompanyApiRequest(
                    $companyToUpdate->name(),
                    $company->address(),
                    $company->postalCode(),
                    $company->town(),
                    $company->country(),
                    $company->phone(),
                    $company->email(),
                    $company->contact()
                ));
            } catch (CompanyNotFoundException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_index');
            }
            $this->addFlash('success', 'Company updated');

            return $this->redirectToRoute('admin_company_index');
        }

        return $this->render('@admin/company/update.html.twig', [
            'companyName' => $companyToUpdate->name(),
            'form' => $form,
        ]);
    }
}
