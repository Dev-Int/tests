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

namespace Inventory\Adapters\Controller\Symfony\Controller\CreateInventory;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateInventoryInput
{
    /**
     * @param array<string> $zoneStorages
     */
    public function __construct(
        #[Assert\NotBlank]
        public ?\DateTimeImmutable $date = null,
        #[Assert\NotBlank]
        #[Assert\All([
            new Assert\Uuid(),
        ])]
        public array $zoneStorages = []
    ) {
    }
}
