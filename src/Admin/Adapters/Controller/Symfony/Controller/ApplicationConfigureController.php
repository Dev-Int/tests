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

use Admin\Contracts\Services\Provider\ConfigurationServiceProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ApplicationConfigureController extends AbstractController
{
    public const string ROUTE_NAME = 'admin_configure_application';

    public function __construct(private readonly ConfigurationServiceProvider $configurationService)
    {
    }

    #[Route(path: '/configure/application', name: 'admin_configure_application')]
    public function __invoke(): Response
    {
        $isCompanySetup = $this->configurationService->isCompanyConfigured();
        if ($isCompanySetup === false) {
            return $this->redirectToRoute('admin_configure');
        }

        $hasUnit = $this->configurationService->isUnitConfigured();
        $hasTax = $this->configurationService->isTaxConfigured();

        if ($this->configurationService->isApplicationReady()) {
            $hasUnit = false;
            $hasTax = false;
        }

        return $this->render('@admin/configure/application.html.twig', [
            'hasUnit' => $hasUnit,
            'hasTaxe' => $hasTax,
        ]);
    }
}
