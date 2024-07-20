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

namespace Admin\Adapters\Controller\Symfony\Controller\Article\ChangeArticleFinancialInformation;

use Admin\Adapters\Gateway\ORM\Entity\Tax;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeArticleFinancialInformationInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public int $amount,
        #[Assert\NotBlank]
        #[Assert\Valid]
        public Tax $tax,
        #[Assert\NotBlank]
        #[Assert\Regex('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/')]
        public string $uuid
    ) {
    }
}
