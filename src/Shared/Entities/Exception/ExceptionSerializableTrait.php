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

namespace Shared\Entities\Exception;

trait ExceptionSerializableTrait
{
    /**
     * @return iterable<string, array<int, string>|int|string>
     */
    public function toJson(): iterable
    {
        $data = [
            'class' => static::class,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile() . ':' . $this->getLine(),
        ];

        $trace = $this->getTrace();

        foreach ($trace as $frame) {
            if (isset($frame['file'], $frame['line'])) {
                $data['trace'][] = $frame['file'] . ':' . $frame['line'];
            }
        }

        return $data;
    }
}
