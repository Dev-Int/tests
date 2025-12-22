<?php

namespace {BC}\UseCases\Gateway\Finder;

use {BC}\Entities\{Entity}\{Entity};
use Shared\Entities\VO\ResourceUuid;

interface {Entity}Finder
{
    public function findByUuid(ResourceUuid|string $uuid): ?{Entity};

    /**
     * @return iterable<{Entity}>
     */
    public function findAll{EntityPlural}(): iterable;

    /**
     * @param array<string> $uuids
     *
     * @return iterable<{Entity}>
     */
    public function findByUuids(array $uuids): iterable;
}