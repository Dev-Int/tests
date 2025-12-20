<?php

namespace {BC}\UseCases\{Entity}\{Action}{Entity};

use {BC}\Entities\{Entity}\{Entity}Repository;

final readonly class {Action}{Entity}
{
    public function __construct(
        private {Entity}Repository $repository,
    ) {}

    public function execute({Action}{Entity}Request $request): {Action}{Entity}Response
    {
        $entity = $this->repository->getByUuid($request->uuid());

        // Business logic
        $entity->{actionMethod}($request->param());

        $this->repository->save($entity);

        return new {Action}{Entity}Response($entity);
    }
}
