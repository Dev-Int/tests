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
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ConfigurationController extends AbstractController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly FamilyLogRepository $familyLogRepository,
        private readonly ZoneStorageRepository $zoneStorageRepository
    ) {
    }

    #[Route(path: '/configure', name: 'admin_configure')]
    public function __invoke(): Response
    {
        $hasCompany = $this->companyRepository->hasCompany();
        $hasFamilyLog = $this->familyLogRepository->hasFamilyLog();
        $hasZoneStorage = $this->zoneStorageRepository->hasZoneStorage();

        return $this->render('@admin/configuration.html.twig', [
            'hasCompany' => $hasCompany,
            'hasApplication' => false,
            'hasStorage' => $hasZoneStorage,
            'hasFamilyLog' => $hasFamilyLog,
            'hasSupplier' => false,
            'hasArticle' => false,
        ]);
    }
}
