# Bounded Contexts - Inter-BC Communication Guide

Complete guide for Domain-Driven Design Bounded Contexts communication patterns.

**Note**: This is the detailed guide. For quick reference, see `docs/architecture.md`.

---

## Golden Rule

**Inter-BC Communication**:

```
NO: BC1 → BC2\Entities
NO: BC1 → BC2\UseCases
OK: BC1\Adapters → BC2\Contracts (interface only)
```

**Reason**: Avoid tight coupling, respect bounded context separation

**See**: `docs/GLOSSARY.md#bounded-context`, `docs/GLOSSARY.md#contract`

---

## Communication Patterns

### Pattern 1: Query Contract (Provider)

**Use case**: BC2 needs read access to BC1 data

**Example**: Inventory needs to read Articles from Admin

**Key points**:
- Contract interface in `BC1/Contracts/`
- Provider implementation in `BC1/Adapters/Contracts/`
- Provider uses **Finder** (NOT Repository) - read-only access
- Consumer depends ONLY on Contract interface
- Data transferred via DTO (`{Entity}Data`)

**See**:
- Pattern details: `docs/architecture.md#pattern-1-query-contract`
- Skill: `.claude/skills/add-bc-contract/`
- Templates: `contract.php.tpl`, `provider.php.tpl`

---

### Pattern 2: TwigComponent Provider (Form Select)

**Use case**: BC2 form needs BC1 select options

**Example**: Inventory form needs ZoneStorage select (from Admin)

**Key points**:
- Contract returns `array<string, string>` (uuid => label)
- TwigComponent depends only on Contract
- Provider uses Finder to fetch data

**See**:
- Pattern details: `docs/architecture.md#pattern-2-twigcomponent-provider`
- Skill: `.claude/skills/add-bc-contract/`

---

## Configuration

### Autowiring

Register Contract → Provider mapping:

```yaml
# BC1/Frameworks/config/services.yaml
BC1\Contracts\{Name}Provider:
    class: BC1\Adapters\Contracts\BC1{Name}Provider
```

### Deptrac

Allow Consumer BC to use Provider BC Contracts:

```yaml
# BC2/Frameworks/deptrac.yaml
BC2\Adapters:
    - BC1\Contracts      # ONLY Contracts (not Entities/UseCases)
```

**Validate**: `bin/deptrac analyse`

**See**: `docs/architecture.md#configuration`

---

## Complete Example: Admin → Inventory

### Context

- **Admin BC**: Manages Articles
- **Inventory BC**: Needs to read Articles for inventory management

### Solution: ArticleProvider Contract

**Files created**:
```
Admin/Contracts/ArticleProvider.php                          # Interface
Admin/Contracts/DTO/ArticleData.php                          # DTO
Admin/Contracts/Exception/ArticleNotFound.php                # Exception
Admin/Adapters/Contracts/AdminArticleProvider.php            # Implementation
Admin/Frameworks/config/services.yaml                        # Autowiring
Inventory/Frameworks/deptrac.yaml                            # Deptrac rule
```

**Usage in Inventory BC**:
```php
use Admin\Contracts\ArticleProvider; // Interface only

public function __construct(private ArticleProvider $articleProvider) {}

$articleData = $this->articleProvider->provide($articleUuid);
```

**Implementation details**: See `docs/architecture.md#inter-bc-communication-patterns`

**Automate**: Use skill `add-bc-contract`

---

## Anti-Patterns

**Direct Entity dependency**:
```php
use Admin\Entities\Article; // ❌ NEVER
```

**Use case dependency**:
```php
use Admin\UseCases\CreateArticle; // ❌ NEVER
```

**Repository in Provider** (use Finder):
```php
public function __construct(private ArticleRepository $repo) {} // ❌
public function __construct(private ArticleFinder $finder) {}   // ✅
```

**Shared Kernel too large**:
```php
// ❌ Article is Admin-specific, NOT shared
Shared\Entities\Article.php

// ✅ ResourceUuid is truly transverse
Shared\Entities\VO\ResourceUuid.php
```

