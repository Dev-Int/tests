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

namespace Admin\Entities\FamilyLog;

use Admin\Entities\Exception\IsAlreadyChildException;
use Shared\Entities\ResourceUuid;
use Shared\Entities\VO\NameField;

final class FamilyLog
{
    public const SLUG_SEPARATOR = '_';

    /**
     * @var array<FamilyLog>|null
     */
    private ?array $children = null;
    private string $slug;
    private string $path;
    private int $level = 1;

    public static function create(ResourceUuid $uuid, NameField $label, ?self $parent = null): self
    {
        return new self($uuid, $label, $parent);
    }

    public static function createFromExistingEntity(
        ResourceUuid $uuid,
        NameField $label,
        string $slug,
        string $path,
        int $level,
        ?self $parent = null
    ): self {
        return new self($uuid, $label, $parent, $slug, $path, $level);
    }

    private function __construct(
        private readonly ResourceUuid $uuid,
        private NameField $label,
        private ?self $parent = null,
        ?string $slug = null,
        ?string $path = null,
        ?int $level = 1
    ) {
        $this->path = $path ?? $label->slugify(self::SLUG_SEPARATOR);
        $this->slug = $slug ?? $label->slugify(self::SLUG_SEPARATOR);
        $this->level = $level ?? 1;

        if ($parent instanceof self) {
            $this->assignParent($parent);
        }
    }

    public function uuid(): ResourceUuid
    {
        return $this->uuid;
    }

    public function label(): NameField
    {
        return $this->label;
    }

    public function changeLabel(NameField $label): void
    {
        $this->label = $label;
        $this->slug = $this->parent instanceof self ?
            $this->parent->slug . self::SLUG_SEPARATOR . $label->slugify(self::SLUG_SEPARATOR) :
            $label->slugify(self::SLUG_SEPARATOR);

        if ($this->children !== null) {
            foreach ($this->children as $child) {
                $child->changeSlugFromParent($this->slug);
            }
        }
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

    public function assignParent(self $parent): void
    {
        $slug = $parent->slug() . self::SLUG_SEPARATOR . $this->label->slugify(self::SLUG_SEPARATOR);
        $this->path = $slug;
        $this->slug = $slug;
        $this->level = $parent->level + 1;

        if ($this->parent instanceof self && $this->parent->uuid() !== $parent->uuid()) {
            $this->parent->removeChild($this);
        }

        if ($this->isChildOf($parent) === false) {
            $parent->addChild($this);
        }

        if ($this->children !== null) {
            foreach ($this->children as $child) {
                $child->assignParent($this);
            }
        }
    }

    public function addChild(self $child): void
    {
        if ($this->children !== null) {
            foreach ($this->children as $item) {
                if ($item->slug === $child->slug) {
                    throw new IsAlreadyChildException($child->slug, $this->slug);
                }
            }
        }

        $this->children[] = $child;
        $child->parent = $this;
    }

    public function isCompatible(self $familyLog): bool
    {
        if ($this->isEqual($familyLog)) {
            return true;
        }

        return ($familyLog->parent instanceof self) && $familyLog->isFamilyMember($this);
    }

    private function isEqual(self $parent): bool
    {
        return $this->slug === $parent->slug;
    }

    private function isFamilyMember(self $parent): bool
    {
        if ($this->parent instanceof self) {
            if ($parent->slug === $this->parent->slug) {
                return true;
            }

            if ($this->parent->parent instanceof self) {
                return $this->parent->isFamilyMember($parent);
            }
        }

        return false;
    }

    private function isChildOf(self $parent): bool
    {
        if ($parent->children() === null) {
            return false;
        }

        foreach ($parent->children() as $child) {
            if ($child->slug === $this->slug) {
                return true;
            }
        }

        return false;
    }

    private function removeChild(self $childToRemove): void
    {
        if ($this->children === null) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        foreach ($this->children as $child) {
            if ($child->slug === $childToRemove->slug) {
                $key = array_search($child, $this->children, true);
                if ($key !== false) {
                    unset($this->children[$key]);
                }
            }
        }
    }

    private function changeSlugFromParent(string $parentSlug): void
    {
        $this->slug = $parentSlug . self::SLUG_SEPARATOR . $this->label->slugify(self::SLUG_SEPARATOR);

        if ($this->children !== null) {
            foreach ($this->children as $child) {
                $child->changeSlugFromParent($this->slug);
            }
        }
    }
}
