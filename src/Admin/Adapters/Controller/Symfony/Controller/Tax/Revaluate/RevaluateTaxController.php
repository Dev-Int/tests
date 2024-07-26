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

namespace Admin\Adapters\Controller\Symfony\Controller\Tax\Revaluate;

use Admin\Adapters\Form\Type\Tax\RevaluateTaxType;
use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Admin\UseCases\Tax\RevaluateTax\RevaluateTax;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class RevaluateTaxController extends AbstractController
{
    public function __construct(private readonly RevaluateTax $useCase)
    {
    }

    #[Route(
        path: 'taxes/{tax}/revaluate',
        name: 'admin_taxes_revaluate',
        requirements: ['tax' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Tax $tax): Response
    {
        $taxToRevaluate = new RevaluateTaxApiRequest($tax->rate(), $tax->uuid());
        $form = $this->createForm(RevaluateTaxType::class, $taxToRevaluate, [
            'action' => $this->generateUrl('admin_taxes_revaluate', ['tax' => $tax->uuid()]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RevaluateTaxApiRequest $taxRequest */
            $taxRequest = $form->getData();

            try {
                $this->useCase->execute($taxRequest);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_taxes_index');
            }
            $this->addFlash('success', 'Tax revaluated');

            return $this->redirectToRoute('admin_taxes_index');
        }

        return $this->render('@admin/taxes/revaluate.html.twig', [
            'form' => $form,
            'tax' => $tax,
        ]);
    }
}
