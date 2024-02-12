<?php

declare(strict_types=1);

/*
 * This file is part of the Tests package.
 *
 * (c) Dev-Int Création <info@developpement-interessant.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Admin\Entities;

use Shared\Entities\VO\NameField;

final class FamilyLog
{
    /**
     * @var array<FamilyLog>|null
     */
    private ?array $children = null;
    private string $slug;
    private string $path;

    public static function create(NameField $label, ?self $parent = null): self
    {
        return new self($label, $parent);
    }

    private function __construct(private readonly NameField $label, private readonly ?self $parent = null)
    {
        $this->path = $label->slugify();
        $this->slug = $label->slugify();

        if (null !== $parent && $this->parent !== null) {
            $this->parent->addChild($this);
            $slug = $parent->slug() . '-' . $label->slugify();
            $this->path = $slug;
            $this->slug = $slug;
        }
    }

    public function label(): NameField
    {
        return $this->label;
    }

    public function parent(): ?self
    {
        return $this->parent;
    }

    /**
     * @return array<FamilyLog>|null
     */
    public function children(): ?array
    {
        return $this->children;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    /**
     * @return array<string, array<int|string, array<string>|string>>
     */
    public function parseTree(): array
    {
        $arrayChildren = [];
        if (null === $this->children) {
            return [$this->label->toString() => $arrayChildren];
        }

        foreach ($this->children as $child) {
            if (null !== $this->hasChildren($child)) {
                $arrayChildren[$child->label->toString()] = $this->hasChildren($child);
            } else {
                $arrayChildren[] = $child->label->toString();
            }
        }

        return [$this->label->toString() => $arrayChildren];
    }

    public function addChild(self $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @return array<string>|null
     */
    private function hasChildren(self $familyLog): ?array
    {
        if (null !== $familyLog->children) {
            return array_map(static function (FamilyLog $child) {
                return $child->label->toString();
            }, $familyLog->children);
        }

        return null;
    }
}
