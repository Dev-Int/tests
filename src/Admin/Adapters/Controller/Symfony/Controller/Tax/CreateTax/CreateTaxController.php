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

namespace Admin\Adapters\Controller\Symfony\Controller\Tax\CreateTax;

use Admin\Adapters\Form\Type\Tax\TaxType;
use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Entities\Exception\Unit\NoUnitRegisteredException;
use Admin\UseCases\Tax\CreateTax\CreateTax;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateTaxController extends AbstractController
{
    public function __construct(
        private readonly CreateTax $useCase,
        private readonly ConfigurationService $configurationService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: 'taxes/create', name: 'admin_taxes_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isUnitConfigured()) {
            $this->addFlash('error', NoUnitRegisteredException::MESSAGE);

            return $this->redirectToRoute('admin_configure');
        }

        $form = $this->createForm(TaxType::class, new CreateTaxApiRequest(), [
            'action' => $this->generateUrl('admin_taxes_create'),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateTaxApiRequest $taxToCreate */
            $taxToCreate = $form->getData();

            try {
                $this->useCase->execute($taxToCreate);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_taxes_index');
            }
            $this->addFlash('success', $this->translator->trans('admin.tax.create.success'));

            return $this->redirectToRoute('admin_taxes_index');
        }

        return $this->render('@admin/taxes/create.html.twig', [
            'form' => $form,
        ]);
    }
}
