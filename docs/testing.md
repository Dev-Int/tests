# Testing Strategy

Test types, isolation strategy, and critical rules

---

## Test Types Overview

| Type       | Base Class               | DB Reset | Speed  | Use                   |
|------------|--------------------------|----------|--------|-----------------------|
| Unit       | `TestCase`               | No       | ~5ms   | Business logic, mocks |
| Functional | `BaseFunctionalTestCase` | Purge    | ~550ms | HTTP, LiveComponents  |
| E2E        | `BasePantherTestCase`    | Purge    | ~700ms | Browser workflows     |

**See**: `docs/GLOSSARY.md#unit-test`, `#functional-test`, `#e2e-test` for detailed definitions

**Decision tree**: `docs/QUICK_REF.md#which-test-type`

---

## Isolation Strategy: LiipTestFixturesBundle

**Why NOT DAMA transactions**:

| Reason                             | Impact                     |
|------------------------------------|----------------------------|
| E2E: Separate web server           | Uncommitted data invisible |
| Functional: Separate DB connection | EntityValueResolver fails  |
| LiveComponents                     | Need committed data        |

**Solution**: `loadFixtures([])` purges DB before each test

**Performance**: ~500-600ms/test (incompressible but reliable)

---

## Critical Rules

### Rule 1: E2E - Start from Root

**CRITICAL**: E2E tests MUST start from `/` (never direct URL navigation)

**Correct**:
```php
$client = self::createPantherClient(['browser' => PantherTestCase::FIREFOX]);
$client->request('GET', '/'); // ALWAYS start from root
$client->clickLink($translator->trans('admin.titlePage')); // Navigate by clicking
```

**Wrong**:
```php
$client->request('GET', '/admin/units'); // ❌ Breaks LiveComponents/Turbo initialization
```

**Reason**: LiveComponents + Turbo Frames require proper initialization from root page

---

### Rule 2: Functional - Use $this->client

**CRITICAL**: Use `$this->client` (NOT `static::createClient()`)

**Correct**:
```php
final class MyFunctionalTest extends BaseFunctionalTestCase {
    public function testSomething(): void {
        $this->client->request('GET', '/route'); // ✅
    }
}
```

**Wrong**:
```php
static::createClient()->request('GET', '/route'); // ❌
```

**See**: Skill `create-functional-test`

---

### Rule 3: Data Setup - Foundry

**Functional tests** (auto-persist + commit):
```php
use Zenstruck\Foundry\Test\Factories;

ArticleFactory::createOne(['name' => 'Test']); // No flush needed
$this->client->request('GET', '/admin/articles');
```

**E2E tests** (MUST flush for Panther visibility):
```php
use Zenstruck\Foundry\Test\Factories;

ArticleFactory::createOne(['name' => 'Test']);
$this->flushAndClearEntityManager(); // CRITICAL for E2E
$client->request('GET', '/');
```

**Unit tests** (use DataBuilder, NOT Foundry):
```php
$entity = EntityDataBuilder::anEntity()->build(); // Mock, no persistence
```

**See**: `docs/GLOSSARY.md#unit-test` for DataBuilder vs Foundry decision

---

## Helper: createMinimalConfiguration()

Helper in `BasePantherTestCase` for complete system config:

```php
// Creates: Company, Unit, Tax, FamilyLog, ZoneStorage, Supplier, Article
$this->createMinimalConfiguration();
$this->flushAndClearEntityManager();
// System configured, ConfigurationService::isConfigured() === true
```

**Usage**: E2E tests requiring configured system state

---

## Test Fixtures: User Accounts

Comptes disponibles après `make reload` ou dans les tests :

| Email | Password | Rôle | Usage |
|-------|----------|------|-------|
| `admin@tests.local` | `password` | ROLE_ADMIN | Accès complet |
| `inventory_manager@tests.local` | `password` | ROLE_INVENTORY_MANAGER | Gestion stocks |
| `user@tests.local` | `password` | ROLE_USER | Utilisateur standard |

**Connexion manuelle** : https://localhost/login

**Dans les tests fonctionnels** :
```php
use Shared\Tests\AuthenticatedFunctionalTestTrait;

$this->loginAsAdmin();           // ou
$this->loginAs('inventory_manager@tests.local');
```

**Dans les tests E2E** :
```php
use Shared\Tests\AuthenticatedPantherTestTrait;

$this->loginAsPanther($client);  // Login via formulaire réel
```

---

## Performance Summary

| Type       | Setup                      | Execution | Total      | Isolation |
|------------|----------------------------|-----------|------------|-----------|
| Unit       | Mocks (~0ms)               | ~5ms      | **~5ms**   | Perfect   |
| Functional | Foundry (~50ms)            | ~500ms    | **~550ms** | DB purge  |
| E2E        | Foundry + Browser (~100ms) | ~600ms    | **~700ms** | DB purge  |

**Note**: Functional/E2E performance incompressible (DB purge + migrations required for isolation)

---

## Validation Checklist

- [ ] Tests pass: `make ta` (or `make e2e`)
- [ ] PHPStan: 0 errors
- [ ] CS-Fixer: formatted
- [ ] E2E: Start from `/`
- [ ] Setup: Foundry (functional/E2E), DataBuilder (unit)
- [ ] E2E: `flushAndClearEntityManager()` after setup

---

## Skills & References

**Skills**: `create-functional-test`, `tdd-workflow`

**Templates**: `test-unit.php.tpl`, `test-functional.php.tpl`, `test-e2e.php.tpl`

**Base classes**:
- `BasePantherTestCase`: `src/Shared/Tests/BasePantherTestCase.php`
- `BaseFunctionalTestCase`: `src/Shared/Tests/BaseFunctionalTestCase.php`

**Libraries**:
- [Symfony Panther](https://github.com/symfony/panther)
- [LiipTestFixturesBundle](https://github.com/liip/LiipTestFixturesBundle)
- [Foundry](https://github.com/zenstruck/foundry)
