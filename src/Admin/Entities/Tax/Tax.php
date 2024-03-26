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

namespace Admin\Entities\Tax;

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class Tax
{
    public static function create(ResourceUuid $uuid, NameField $name, float $rate): self
    {
        return new self($uuid, $name, $rate);
    }

    private function __construct(
        private readonly ResourceUuid $uuid,
        private readonly NameField $name,
        private float $rate
    ) {
        $coeff = preg_match('/0,|\.\d+/', (string) $this->rate) === 1 ? 1 : 100;
        $this->rate = $rate / $coeff;
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function rate(): float
    {
        return $this->rate;
    }

    public function name(): NameField
    {
        return $this->name;
    }
}
