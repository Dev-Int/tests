<?php

namespace {BC}\Entities\{Entity};

use Shared\Entities\VO\NameField;
use Shared\Entities\VO\ResourceUuid;

final class {Entity}
{
    public static function create(string $name): self
    {
        return new self(
            ResourceUuid::generate(),
            NameField::fromString($name),
        );
    }

    private function __construct(
        private ResourceUuid $uuid,
        private NameField $name,
    ) {}

    // Getters
    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function name(): NameField
    {
        return $this->name;
    }

    // Behavior methods
    public function rename(string $name): void
    {
        $this->name = NameField::fromString($name);
    }
}
