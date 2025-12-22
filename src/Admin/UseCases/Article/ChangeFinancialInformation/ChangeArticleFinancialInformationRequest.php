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

namespace Admin\UseCases\Article\ChangeFinancialInformation;

use Admin\Entities\Tax\Tax;
use Shared\Entities\ResourceUuid;

interface ChangeArticleFinancialInformationRequest
{
    public function amount(): int;

    public function tax(): Tax;

    public function uuid(): ResourceUuid;
}
