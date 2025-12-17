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

final class ArticleNotFound extends \Exception implements \JsonSerializable
{
    public const string MESSAGE = 'Article not found.';

    public function __construct(private readonly string $uuid)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return ['uuid' => $this->uuid];
    }
}
