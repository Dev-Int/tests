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

namespace Inventory\Entities\VO;

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\Amount;
use Shared\Entities\VO\NameField;
use Shared\Entities\VO\Quantity;

final readonly class Article
{
    public function __construct(
        public ResourceUuid $uuid,
        public NameField $name,
        public Amount $unitPrice,
        public Quantity $quantity,
        public string $slug,
    ) {
    }
}
