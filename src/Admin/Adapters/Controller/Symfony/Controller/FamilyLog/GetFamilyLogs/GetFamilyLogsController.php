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

use Admin\UseCases\FamilyLog\GetFamilyLogs\GetFamilyLogs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetFamilyLogsController extends AbstractController
{
    public function __construct(private readonly GetFamilyLogs $useCase)
    {
    }

    #[Route(path: '/family_logs/', name: 'admin_family_logs_index')]
    public function __invoke(): Response
    {
        $familyLogs = $this->useCase->execute();

        $response = new GetFamilyLogsWebResponse($familyLogs);

        return $this->render('@admin/familyLogs/index.html.twig', [
            'familyLogs' => $response->familyLogs(),
        ]);
    }
}
