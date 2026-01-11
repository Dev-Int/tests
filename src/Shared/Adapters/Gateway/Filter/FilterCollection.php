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
 * Collection fluide pour appliquer plusieurs filtres à un QueryBuilder.
 *
 * Génère automatiquement des noms de paramètres uniques pour éviter les collisions.
 * Les filtres avec des valeurs non-applicables sont ignorés silencieusement.
 *
 * Usage :
 * $filters = new FilterCollection();
 * $filters
 *     ->add(new SearchFilter(), 'entity', 'status', $status)
 *     ->add(DateFilter::after(), 'entity', 'date', $dateAfter)
 *     ->add(DateFilter::before(), 'entity', 'date', $dateBefore)
 *     ->apply($queryBuilder);
 */
final class FilterCollection
{
    /** @var array<int, array{filter: FilterInterface, alias: string, property: string, value: mixed}> */
    private array $filters = [];

    private int $parameterCounter = 0;

    public function add(
        FilterInterface $filter,
        string $alias,
        string $property,
        mixed $value,
    ): self {
        if ($filter->isApplicable($value)) {
            $this->filters[] = [
                'filter' => $filter,
                'alias' => $alias,
                'property' => $property,
                'value' => $value,
            ];
        }

        return $this;
    }

    public function apply(QueryBuilder $queryBuilder): void
    {
        foreach ($this->filters as $filterConfig) {
            $parameterName = $this->generateParameterName($filterConfig['property']);

            $filterConfig['filter']->apply(
                $queryBuilder,
                $filterConfig['alias'],
                $filterConfig['property'],
                $filterConfig['value'],
                $parameterName,
            );
        }
    }

    /**
     * Génère un nom de paramètre unique pour éviter les collisions.
     */
    private function generateParameterName(string $property): string
    {
        return 'filter_' . $property . '_' . (++$this->parameterCounter);
    }
}
