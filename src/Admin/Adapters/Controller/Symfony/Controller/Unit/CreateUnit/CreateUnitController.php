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

use Admin\Adapters\Form\Type\UnitType;
use Admin\UseCases\Unit\CreateUnit\CreateUnit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CreateUnitController extends AbstractController
{
    public function __construct(private readonly CreateUnit $useCase)
    {
    }

    #[Route(path: 'units/create', name: 'admin_unit_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(UnitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateUnitApiRequest $unit */
            $unit = $form->getData();

            try {
                $this->useCase->execute($unit);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_configure');
            }
            $this->addFlash('success', 'Unit created');

            return $this->redirectToRoute('admin_configure');
        }

        return $this->render('@admin/units/create.html.twig', [
            'form' => $form,
        ]);
    }
}
