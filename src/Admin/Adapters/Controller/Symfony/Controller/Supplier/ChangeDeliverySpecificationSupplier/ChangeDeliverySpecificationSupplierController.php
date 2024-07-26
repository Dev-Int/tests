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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeDeliverySpecificationSupplier;

use Admin\Adapters\Form\Type\Supplier\ChangeDeliverySpecificationSupplierType;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\UseCases\Supplier\ChangeDeliverySpecification\ChangeDeliverySpecificationSupplier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ChangeDeliverySpecificationSupplierController extends AbstractController
{
    public function __construct(private readonly ChangeDeliverySpecificationSupplier $useCase)
    {
    }

    #[Route(
        path: 'suppliers/{supplier}/change-delivery-specification',
        name: 'admin_suppliers_change-delivery-specification',
        requirements: ['supplier' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Supplier $supplier): Response
    {
        $form = $this->createForm(
            ChangeDeliverySpecificationSupplierType::class,
            new ChangeDeliverySpecificationSupplierDto(
                $supplier->familyLog(),
                $supplier->delayDelivery(),
                $supplier->orderDays(),
                $supplier->slug()
            ),
            [
                'action' => $this->generateUrl(
                    'admin_suppliers_change-delivery-specification',
                    ['supplier' => $supplier->uuid()]
                ),
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeDeliverySpecificationSupplierDto $supplierToUpdate */
            $supplierToUpdate = $form->getData();

            try {
                $this->useCase->execute(
                    new ChangeDeliverySpecificationSupplierApiRequest(
                        $supplierToUpdate->familyLog->toDomain(),
                        $supplierToUpdate->delayDelivery,
                        $supplierToUpdate->orderDays,
                        $supplierToUpdate->slug
                    )
                );
                // @codeCoverageIgnoreStart
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_suppliers_index');
                // @codeCoverageIgnoreEnd
            }

            $this->addFlash('success', 'Supplier updated');

            return $this->redirectToRoute('admin_suppliers_index');
        }

        return $this->render('@admin/suppliers/change-delivery-specification.html.twig', [
            'form' => $form,
            'supplier' => $supplier,
        ]);
    }
}
