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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeDomiciliationSupplier;

use Admin\Adapters\Form\Type\Supplier\ChangeDomiciliationSupplierType;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\UseCases\Supplier\ChangeDomiciliationSupplier\ChangeDomiciliationSupplier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class ChangeDomiciliationSupplierController extends AbstractController
{
    public const ROUTE_NAME = 'admin_supplier_change-domiciliation';

    public function __construct(
        private readonly ChangeDomiciliationSupplier $useCase,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(
        path: 'suppliers/{supplier}/change-domiciliation',
        name: self::ROUTE_NAME,
        requirements: ['supplier' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Supplier $supplier): Response
    {
        $form = $this->createForm(
            ChangeDomiciliationSupplierType::class,
            new ChangeDomiciliationSupplierApiRequest(
                $supplier->address(),
                $supplier->postalCode(),
                $supplier->city(),
                $supplier->country(),
                $supplier->phone(),
                $supplier->email(),
                $supplier->slug()
            ),
            [
                'action' => $this->generateUrl(
                    self::ROUTE_NAME,
                    ['supplier' => $supplier->uuid()]
                ),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeDomiciliationSupplierApiRequest $supplierToUpdate */
            $supplierToUpdate = $form->getData();

            try {
                $this->useCase->execute($supplierToUpdate);
                // @codeCoverageIgnoreStart
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_suppliers_index');
                // @codeCoverageIgnoreEnd
            }

            $this->addFlash('success', $this->translator->trans('admin.supplier.changeDomiciliation.success'));

            return $this->redirectToRoute('admin_suppliers_index');
        }

        return $this->render('@admin/suppliers/change-domiciliation.html.twig', [
            'form' => $form,
            'supplier' => $supplier,
        ]);
    }
}
