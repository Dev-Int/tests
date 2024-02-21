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
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
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

    #[Route(path: 'family_logs/{slug}/change-label', name: 'admin_family_logs_change-label', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, FamilyLog $familyLog): Response
    {
        $form = $this->createForm(
            ChangeLabelFamilyLogType::class,
            ['label' => $familyLog->label(), 'slug' => $familyLog->slug()]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{label: string, slug: string} $familyLogToUpdate */
            $familyLogToUpdate = $form->getData();

            try {
                $this->useCase->execute(
                    new ChangeLabelFamilyLogApiRequest(
                        $familyLogToUpdate['slug'],
                        $familyLogToUpdate['label'],
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_family_logs_index');
            }
            $this->addFlash('success', 'FamilyLog label changed.');

            return $this->redirectToRoute('admin_family_logs_index');
        }

        return $this->render('@admin/familyLogs/change-label.html.twig', [
            'form' => $form,
        ]);
    }
}
