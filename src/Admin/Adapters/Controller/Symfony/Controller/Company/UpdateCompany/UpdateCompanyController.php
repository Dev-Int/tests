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

use Admin\Adapters\Form\Type\Company\CompanyUpdateType;
use Admin\Adapters\Gateway\ORM\Entity\Company;
use Admin\UseCases\Company\UpdateCompany\UpdateCompany;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class UpdateCompanyController extends AbstractController
{
    public function __construct(
        private readonly UpdateCompany $useCase,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(
        path: '/company/{company}/update',
        name: 'admin_company_update',
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Company $company): Response
    {
        $companyToUpdate = new UpdateCompanyApiRequest(
            $company->name(),
            $company->address(),
            $company->postalCode(),
            $company->city(),
            $company->country(),
            $company->phone(),
            $company->email(),
            $company->contact()
        );
        $form = $this->createForm(CompanyUpdateType::class, $companyToUpdate, [
            'action' => $this->generateUrl('admin_company_update', ['company' => $company->slug()]),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UpdateCompanyApiRequest $companyRequest */
            $companyRequest = $form->getData();

            $this->useCase->execute($companyRequest);

            $this->addFlash('success', $this->translator->trans('admin.company.update.success'));

            return $this->redirectToRoute('admin_company_index');
        }

        return $this->render('@admin/company/update.html.twig', [
            'companyName' => $companyToUpdate->name(),
            'form' => $form,
        ]);
    }
}
