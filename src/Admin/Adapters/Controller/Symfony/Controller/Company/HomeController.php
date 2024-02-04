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

namespace Admin\Adapters\Controller\Symfony\Controller\Company;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineCompanyRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class HomeController extends AbstractController
{
    public function __construct(private readonly DoctrineCompanyRepository $repository)
    {
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route(path: 'company/', name: 'admin_company_index')]
    public function __invoke(): Response
    {
        $company = $this->repository->findCompany();

        return $this->render('@admin/company/index.html.twig', [
            'company' => $company,
        ]);
    }
}
