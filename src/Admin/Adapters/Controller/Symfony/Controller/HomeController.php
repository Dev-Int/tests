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

// use Admin\UseCases\Gateway\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class HomeController extends AbstractController
{
    // public function __construct(private readonly CompanyRepository $companyRepository)
    // {
    // }

    #[Route(path: '/', name: 'admin_index')]
    public function __invoke(): Response
    {
        // $isConfigured = $this->companyRepository->hasCompany();

        // if ($isConfigured === false) {
        return $this->redirectToRoute('admin_configure');
        // }
        //
        // return $this->render('@admin/layout.html.twig');
    }
}
