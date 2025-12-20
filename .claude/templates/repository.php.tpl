<?php

namespace {BC}\Entities\{Entity};

use {BC}\Entities\Exception\{Entity}NotFound;
use Shared\Entities\VO\ResourceUuid;

interface {Entity}Repository
{
    /**
     * @throws {Entity}NotFound
     */
    public function getByUuid(ResourceUuid $uuid): {Entity};

    public function save({Entity} $entity): void;

    /**
     * @throws {Entity}NotFound
     */
    public function delete({Entity} $entity): void;
}
