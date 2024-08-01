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

use Admin\Adapters\Form\Type\Unit\ChangeLabelUnitType;
use Admin\Adapters\Gateway\ORM\Entity\Unit;
use Admin\UseCases\Unit\ChangeUnitLabel\ChangeUnitLabel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ChangeUnitLabelController extends AbstractController
{
    public function __construct(private readonly ChangeUnitLabel $useCase)
    {
    }

    #[Route(
        path: 'units/{unit}/change-label',
        name: 'admin_units_change-label',
        requirements: ['unit' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, Unit $unit): Response
    {
        $unitToUpdate = new ChangeUnitLabelApiRequest($unit->label(), $unit->abbreviation(), $unit->slug());
        $form = $this->createForm(ChangeLabelUnitType::class, $unitToUpdate, [
            'action' => $this->generateUrl('admin_units_change-label', ['unit' => $unit->uuid()]),
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

                return $this->redirectToRoute('admin_units_index');
            }
            $this->addFlash('success', 'Unit updated');

            return $this->redirectToRoute('admin_units_index');
        }

        return $this->render('@admin/units/change-label.html.twig', [
            'form' => $form,
            'unit' => $unit,
        ]);
    }
}
