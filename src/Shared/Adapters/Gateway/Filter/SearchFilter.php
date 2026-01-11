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
 * Filtre de correspondance exacte pour les propriétés (status, uuid, chaînes).
 *
 * Supporte les BackedEnum en extrayant leur valeur sous-jacente.
 */
final class SearchFilter implements FilterInterface
{
    #[\Override]
    public function apply(
        QueryBuilder $queryBuilder,
        string $alias,
        string $property,
        mixed $value,
        string $parameterName,
    ): void {
        $queryValue = $value instanceof \BackedEnum ? $value->value : $value;

        $queryBuilder
            ->andWhere(\sprintf('%s.%s = :%s', $alias, $property, $parameterName))
            ->setParameter($parameterName, $queryValue)
        ;
    }

    #[\Override]
    public function isApplicable(mixed $value): bool
    {
        return $value !== null;
    }
}
