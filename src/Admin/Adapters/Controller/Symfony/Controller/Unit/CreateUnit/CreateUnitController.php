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

namespace Admin\Adapters\Controller\Symfony\Controller\Unit\CreateUnit;

use Admin\Adapters\Form\Type\Unit\UnitType;
use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Entities\Exception\Company\NoCompanyRegisteredException;
use Admin\UseCases\Unit\CreateUnit\CreateUnit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateUnitController extends AbstractController
{
    public const ROUTE_NAME = 'admin_unit_create';

    public function __construct(
        private readonly CreateUnit $useCase,
        private readonly ConfigurationService $configurationService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: 'units/create', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isCompanyConfigured()) {
            $this->addFlash('error', NoCompanyRegisteredException::MESSAGE);

            return $this->redirectToRoute('admin_configure');
        }

        $form = $this->createForm(UnitType::class, new CreateUnitApiRequest(), [
            'action' => $this->generateUrl('admin_unit_create'),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateUnitApiRequest $unit */
            $unit = $form->getData();

            try {
                $this->useCase->execute($unit);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_units_index');
            }
            $this->addFlash('success', $this->translator->trans('admin.unit.create.success'));

            return $this->redirectToRoute('admin_units_index');
        }

        return $this->render('@admin/units/create.html.twig', [
            'form' => $form,
        ]);
    }
}
