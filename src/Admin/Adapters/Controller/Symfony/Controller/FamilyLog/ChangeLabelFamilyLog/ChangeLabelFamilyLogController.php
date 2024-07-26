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

namespace Admin\Adapters\Controller\Symfony\Controller\FamilyLog\ChangeLabelFamilyLog;

use Admin\Adapters\Form\Type\FamilyLog\ChangeLabelFamilyLogType;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\UseCases\FamilyLog\ChangeLabelFamilyLog\ChangeLabelFamilyLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ChangeLabelFamilyLogController extends AbstractController
{
    public function __construct(private readonly ChangeLabelFamilyLog $useCase)
    {
    }

    #[Route(
        path: 'family_logs/{familyLog}/change-label',
        name: 'admin_family_logs_change-label',
        requirements: ['familyLog' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, FamilyLog $familyLog): Response
    {
        $form = $this->createForm(
            ChangeLabelFamilyLogType::class,
            new ChangeLabelFamilyLogApiRequest($familyLog->uuid(), $familyLog->label()),
            [
                'action' => $this->generateUrl('admin_family_logs_change-label', ['familyLog' => $familyLog->uuid()]),
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChangeLabelFamilyLogApiRequest $familyLogToUpdate */
            $familyLogToUpdate = $form->getData();

            try {
                $this->useCase->execute($familyLogToUpdate);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_family_logs_index');
            }
            $this->addFlash('success', 'FamilyLog label changed.');

            return $this->redirectToRoute('admin_family_logs_index');
        }

        return $this->render('@admin/familyLogs/change-label.html.twig', [
            'form' => $form,
            'familyLog' => $familyLog,
        ]);
    }
}
