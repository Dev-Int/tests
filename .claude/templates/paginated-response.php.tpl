<?php

declare(strict_types=1);

namespace {BC}\UseCases\{Entity}\Get{EntityPlural};

use {BC}\Entities\{Entity}\{Entity}Collection;

final readonly class Get{EntityPlural}Response
{
    public function __construct(public {Entity}Collection $collection)
    {
    }
}