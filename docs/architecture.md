# Architecture - Bounded Contexts

DDD + Clean Architecture patterns for inter-BC communication

---

## Dependency Rules (Deptrac enforced)

### Intra-BC

```
Entities  → Shared\Entities only
UseCases  → Entities + Shared\Entities
Adapters  → UseCases + Entities + Shared
```

**Rule**: Inner layers (Entities, UseCases) MUST NEVER depend on outer layers (Adapters, Frameworks)

**See**: `docs/GLOSSARY.md#layers` for detailed layer definitions

### Inter-BC

```
✅ BC1\Adapters → BC2\Contracts (interface)
❌ BC1 → BC2\Entities
❌ BC1 → BC2\UseCases
```

**Reason**: Avoid tight coupling, respect bounded context separation

**See**: `docs/GLOSSARY.md#contract`, `docs/GLOSSARY.md#provider`

---

## Inter-BC Communication Patterns

### Pattern 1: Query Contract (Data Access)

**When**: BC2 needs read access to BC1 data

**Structure**:
```
BC1/Contracts/{Name}Provider.php              # Interface
BC1/Adapters/Contracts/BC1{Name}Provider.php  # Implementation (uses Finder)
BC2/Adapters/*/{Consumer}.php                 # Uses interface
```

**Contract**:
```php
interface {Name}Provider {
    public function provide(string $uuid): {Entity}Data; // throws
    public function provideAll(?array $ids = null): iterable;
}
```

**Provider** (uses Finder, NOT Repository):
```php
final readonly class BC1{Name}Provider implements {Name}Provider {
    public function __construct(private {Entity}Finder $finder) {}
    private function toData({Entity} $entity): {Entity}Data { /* ... */ }
}
```

**Consumer**:
```php
use BC1\Contracts\{Name}Provider; // Interface only

public function __construct(private {Name}Provider $provider) {}
```

**See**: Skill `add-bc-contract` | Templates: `contract.php.tpl`, `provider.php.tpl`

---

### Pattern 2: TwigComponent Provider (Form Select)

**When**: BC2 form needs BC1 select options

**Contract**:
```php
interface {Name}Provider {
    /** @return array<string, string> [uuid => label] */
    public function getAllForChoice(): array;
}
```

**TwigComponent**:
```php
use BC1\Contracts\{Name}Provider; // Contract only

#[AsTwigComponent('BC2:Form:{Name}Choice')]
final readonly class {Name}Choice {
    public function __construct(private {Name}Provider $provider) {}
    public function getChoices(): array { return $this->provider->getAllForChoice(); }
}
```

**See**: Skill `add-bc-contract`

---

### Pattern 3: Exceptions

**Option A (recommended)**: Exception in Contract namespace
```php
// BC1/Contracts/Exception/{Entity}NotFound.php
namespace BC1\Contracts\Exception;
```

**Benefits**: Exception is part of public API, consumer can catch specifically

**Option B**: Map in consumer
```php
use BC1\Contracts\Exception\{Entity}NotFound as BC1NotFound;
use BC2\Entities\Exception\{Entity}NotAvailable;

try { $data = $this->provider->provide($uuid); }
catch (BC1NotFound $e) { throw {Entity}NotAvailable::fromUuid($uuid); }
```

**Benefits**: Ubiquitous language per BC, total isolation

---

## Configuration

### Autowiring (only if necessary)

```yaml
# BC1/Frameworks/config/services.yaml
BC1\Contracts\{Name}Provider:
    class: BC1\Adapters\Contracts\BC1{Name}Provider
```

### Deptrac

```yaml
# BC2/Frameworks/deptrac.yaml
BC2\Adapters:
    - BC1\Contracts      # ONLY Contracts (not Entities/UseCases)
```

**Validate**: `bin/deptrac analyse`

---

## TwigComponents Organization

| Location | Usage | Dependencies |
|----------|-------|--------------|
| `BC\Twig\Components` | BC-specific components | ✅ Domain dependencies allowed |
| `src/Twig/Components` | Generic components (e.g., Icon) | ❌ No BC coupling |

---

## Anti-patterns

```php
// ❌ Direct entity dependency
use Admin\Entities\Article;

// ❌ Use case dependency
use Admin\UseCases\CreateArticle;

// ❌ Repository in provider (use Finder)
public function __construct(private ArticleRepository $repository) {}

// ❌ Shared Kernel too large (Article is Admin-specific)
Shared\Entities\Article.php

// ✅ Contract only
use Admin\Contracts\ArticleProvider;
```

**Rule**: Shared contains ONLY truly transverse concepts (ResourceUuid, NameField, EmailField)

**See**: `docs/GLOSSARY.md#shared-vs-bc-specific`

---

## Checklist

- [ ] Contract in `BC1/Contracts/`
- [ ] Implementation in `BC1/Adapters/Contracts/` (uses Finder, NOT Repository)
- [ ] Autowiring in `BC1/Frameworks/config/services.yaml`
- [ ] Deptrac updated in `BC2/Frameworks/deptrac.yaml`
- [ ] Tests for provider implementation
- [ ] `make qa` passes

---

## Skills & References

**Skills**: `add-bc-contract` (automate Contract creation)

**Templates**: `contract.php.tpl`, `provider.php.tpl`

**Detailed guide**: `docs/guides/bounded-contexts.md`

**Definitions**: `docs/GLOSSARY.md#contract`, `docs/GLOSSARY.md#provider`, `docs/GLOSSARY.md#finder`
