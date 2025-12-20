<?php

namespace {BC}\UseCases\Gateway\Finder;

use {BC}\Entities\{Entity}\{Entity};
use Shared\Entities\VO\ResourceUuid;

interface {Entity}Finder
{
    public function find(ResourceUuid|string $uuid): ?{Entity};

    /**
     * @return iterable<{Entity}>
     */
    public function findAll(): iterable;

    /**
     * @param string[] $uuids
     * @return iterable<{Entity}>
     */
    public function findByUuids(array $uuids): iterable;
}