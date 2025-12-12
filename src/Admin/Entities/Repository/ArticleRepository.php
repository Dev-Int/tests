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

namespace Admin\Entities\Repository;

use Admin\Entities\Article\Article;
use Admin\Entities\Article\ArticleCollection;
use Admin\Entities\Exception\Article\ArticleNotFound;
use Admin\Entities\Exception\Article\NoArticleRegistered;
use Admin\Entities\Exception\FamilyLog\FamilyLogNotFound;
use Admin\Entities\Exception\Supplier\SupplierNotFound;
use Admin\Entities\Exception\Tax\TaxNotFound;
use Admin\Entities\Exception\ZoneStorage\ZoneStorageNotFound;

interface ArticleRepository
{
    public function isExists(string $name): bool;

    public function hasArticle(): bool;

    /**
     * @throws FamilyLogNotFound|SupplierNotFound|TaxNotFound|ZoneStorageNotFound
     */
    public function save(Article $article): void;

    /**
     * @throws ArticleNotFound
     */
    public function renameArticle(Article $article): void;

    /**
     * @throws ArticleNotFound|FamilyLogNotFound|SupplierNotFound|ZoneStorageNotFound
     */
    public function reAssignSupplier(Article $article): void;

    /**
     * @throws ArticleNotFound
     */
    public function changeStorageInformation(Article $article): void;

    /**
     * @throws ArticleNotFound|TaxNotFound
     */
    public function changeFinancialInformation(Article $article): void;

    /**
     * @throws NoArticleRegistered
     */
    public function getAllArticlesPaginated(int $page, int $itemPerPage): ArticleCollection;

    /**
     * @throws ArticleNotFound
     */
    public function getByUuid(string $uuid): Article;

    /**
     * @throws ArticleNotFound
     */
    public function getBySlug(string $slug): Article;
}
