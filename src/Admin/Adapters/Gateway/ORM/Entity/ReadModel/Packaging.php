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

namespace Admin\Adapters\Gateway\ORM\Entity\ReadModel;

use Symfony\Component\Validator\Constraints as Assert;

final class Packaging
{
    public function __construct(
        #[Assert\Valid]
        public ?Storage $parcel = null,
        #[Assert\Valid]
        public ?Storage $subPackage = null,
        #[Assert\Valid]
        public ?Storage $consumeUnit = null
    ) {
    }
}
