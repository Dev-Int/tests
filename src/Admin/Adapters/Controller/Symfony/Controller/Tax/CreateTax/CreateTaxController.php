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

use Admin\Adapters\Form\Type\TaxType;
use Admin\UseCases\Tax\CreateTax\CreateTax;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CreateTaxController extends AbstractController
{
    public function __construct(private readonly CreateTax $useCase)
    {
    }

    #[Route(path: 'taxes/create', name: 'admin_taxes_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(TaxType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateTaxApiRequest $taxToCreate */
            $taxToCreate = $form->getData();

            try {
                $this->useCase->execute($taxToCreate);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
            $this->addFlash('success', 'Tax created');

            return $this->redirectToRoute('admin_configure');
        }

        return $this->render('@admin/tax/create.html.twig', [
            'form' => $form,
        ]);
    }
}
