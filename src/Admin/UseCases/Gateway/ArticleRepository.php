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

namespace Admin\UseCases\Gateway;

use Admin\Entities\Article\Article;
use Admin\Entities\Article\ArticleCollection;

interface ArticleRepository
{
    public function isExists(string $name): bool;

    public function hasArticle(): bool;

    public function save(Article $article): void;

    public function renameArticle(Article $article): void;

    public function reAssignSupplier(Article $article): void;

    public function changeStorageInformation(Article $article): void;

    public function changeFinancialInformation(Article $article): void;

    public function findAllArticlesPaginated(int $page, int $itemPerPage): ArticleCollection;

    public function findByUuid(string $uuid): Article;
}
