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

namespace Admin\Tests\DataBuilder;

use Admin\Entities\Article\Article;
use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Tax\Tax;
use Admin\Entities\Unit\Unit;
use Admin\Entities\ZoneStorage\ZoneStorage;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Packaging;

final class ArticleDataBuilder implements DataBuilderInterface
{
    public const UUID_VALID = '652313f1-e2c6-4fea-a0f6-69e881091c0f';

    private string $uuid;
    private string $name;
    private Supplier $supplier;

    /**
     * @var array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    private array $packaging;
    private int $amount = 685;
    private Tax $tax;
    private float $minStock = 8.5;

    /**
     * @var array<ZoneStorage>
     */
    private array $zoneStorages = [];
    private FamilyLog $familyLog;
    private float $quantity = 12.500;

    /**
     * @param array<ZoneStorage>                                                          $zoneStorages
     * @param array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null} $packaging
     */
    public function create(
        string $name,
        Supplier $supplier,
        Tax $tax,
        array $zoneStorages,
        FamilyLog $familyLog,
        array $packaging
    ): self {
        $this->uuid = self::UUID_VALID;
        $this->name = $name;
        $this->supplier = $supplier;
        $this->tax = $tax;
        $this->zoneStorages = $zoneStorages;
        $this->familyLog = $familyLog;
        $this->packaging = $packaging;

        return $this;
    }

    public function withUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function withAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function withMinStock(float $minStock): self
    {
        $this->minStock = $minStock;

        return $this;
    }

    public function withQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function build(): Article
    {
        return Article::create(
            ResourceUuid::fromString($this->uuid),
            NameField::fromString($this->name),
            $this->supplier,
            Packaging::fromArray($this->packaging),
            Amount::fromInt($this->amount),
            $this->tax,
            $this->minStock,
            $this->zoneStorages,
            $this->familyLog,
            true,
            $this->quantity
        );
    }
}
