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

namespace Admin\Adapters\Gateway\ORM\Entity;

use Admin\Adapters\Gateway\ORM\Repository\DoctrineFamilyLogRepository;
use Admin\Entities\FamilyLog as FamilyLogDomain;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shared\Entities\VO\NameField;

#[ORM\Entity(repositoryClass: DoctrineFamilyLogRepository::class)]
class FamilyLog
{
    #[ORM\Id]
    #[ORM\Column]
    private string $slug;
    #[ORM\Column(name: 'label', type: 'string', length: 64)]
    private string $label;
    #[ORM\Column(name: 'path', type: 'string', length: 3000)]
    private string $path = '';
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_path', referencedColumnName: 'slug', onDelete: 'CASCADE')]
    private ?FamilyLog $parent;
    #[ORM\Column(name: 'lvl', nullable: true)]
    private int $level;

    /** @var Collection<FamilyLog> */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function fromDomain(FamilyLogDomain $familyLog): self
    {
        $this->slug = $familyLog->slug();
        $this->label = $familyLog->label()->toString();
        $this->path = $familyLog->path();
        $this->level = $familyLog->level();

        return $this;
    }

    public function toDomain(): FamilyLogDomain
    {
        return FamilyLogDomain::create(NameField::fromString($this->label), $this->parent?->toDomain());
    }

    public function label(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function level(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function parent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection<FamilyLog>
     */
    public function children(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return array<string, array<int|string, iterable<string>|string>>
     */
    public function parseTree(): array
    {
        $arrayChildren = [];
        if ($this->children->count() > 0) {
            return [$this->label => $arrayChildren];
        }
        foreach ($this->children as $child) {
            if ($this->childrenArrayLabels($child) !== null) {
                $arrayChildren[$child->label()] = $this->childrenArrayLabels($child);
            } else {
                $arrayChildren[] = $child->label();
            }
        }

        return [$this->label => $arrayChildren];
    }

    public function getIndentedLabel(): string
    {
        $prefix = str_repeat('|- - ', $this->level);

        return sprintf('%s %s', $prefix, $this->label);
    }

    /**
     * @return iterable<string>|null
     */
    private function childrenArrayLabels(self $familyLog): ?iterable
    {
        if ($familyLog->children->count() > 0) {
            $childrenNames = [];
            foreach ($familyLog->children as $child) {
                $childrenNames[] = $child->label();
            }

            return $childrenNames;
        }

        return null;
    }
}
