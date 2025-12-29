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

use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Admin\Contracts\Services\Updater\Article\ArticleQuantityUpdater as ArticleQuantityUpdaterContract;
use Admin\Contracts\Services\Updater\Article\ArticleStockUpdate;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ArticleQuantityUpdater implements ArticleQuantityUpdaterContract
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<ArticleStockUpdate> $updates
     */
    public function updateQuantities(array $updates): void
    {
        foreach ($updates as $update) {
            $article = $this->entityManager->find(Article::class, $update->articleUuid);

            if ($article instanceof Article) {
                // Convert milliemes to float
                $quantity = $update->newQuantityMilliemes / 1000;
                $article->setQuantity($quantity);
            }
        }

        $this->entityManager->flush();
    }
}
