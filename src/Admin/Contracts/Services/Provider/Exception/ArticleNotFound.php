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

namespace Admin\Contracts\Services\Provider\Exception;

use Shared\Entities\Exception\DomainException;

final class ArticleNotFound extends DomainException implements \JsonSerializable
{
    public const string MESSAGE = 'Article not found.';

    public function __construct(private readonly string $uuid)
    {
        parent::__construct(self::MESSAGE, DomainException::NOT_FOUND_CODE);
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return ['uuid' => $this->uuid];
    }
}
