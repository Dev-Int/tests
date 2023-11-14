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

use Article\Entities\Article;
use Article\Entities\Component\Supplier;
use Article\Entities\VO\ArticleQuantity;
use Article\Entities\VO\Packaging;
use Shared\Entities\ResourceUuidInterface;
use Shared\Entities\VO\FamilyLog;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Taxes;

final class CreateArticleResponse
{
    public static function create(Article $article): self
    {
        return new self(
            $article->uuid(),
            $article->name(),
            $article->supplier(),
            $article->packaging(),
            $article->price(),
            $article->taxes(),
            $article->minStock(),
            $article->zoneStorages(),
            $article->familyLog(),
            $article->quantity(),
            $article->isActive(),
            $article->slug()
        );
    }

    private function __construct(
        private readonly ResourceUuidInterface $uuid,
        private readonly NameField $name,
        private readonly Supplier $supplier,
        private readonly Packaging $packaging,
        private readonly float $price,
        private readonly Taxes $taxes,
        private readonly float $minStock,
        private readonly array $zoneStorages,
        private readonly FamilyLog $familyLog,
        private readonly ArticleQuantity $quantity,
        private readonly bool $active,
        private readonly string $slug
    ) {
    }

    public function uuid(): ResourceUuidInterface
    {
        return $this->uuid;
    }

    public function name(): NameField
    {
        return $this->name;
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    public function packaging(): Packaging
    {
        return $this->packaging;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function taxes(): Taxes
    {
        return $this->taxes;
    }

    public function minStock(): float
    {
        return $this->minStock;
    }

    public function zoneStorages(): array
    {
        return $this->zoneStorages;
    }

    public function familyLog(): FamilyLog
    {
        return $this->familyLog;
    }

    public function quantity(): ArticleQuantity
    {
        return $this->quantity;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
