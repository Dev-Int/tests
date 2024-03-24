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

namespace Admin\Adapters\Controller\Symfony\Controller;

use Admin\UseCases\Gateway\CompanyRepository;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\UnitRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ApplicationConfigureController extends AbstractController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly FamilyLogRepository $familyLogRepository,
        private readonly ZoneStorageRepository $zoneStorageRepository,
        private readonly UnitRepository $unitRepository
    ) {
    }

    #[Route(path: '/configure/application', name: 'admin_configure_application')]
    public function __invoke(): Response
    {
        $hasBefore = $this->companyRepository->hasCompany()
            && $this->familyLogRepository->hasFamilyLog()
            && $this->zoneStorageRepository->hasZoneStorage();
        if ($hasBefore === false) {
            return $this->redirectToRoute('admin_configure');
        }

        $hasUnit = $this->unitRepository->hasUnit();

        return $this->render('@admin/configure/application.html.twig', [
            'hasUnit' => $hasUnit,
            'hasTaxe' => false,
        ]);
    }
}
