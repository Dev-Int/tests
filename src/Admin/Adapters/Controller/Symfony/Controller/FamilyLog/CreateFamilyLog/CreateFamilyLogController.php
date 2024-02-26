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

namespace Admin\Adapters\Controller\Symfony\Controller\FamilyLog\CreateFamilyLog;

use Admin\Adapters\Form\Type\FamilyLog\CreateFamilyLogType;
use Admin\Adapters\Gateway\ORM\Entity\FamilyLog;
use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\UseCases\FamilyLog\CreateFamilyLog\CreateFamilyLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CreateFamilyLogController extends AbstractController
{
    public function __construct(
        private readonly CreateFamilyLog $useCase,
        private readonly DoctrineFamilyLogRepository $familyLogRepository
    ) {
    }

    #[Route(path: 'family_logs/create', name: 'admin_family_logs_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(CreateFamilyLogType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $parent = null;

            /** @var array{label: string, parent: FamilyLog|null} $familyLog */
            $familyLog = $form->getData();
            if ($familyLog['parent'] !== null) {
                $parent = $this->familyLogRepository->findBySlug($familyLog['parent']->slug());
            }

            try {
                $this->useCase->execute(
                    new CreateFamilyLogApiRequest(
                        $familyLog['label'],
                        $parent
                    )
                );
            } catch (FamilyLogAlreadyExistsException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_family_logs_index');
            }
            $this->addFlash('success', 'FamilyLog created');

            return $this->redirectToRoute('admin_family_logs_index', [], Response::HTTP_CREATED);
        }

        return $this->render('@admin/familyLogs/create.html.twig', [
            'form' => $form,
        ]);
    }
}
