<?php

namespace {ProviderBC}\Adapters\Contracts;

use {ProviderBC}\Contracts\{Name}Provider;
use {ProviderBC}\Contracts\Exception\{Entity}NotFound;
use {ProviderBC}\UseCases\Gateway\Finder\{Entity}Finder;

final readonly class {ProviderBC}{Name}Provider implements {Name}Provider
{
    public function __construct(
        private {Entity}Finder $finder  // Uses Finder (not Repository)
    ) {}

    public function provide(string $uuid): {Entity}Data
    {
        $entity = $this->finder->find($uuid);

        if ($entity === null) {
            throw {Entity}NotFound::fromUuid($uuid);
        }

        return new {Entity}Data(
            $entity->uuid()->toString(),
            $entity->name()->toString(),
        );
    }

    public function provideAll(?array $ids = null): iterable
    {
        $entities = $ids === null
            ? $this->finder->findAll()
            : $this->finder->findByUuids($ids);

        foreach ($entities as $entity) {
            yield new {Entity}Data(
                $entity->uuid()->toString(),
                $entity->name()->toString(),
            );
        }
    }
}