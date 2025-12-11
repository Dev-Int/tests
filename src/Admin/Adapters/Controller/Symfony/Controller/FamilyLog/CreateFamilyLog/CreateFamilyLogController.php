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

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Adapters\Controller\Symfony\Controller\FamilyLog\GetFamilyLogs\GetFamilyLogsController;
use Admin\Adapters\Form\Type\FamilyLog\CreateFamilyLogType;
use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Entities\Exception\FamilyLog\FamilyLogAlreadyExists;
use Admin\Entities\Exception\Tax\NoTaxRegistered;
use Admin\UseCases\FamilyLog\CreateFamilyLog\CreateFamilyLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateFamilyLogController extends AbstractController
{
    public const ROUTE_NAME = 'admin_family_logs_create';

    public function __construct(
        private readonly CreateFamilyLog $useCase,
        private readonly ConfigurationService $configurationService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: 'family_logs/create', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->configurationService->isTaxConfigured()) {
            $this->addFlash('error', NoTaxRegistered::MESSAGE);

            return $this->redirectToRoute(ConfigurationController::ROUTE_NAME);
        }

        $form = $this->createForm(CreateFamilyLogType::class, new CreateFamilyLogApiRequest(), [
            'action' => $this->generateUrl(self::ROUTE_NAME),
            'attr' => ['data-turbo-frame' => '_top'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateFamilyLogApiRequest $familyLog */
            $familyLog = $form->getData();

            try {
                $this->useCase->execute($familyLog);
            } catch (FamilyLogAlreadyExists $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute(GetFamilyLogsController::ROUTE_NAME);
            }
            $this->addFlash('success', $this->translator->trans('admin.familyLog.create.success'));

            return $this->redirectToRoute(GetFamilyLogsController::ROUTE_NAME);
        }

        return $this->render('@admin/familyLogs/create.html.twig', [
            'form' => $form,
        ]);
    }
}
