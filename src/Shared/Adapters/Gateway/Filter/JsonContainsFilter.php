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
use Shared\Entities\ResourceUuid;

/**
 * Filtre pour rechercher dans les champs JSON avec PostgreSQL.
 *
 * Utilise la fonction DQL TEXT() (définie dans TextFunction.php) pour convertir
 * le JSON en texte, puis applique LIKE pour la recherche par sous-chaîne.
 */
final class JsonContainsFilter implements FilterInterface
{
    #[\Override]
    public function apply(
        QueryBuilder $queryBuilder,
        string $alias,
        string $property,
        mixed $value,
        string $parameterName,
    ): void {
        $searchValue = match (true) {
            $value instanceof ResourceUuid => $value->toString(),
            \is_string($value) => $value,
            default => throw new \InvalidArgumentException('JsonContainsFilter value must be string or ResourceUuid'),
        };

        // TEXT() est une fonction DQL personnalisée définie dans TextFunction.php
        // Elle convertit le JSON en texte pour la compatibilité LIKE de PostgreSQL
        // Échapper les caractères spéciaux LIKE pour éviter les injections logiques
        $escapedValue = $this->escapeLikeValue($searchValue);

        $queryBuilder
            ->andWhere(\sprintf('TEXT(%s.%s) LIKE :%s', $alias, $property, $parameterName))
            ->setParameter($parameterName, '%"' . $escapedValue . '"%')
        ;
    }

    #[\Override]
    public function isApplicable(mixed $value): bool
    {
        return $value instanceof ResourceUuid || (\is_string($value) && $value !== '');
    }

    /**
     * Échappe les caractères spéciaux LIKE (%, _, \) pour éviter les injections logiques.
     */
    private function escapeLikeValue(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
