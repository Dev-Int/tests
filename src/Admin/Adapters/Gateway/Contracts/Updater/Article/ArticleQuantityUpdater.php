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

namespace Admin\Adapters\Gateway\Contracts\Updater\Article;

use Admin\Contracts\Services\Updater\Article\ArticleQuantityUpdater as ArticleQuantityUpdaterContract;
use Admin\Contracts\Services\Updater\Article\ArticleStockUpdate;
use Admin\Entities\Repository\ArticleRepository;
use Psr\Log\LoggerInterface;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Quantity;

final readonly class ArticleQuantityUpdater implements ArticleQuantityUpdaterContract
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<ArticleStockUpdate> $updates
     */
    public function updateQuantities(array $updates): void
    {
        $domainUpdates = array_map(
            static fn (ArticleStockUpdate $update): array => [
                'uuid' => ResourceUuid::fromString($update->articleUuid),
                'quantity' => Quantity::fromMilliemes($update->newQuantityMilliemes),
            ],
            $updates
        );

        $lowStockEvents = $this->articleRepository->resetQuantities($domainUpdates);

        foreach ($lowStockEvents as $event) {
            $this->logger->warning('Article sous stock minimum', [
                'articleUuid' => $event->articleUuid->toString(),
                'articleName' => $event->articleName->toString(),
                'currentQuantity' => $event->currentQuantity->toUnit(),
                'minStock' => $event->minStock,
            ]);
        }
    }
}
