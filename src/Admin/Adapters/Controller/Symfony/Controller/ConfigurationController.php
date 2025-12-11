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

use Admin\Adapters\Gateway\ConfigurationService;
use Admin\Entities\Repository\CompanyRepository;
use Admin\UseCases\Gateway\ArticleRepository;
use Admin\UseCases\Gateway\FamilyLogRepository;
use Admin\UseCases\Gateway\SupplierRepository;
use Admin\UseCases\Gateway\ZoneStorageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ConfigurationController extends AbstractController
{
    public const ROUTE_NAME = 'admin_configure';

    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly ConfigurationService $configurationService,
        private readonly FamilyLogRepository $familyLogRepository,
        private readonly ZoneStorageRepository $zoneStorageRepository,
        private readonly SupplierRepository $supplierRepository,
        private readonly ArticleRepository $articleRepository
    ) {
    }

    #[Route(path: '/configure', name: self::ROUTE_NAME)]
    public function __invoke(): Response
    {
        $hasCompany = $this->companyRepository->hasCompany();
        $hasApplication = $this->configurationService->isApplicationConfigured();
        $hasFamilyLog = $this->familyLogRepository->hasFamilyLog();
        $hasZoneStorage = $this->zoneStorageRepository->hasZoneStorage();
        $hasSupplier = $this->supplierRepository->hasSupplier();
        $hasArticle = $this->articleRepository->hasArticle();

        return $this->render('@admin/configuration.html.twig', [
            'hasCompany' => $hasCompany,
            'hasApplication' => $hasApplication,
            'hasStorage' => $hasZoneStorage,
            'hasFamilyLog' => $hasFamilyLog,
            'hasSupplier' => $hasSupplier,
            'hasArticle' => $hasArticle,
        ]);
    }
}
