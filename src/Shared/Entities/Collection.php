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

namespace Shared\Entities;

/**
 * @template T
 *
 * @extends \Iterator<array-key, T>
 */
interface Collection extends \Iterator
{
    public function add(object $item): void;

    /**
     * @return iterable<T>
     */
    public function toArray(): iterable;
}
