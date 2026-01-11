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
 * Interface pour les filtres Doctrine réutilisables, inspiré d'API Platform.
 */
interface FilterInterface
{
    /**
     * Applique le filtre au QueryBuilder.
     *
     * @param QueryBuilder $queryBuilder  Le QueryBuilder à modifier
     * @param string       $alias         L'alias de l'entité (ex: 'inventory')
     * @param string       $property      Le nom de la propriété à filtrer
     * @param mixed        $value         La valeur du filtre
     * @param string       $parameterName Nom de paramètre unique pour éviter les collisions
     */
    public function apply(
        QueryBuilder $queryBuilder,
        string $alias,
        string $property,
        mixed $value,
        string $parameterName,
    ): void;

    /**
     * Vérifie si le filtre doit être appliqué (valeur valide/non-nulle).
     */
    public function isApplicable(mixed $value): bool;
}
