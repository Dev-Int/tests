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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\ChangeArticleStorageInformation;

use Admin\Entities\Unit\Unit;
use Admin\UseCases\Article\ChangeStorageInformation\ChangeArticleStorageInformationRequest;

final class ChangeArticleStorageInformationApiRequest implements ChangeArticleStorageInformationRequest
{
    /**
     * @param array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null} $packaging
     */
    public function __construct(public array $packaging, public float $minStock, public string $uuid)
    {
    }

    public function packaging(): array
    {
        return $this->packaging;
    }

    public function minStock(): float
    {
        return $this->minStock;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
