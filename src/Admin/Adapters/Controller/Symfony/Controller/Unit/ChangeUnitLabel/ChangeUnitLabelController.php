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

namespace Admin\Adapters\Controller\Symfony\Controller\Unit\ChangeUnitLabel;

use Admin\Adapters\Controller\Symfony\Controller\Unit\GetUnits\GetUnitsController;
use Admin\Adapters\Form\Type\Unit\ChangeLabelUnitType;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\UseCases\Unit\ChangeUnitLabel\ChangeUnitLabel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class ChangeUnitLabelController extends AbstractController
{
    public const ROUTE_NAME = 'admin_units_change-label';

    public function __construct(
        private readonly ChangeUnitLabel $useCase,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(
        path: 'units/{unit}/change-label',
        name: self::ROUTE_NAME,
        requirements: ['unit' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Unit $unit): Response
    {
        $unitToUpdate = new ChangeUnitLabelApiRequest($unit->label(), $unit->abbreviation(), $unit->slug());
        $form = $this->createForm(ChangeLabelUnitType::class, $unitToUpdate, [
            'action' => $this->generateUrl(self::ROUTE_NAME, ['unit' => $unit->uuid()]),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeUnitLabelApiRequest $unitRequest */
            $unitRequest = $form->getData();

            try {
                $this->useCase->execute($unitRequest);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute(GetUnitsController::ROUTE_NAME);
            }
            $this->addFlash('success', $this->translator->trans('admin.unit.changeLabel.success'));

            return $this->redirectToRoute(GetUnitsController::ROUTE_NAME);
        }

        return $this->render('@admin/units/change-label.html.twig', [
            'form' => $form,
            'unit' => $unit,
        ]);
    }
}
