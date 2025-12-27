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

namespace Inventory\Entities\Exception;

use Shared\Entities\Exception\DomainException;
use Shared\Entities\ResourceUuid;

final class ArticleNotFoundInInventory extends DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'Article not found in inventory.';

    public function __construct(
        private readonly ResourceUuid $articleUuid
    ) {
        parent::__construct(self::MESSAGE, DomainException::NOT_FOUND_CODE);
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'articleUuid' => $this->articleUuid->toString(),
        ];
    }
}
