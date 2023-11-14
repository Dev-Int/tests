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

namespace Article\UseCases\Gateway;

use Article\Entities\Article;

interface ArticleRepository
{
    public function save(Article $article): void;

    public function remove(Article $article): void;

    public function articleExists(string $name): bool;
}
