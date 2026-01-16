<?php

declare(strict_types=1);

namespace {BC}\UseCases\{Entity}\Get{EntityPlural};

use {BC}\Entities\Repository\{Entity}Repository;

final readonly class Get{EntityPlural}
{
    public function __construct(private {Entity}Repository $repository)
    {
    }

    public function execute(Get{EntityPlural}Request $request): Get{EntityPlural}Response
    {
        $collection = $this->repository->getAll{EntityPlural}Paginated(
            $request->page(),
            $request->itemsPerPage()
        );

        return new Get{EntityPlural}Response($collection);
    }
}