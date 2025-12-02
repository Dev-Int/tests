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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\RenameSupplier;

use Admin\Adapters\Form\Type\Supplier\RenameSupplierType;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\UseCases\Supplier\RenameSupplier\RenameSupplier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RenameSupplierController extends AbstractController
{
    public const ROUTE_NAME = 'admin_suppliers_rename';

    public function __construct(
        private readonly RenameSupplier $useCase,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(
        path: 'suppliers/{supplier}/rename',
        name: self::ROUTE_NAME,
        requirements: ['supplier' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Supplier $supplier): Response
    {
        $form = $this->createForm(
            RenameSupplierType::class,
            new RenameSupplierApiRequest($supplier->name(), $supplier->slug()),
            [
                'action' => $this->generateUrl(self::ROUTE_NAME, ['supplier' => $supplier->uuid()]),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RenameSupplierApiRequest $supplierToUpdate */
            $supplierToUpdate = $form->getData();

            try {
                $this->useCase->execute($supplierToUpdate);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_suppliers_index');
            }
            $this->addFlash('success', $this->translator->trans('admin.supplier.rename.success'));

            return $this->redirectToRoute('admin_suppliers_index');
        }

        return $this->render('@admin/suppliers/rename.html.twig', [
            'form' => $form,
            'supplier' => $supplier,
        ]);
    }
}
