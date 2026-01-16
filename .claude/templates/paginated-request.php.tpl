<?php

declare(strict_types=1);

namespace {BC}\UseCases\{Entity}\Get{EntityPlural};

interface Get{EntityPlural}Request
{
    public function page(): int;

    public function itemsPerPage(): int;
}