**See**: `docs/architecture.md#anti-patterns`, `docs/GLOSSARY.md#shared-vs-bc-specific`

---

## Checklist

When adding inter-BC communication:

- [ ] Contract interface in `BC1/Contracts/`
- [ ] Provider implementation in `BC1/Adapters/Contracts/` (uses **Finder**, not Repository)
- [ ] DTO in `BC1/Contracts/DTO/` (if needed)
- [ ] Exception in `BC1/Contracts/Exception/` (if needed)
- [ ] Autowiring in `BC1/Frameworks/config/services.yaml`
- [ ] Deptrac updated in `BC2/Frameworks/deptrac.yaml`
- [ ] Tests for Provider implementation
- [ ] `bin/deptrac analyse` passes
- [ ] `make qa` passes

---

## Use Cases

### UC1: Read Single Entity

**Example**: Inventory reads one Article by UUID

**Contract method**:
```php
public function provide(string $uuid): ArticleData; // throws ArticleNotFound
```

**See**: `docs/architecture.md#pattern-1-query-contract`

---

### UC2: Read Multiple Entities

**Example**: Inventory reads all Articles or subset by IDs

**Contract method**:
```php
/** @return iterable<ArticleData> */
public function provideAll(?array $ids = null): iterable;
```

---

### UC3: Form Select Options

**Example**: Inventory form with Article dropdown

**Contract method**:
```php
/** @return array<string, string> [uuid => label] */
public function getAllForChoice(): array;
```

**See**: `docs/architecture.md#pattern-2-twigcomponent-provider`

---

### UC4: Validation Check

**Example**: Inventory checks if Article exists

**Contract method**:
```php
public function exists(string $uuid): bool;
```

---

### UC5: Aggregate Data

**Example**: Inventory needs Article count

**Contract method**:
```php
public function count(): int;
public function countActive(): int;
```

---

## Testing Contracts

### Unit Test (Provider Implementation)

**Test**:
```php
final class AdminArticleProviderTest extends TestCase {
    public function testProvide(): void {
        $article = ArticleDataBuilder::anArticle()->build();
        $finder = $this->createMock(ArticleFinder::class);
        $finder->expects($this->once())
            ->method('find')
            ->willReturn($article);

        $provider = new AdminArticleProvider($finder);
        $data = $provider->provide($article->uuid()->toString());

        self::assertInstanceOf(ArticleData::class, $data);
    }
}
```

**See**: Skill `tdd-workflow`, template `test-unit.php.tpl`

---

### Functional Test (Consumer)

Test Consumer BC using Provider:

```php
final class InventoryConsumerTest extends BaseFunctionalTestCase {
    use Factories;

    public function testConsumerUsesProvider(): void {
        // Setup: Create Article in Admin BC
        $article = ArticleFactory::createOne(['name' => 'Test Article']);

        // Action: Inventory BC uses ArticleProvider
        $this->client->request('GET', '/inventory/articles');

        // Assert: Article data visible in Inventory
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.article-name', 'Test Article');
    }
}
```

**See**: Skill `create-functional-test`, template `test-functional.php.tpl`

---

## Skills & References

**Skills**: `add-bc-contract` (automate Contract creation)

**Detailed patterns**: `docs/architecture.md#inter-bc-communication-patterns`

**Templates**: `contract.php.tpl`, `provider.php.tpl`

**Definitions**:
- `docs/GLOSSARY.md#bounded-context`
- `docs/GLOSSARY.md#contract`
- `docs/GLOSSARY.md#provider`
- `docs/GLOSSARY.md#data-dto`
- `docs/GLOSSARY.md#finder`

**Books**:
- Domain-Driven Design (Eric Evans) - Chapter 14: Maintaining Model Integrity
- Implementing Domain-Driven Design (Vaughn Vernon) - Chapter 2: Domains, Subdomains, and Bounded Contexts
