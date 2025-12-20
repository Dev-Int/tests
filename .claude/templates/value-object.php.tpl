<?php

namespace {Namespace}\Entities\VO;

use {Namespace}\Entities\Exception\Invalid{Name}Field;

final readonly class {Name}Field
{
    private function __construct(
        private {Type} $value,
    ) {}

    public static function from{FactoryType}({Type} $value): self
    {
        // Validation
        if (empty($value)) {
            throw Invalid{Name}Field::empty();
        }

        return new self($value);
    }

    public function to{FactoryType}(): {Type}
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
