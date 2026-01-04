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

namespace Shared\Twig\Components;

use Shared\Adapters\Gateway\Pagination\Pagination as BasePagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Pagination extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public int $page = BasePagination::DEFAULT_PAGE;
    #[LiveProp(writable: true, url: true)]
    public int $itemsPerPage = BasePagination::DEFAULT_ITEMS_PER_PAGE;
    #[LiveProp]
    public int $totalPages;
    #[LiveProp]
    public string $route = '';

    #[LiveAction]
    public function paginate(): Response
    {
        if ($this->route === '') {
            throw new \LogicException('Pagination route must be set');
        }

        return $this->redirectToRoute(
            route: $this->route,
            parameters: ['page' => $this->page, 'itemsPerPage' => $this->itemsPerPage]
        );
    }
}
