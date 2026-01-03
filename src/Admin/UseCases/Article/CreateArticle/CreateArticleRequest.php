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

namespace Admin\UseCases\Article\CreateArticle;

use Admin\Entities\FamilyLog\FamilyLog;
use Admin\Entities\Supplier\Supplier;
use Admin\Entities\Tax\Tax;
use Admin\Entities\Unit\Unit;
use Admin\Entities\ZoneStorage\ZoneStorage;

interface CreateArticleRequest
{
    public function name(): string;

    public function supplier(): Supplier;

    /**
     * @return array{array{Unit, float}, array{Unit, float}|null, array{Unit, float}|null}
     */
    public function packaging(): array;

    public function unitPrice(): int;

    public function tax(): Tax;

    public function minStock(): float;

    public function quantity(): ?float;

    /**
     * @return array<ZoneStorage>
     */
    public function zoneStorages(): array;

    public function familyLog(): FamilyLog;
}
