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

namespace Admin\Adapters\Controller\Symfony\Controller\FamilyLog\AssignParentFamilyLog;

use Admin\Adapters\Form\Type\FamilyLog\AssignParentFamilyLogType;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\UseCases\FamilyLog\ChangeParentFamilyLog\AssignParentFamilyLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class AssignParentFamilyLogController extends AbstractController
{
    public function __construct(private readonly AssignParentFamilyLog $useCase)
    {
    }

    #[Route(
        path: '/family_logs/{uuid}/assign-parent',
        name: 'admin_family_logs_assign-parent',
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, FamilyLog $familyLog): Response
    {
        $form = $this->createForm(
            AssignParentFamilyLogType::class,
            ['parent' => $familyLog->parent(), 'label' => $familyLog->label()]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{label: string, parent: FamilyLog} $familyLogToUpdate */
            $familyLogToUpdate = $form->getData();

            try {
                $this->useCase->execute(
                    new AssignParentFamilyLogApiRequest(
                        $familyLog->uuid(),
                        $familyLogToUpdate['parent']->toDomain()
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_family_logs_index');
            }
            $this->addFlash('success', 'FamilyLog parent assigned.');

            return $this->redirectToRoute('admin_family_logs_index');
        }

        return $this->render('@admin/familyLogs/assign-parent.html.twig', [
            'form' => $form,
        ]);
    }
}
