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

namespace Shared\Entities\VO;

final class FamilyLog
{
    /**
     * @var array<FamilyLog>|null
     */
    private ?array $children = null;
    private string $slug;
    private string $path;

    public static function create(NameField $name, ?self $parent = null): self
    {
        return new self($name, $parent);
    }

    private function __construct(private readonly NameField $name, private ?self $parent = null)
    {
        $this->path = $name->slugify();
        $this->slug = $name->slugify();

        if (null !== $parent) {
            $this->parent = $parent;
            $this->parent->addChild($this);
            $this->path = $parent->slug() . ':' . $name->slugify();

            if (null !== $this->parent && null !== $this->parent->parent) {
                $this->path = $this->parent->parent->slug() . ':' . $this->parent->slug() . ':' . $name->slugify();
            }
        }
    }

    public function name(): NameField
    {
        return $this->name;
    }

    public function parent(): ?self
    {
        return $this->parent;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function parseTree(): array
    {
        $arrayChildren = [];
        if (null === $this->children) {
            return [$this->name->toString() => $arrayChildren];
        }

        foreach ($this->children as $child) {
            if (null !== $this->hasChildren($child)) {
                $arrayChildren[$child->name->toString()] = $this->hasChildren($child);
            } else {
                $arrayChildren[] = $child->name->toString();
            }
        }

        return [$this->name->toString() => $arrayChildren];
    }

    /**
     * @return array<string>|null
     */
    private function hasChildren(self $familyLog): ?array
    {
        if (null !== $familyLog->children) {
            return array_map(static function ($child) {
                return $child->name->toString();
            }, $familyLog->children);
        }

        return null;
    }

    private function addChild(self $child): void
    {
        $this->children[] = $child;
    }
}
