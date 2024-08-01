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
use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Entities\Exception\FamilyLogAlreadyExistsException;
use Admin\Entities\Exception\NoTaxRegisteredException;
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
        private readonly ConfigurationService $configurationService
    ) {
    }

    #[Route(path: 'family_logs/create', name: 'admin_family_logs_create', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isTaxConfigured()) {
            $this->addFlash('error', NoTaxRegisteredException::MESSAGE);

            return $this->redirectToRoute('admin_configure');
        }

        $form = $this->createForm(CreateFamilyLogType::class, new CreateFamilyLogApiRequest(), [
            'action' => $this->generateUrl('admin_family_logs_create'),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateFamilyLogApiRequest $familyLog */
            $familyLog = $form->getData();

            try {
                $this->useCase->execute($familyLog);
            } catch (FamilyLogAlreadyExistsException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('admin_family_logs_index');
            }
            $this->addFlash('success', 'FamilyLog created');

            return $this->redirectToRoute('admin_family_logs_index', [], Response::HTTP_FOUND);
        }

        return $this->render('@admin/familyLogs/create.html.twig', [
            'form' => $form,
        ]);
    }
}
