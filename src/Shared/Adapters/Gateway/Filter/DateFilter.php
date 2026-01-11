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

namespace Shared\Adapters\Gateway\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Filtre de plage de dates avec stratégies after/before, inspiré d'API Platform.
 *
 * Usage :
 * - DateFilter::after() pour comparaison >=
 * - DateFilter::before() pour comparaison <=
 */
final class DateFilter implements FilterInterface
{
    public const string AFTER = 'after';
    public const string BEFORE = 'before';

    public static function after(): self
    {
        return new self(self::AFTER);
    }

    public static function before(): self
    {
        return new self(self::BEFORE);
    }

    private function __construct(
        private readonly string $strategy,
    ) {
    }

    #[\Override]
    public function apply(
        QueryBuilder $queryBuilder,
        string $alias,
        string $property,
        mixed $value,
        string $parameterName,
    ): void {
        \assert($value instanceof \DateTimeInterface, 'DateFilter value must be DateTimeInterface');

        $operator = match ($this->strategy) {
            self::AFTER => '>=',
            self::BEFORE => '<=',
            default => throw new \InvalidArgumentException(\sprintf(
                'Invalid DateFilter strategy: %s',
                $this->strategy
            )),
        };

        $queryBuilder
            ->andWhere(\sprintf('%s.%s %s :%s', $alias, $property, $operator, $parameterName))
            ->setParameter($parameterName, $value)
        ;
    }

    #[\Override]
    public function isApplicable(mixed $value): bool
    {
        return $value instanceof \DateTimeInterface;
    }
}
