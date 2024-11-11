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

namespace Admin\Adapters\Controller\Symfony\Controller\Supplier\CreateSupplier;

use Admin\Adapters\Form\Type\Supplier\SupplierType;
use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Entities\Exception\Supplier\SupplierAlreadyExists;
use Admin\Entities\Exception\ZoneStorage\NoZoneStorageRegisteredException;
use Admin\UseCases\Supplier\CreateSupplier\CreateSupplier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateSupplierController extends AbstractController
{
    public function __construct(
        private readonly CreateSupplier $useCase,
        private readonly ConfigurationService $configurationService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: 'suppliers/create', name: 'admin_suppliers_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isZoneStorageConfigured()) {
            $this->addFlash('error', NoZoneStorageRegisteredException::MESSAGE);

            return $this->redirectToRoute('admin_configure');
        }

        $form = $this->createForm(SupplierType::class, new CreateSupplierDto(), [
            'action' => $this->generateUrl('admin_suppliers_create'),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateSupplierDto $supplier */
            $supplier = $form->getData();

            if ($supplier->familyLog === null) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException('FamilyLog expected!');
                // @codeCoverageIgnoreEnd
            }

            try {
                $this->useCase->execute(
                    new CreateSupplierApiRequest(
                        $supplier->name,
                        $supplier->address,
                        $supplier->postalCode,
                        $supplier->city,
                        $supplier->country,
                        $supplier->phone,
                        $supplier->email,
                        $supplier->contact,
                        $supplier->cellphone,
                        $supplier->familyLog->toDomain(),
                        $supplier->delayDelivery,
                        $supplier->orderDays
                    )
                );
            } catch (SupplierAlreadyExists $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_suppliers_index');
            }
            $this->addFlash('success', $this->translator->trans('admin.supplier.create.success'));

            return $this->redirectToRoute('admin_suppliers_index', [], Response::HTTP_FOUND);
        }

        return $this->render('@admin/suppliers/create.html.twig', [
            'form' => $form,
        ]);
    }
}
