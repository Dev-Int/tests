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

namespace Article\UseCases\CreateArticle;

interface CreateArticleRequest
{
    public function name(): string;

    public function supplierId(): string;

    /**
     * @return array<array{string, float}|null>
     */
    public function packaging(): array;

    public function price(): float;

    public function taxes(): float;

    public function minStock(): float;

    /**
     * @return array<string>
     */
    public function zoneStorageIds(): array;

    public function familyLogName(): string;

    public function quantity(): float;
}
