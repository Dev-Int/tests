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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\ChangeContactSupplier;

use Admin\Adapters\Form\Type\Supplier\ChangeContactType;
use Admin\Adapters\Gateway\ORM\Entity\Supplier;
use Admin\UseCases\Supplier\ChangeContactSupplier\ChangeContactSupplier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class ChangeContactSupplierController extends AbstractController
{
    public function __construct(private readonly ChangeContactSupplier $useCase, private readonly TranslatorInterface $translator)
    {
    }

    #[Route(
        path: 'suppliers/{supplier}/change-contact',
        name: 'admin_suppliers_change-contact',
        requirements: ['supplier' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Supplier $supplier): Response
    {
        $form = $this->createForm(
            ChangeContactType::class,
            new ChangeContactSupplierApiRequest(
                $supplier->contact(),
                $supplier->cellphone(),
                $supplier->slug()
            ),
            [
                'action' => $this->generateUrl('admin_suppliers_change-contact', ['supplier' => $supplier->uuid()]),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeContactSupplierApiRequest $supplierToUpdate */
            $supplierToUpdate = $form->getData();

            try {
                $this->useCase->execute($supplierToUpdate);
                // @codeCoverageIgnoreStart
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_suppliers_index');
                // @codeCoverageIgnoreEnd
            }

            $this->addFlash('success', $this->translator->trans('admin.supplier.changeContact.success'));

            return $this->redirectToRoute('admin_suppliers_index');
        }

        return $this->render('@admin/suppliers/change-contact.html.twig', [
            'form' => $form,
            'supplier' => $supplier,
        ]);
    }
}
