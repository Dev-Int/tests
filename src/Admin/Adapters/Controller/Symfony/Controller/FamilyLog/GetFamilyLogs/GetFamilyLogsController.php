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

namespace Admin\Adapters\Controller\Symfony\Controller\FamilyLog\GetFamilyLogs;

use Admin\Adapters\Controller\Symfony\Controller\ConfigurationController;
use Admin\Entities\Exception\FamilyLog\NoFamilyLogRegistered;
use Admin\UseCases\FamilyLog\GetFamilyLogs\GetFamilyLogs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetFamilyLogsController extends AbstractController
{
    public const ROUTE_NAME = 'admin_family_logs_index';

    public function __construct(private readonly GetFamilyLogs $useCase)
    {
    }

    #[Route(path: '/family_logs', name: self::ROUTE_NAME, methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $familyLogs = $this->useCase->execute();
        } catch (NoFamilyLogRegistered $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute(ConfigurationController::ROUTE_NAME);
        }

        $response = new GetFamilyLogsWebResponse($familyLogs);

        return $this->render('@admin/familyLogs/index.html.twig', [
            'familyLogs' => $response->familyLogs(),
        ]);
    }
}
