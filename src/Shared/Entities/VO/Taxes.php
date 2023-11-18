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

namespace Shared\Entities\VO;

use Shared\Entities\Exception\InvalidRateFormatException;

final class Taxes
{
    private string $name;

    public static function fromFloat(float $rate): self
    {
        return new self($rate);
    }

    public static function fromPercent(string $name): self
    {
        preg_match("/^(\\d*)(,(\\d*?))\u{a0}%\$/u", trim($name), $str);
        $float = $str[1] . '.' . $str[3];

        return new self((float) $float);
    }

    private function __construct(private float $rate)
    {
        $fmtPercent = new \NumberFormatter('fr_FR', \NumberFormatter::PERCENT);
        $fmtPercent->setAttribute($fmtPercent::FRACTION_DIGITS, 2);

        $this->rate = $rate / 100;

        $fraction = explode('.', (string) $rate);
        if (isset($fraction[1]) && \strlen($fraction[1]) > 2) {
            $this->rate = $rate;
        }

        if (($formattedRate = $fmtPercent->format($this->rate)) === false) {
            throw new InvalidRateFormatException($this->rate);
        }

        $this->name = $formattedRate;
    }

    public function rate(): float
    {
        return $this->rate;
    }

    public function name(): string
    {
        return $this->name;
    }
}
