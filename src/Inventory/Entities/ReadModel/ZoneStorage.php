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

namespace Inventory\Entities\ReadModel;

use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final readonly class ZoneStorage
{
    public function __construct(
        public ResourceUuid $uuid,
        public NameField $label
    ) {
    }
}
