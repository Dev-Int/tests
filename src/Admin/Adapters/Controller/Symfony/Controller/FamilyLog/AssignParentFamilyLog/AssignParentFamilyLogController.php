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
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog\FamilyLog;
use Admin\UseCases\FamilyLog\ChangeParentFamilyLog\AssignParentFamilyLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class AssignParentFamilyLogController extends AbstractController
{
    public function __construct(
        private readonly AssignParentFamilyLog $useCase,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(
        path: '/family_logs/{familyLog}/assign-parent',
        name: 'admin_family_logs_assign-parent',
        requirements: ['familyLog' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request, FamilyLog $familyLog): Response
    {
        $form = $this->createForm(
            AssignParentFamilyLogType::class,
            ['parent' => $familyLog->parent(), 'uuid' => $familyLog->uuid()],
            [
                'action' => $this->generateUrl('admin_family_logs_assign-parent', ['familyLog' => $familyLog->uuid()]),
                'attr' => ['data-turbo-frame' => '_top'],
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{uuid: string, parent: FamilyLog} $familyLogToUpdate */
            $familyLogToUpdate = $form->getData();

            try {
                $this->useCase->execute(
                    new AssignParentFamilyLogApiRequest(
                        $familyLogToUpdate['uuid'],
                        $familyLogToUpdate['parent']->toDomain()
                    )
                );
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_family_logs_index');
            }
            $this->addFlash('success', $this->translator->trans('admin.familyLog.assignParent.success'));

            return $this->redirectToRoute('admin_family_logs_index');
        }

        return $this->render('@admin/familyLogs/assign-parent.html.twig', [
            'form' => $form,
            'familyLog' => $familyLog,
        ]);
    }
}
