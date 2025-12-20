<?php

namespace {ProviderBC}\Contracts;

use {ProviderBC}\Contracts\Exception\{Entity}NotFound;

interface {Name}Provider
{
    /**
     * @throws {Entity}NotFound
     */
    public function provide(string $uuid): {Entity}Data;

    /**
     * @return iterable<{Entity}Data>
     */
    public function provideAll(?array $ids = null): iterable;
}