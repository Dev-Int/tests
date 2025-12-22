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

use Admin\Entities\Tax\Tax;
use Admin\UseCases\Article\ChangeFinancialInformation\ChangeArticleFinancialInformationRequest;
use Shared\Entities\ResourceUuid;

final readonly class ChangeArticleFinancialInformationApiRequest implements ChangeArticleFinancialInformationRequest
{
    public function __construct(public int $amount, public Tax $tax, public string $uuid)
    {
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function tax(): Tax
    {
        return $this->tax;
    }

    public function uuid(): ResourceUuid
    {
        return ResourceUuid::fromString($this->uuid);
    }
}
