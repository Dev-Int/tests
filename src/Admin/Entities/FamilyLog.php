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

    private int $level;

    public static function create(NameField $label, ?self $parent = null): self
    {
        return new self($label, $parent);
    }

    private function __construct(private NameField $label, private ?self $parent = null)
    {
        $this->path = $label->slugify();
        $this->slug = $label->slugify();
        $this->level = 1;

        if (null !== $parent && $this->parent !== null) {
            $this->assignParent($parent, $label);
        }
    }

    public function label(): NameField
    {
        return $this->label;
    }

    public function changeLabel(NameField $label): void
    {
        $this->label = $label;
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

    public function level(): int
    {
        return $this->level;
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
            if (null !== $this->getChildrenLabel($child)) {
                $arrayChildren[$child->label->toString()] = $this->getChildrenLabel($child);
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

    public function assignParent(self $parent, NameField $label): void
    {
        $this->parent = $parent;

        if ($this->isChild($parent) === false) {
            $this->parent->addChild($this);
        }

        $slug = $parent->slug() . '-' . $label->slugify();
        $this->path = $slug;
        $this->slug = $slug;

        if ($this->parent !== null) {
            $this->level = $this->parent->level + 1;
        }

        if ($this->children !== null) {
            foreach ($this->children as $child) {
                $child->assignParent($this, $child->label);
            }
        }
    }

    /**
     * @return array<string>|null
     */
    private function getChildrenLabel(self $familyLog): ?array
    {
        if (null !== $familyLog->children) {
            return array_map(static function (FamilyLog $child) {
                return $child->label->toString();
            }, $familyLog->children);
        }

        return null;
    }

    private function isChild(self $parent): bool
    {
        if ($parent->children !== null) {
            foreach ($parent->children as $child) {
                if ($child->slug === $this->slug) {
                    return true;
                }
            }
        }

        return false;
    }
}
