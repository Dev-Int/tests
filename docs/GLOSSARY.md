# Glossary

**Purpose**: Single source of truth for all architectural and domain concepts

**Rule**: When ambiguity detected → reference this glossary

---

## Core Architecture Concepts

### Bounded Context (BC)

**Definition**: Autonomous module with its own domain model, isolated from other modules

**Examples**: Admin, Inventory, Shared

**Location**: `src/{BC}/`

**Rules**:
- Each BC has full structure: Entities, UseCases, Adapters, Frameworks, Tests
- BCs communicate via Contracts only (never direct Entity dependencies)

---

### Entity

**Definition**: Mutable domain object with identity (UUID) and behavior

**Characteristics**:
- Has unique identifier (ResourceUuid)
- Has lifecycle (created, modified, deleted)
- Encapsulates business rules and behavior
- Uses Value Objects (not primitives)

**Structure**:
- `final` class
- Private constructor
- Static factory methods
- Behavior methods (not setters)

**Location**: `BC/Entities/`

**Not**: Data structure only (use Value Object instead)

---

### Value Object (VO)

**Definition**: Immutable domain type representing a concept with validation

**Characteristics**:
- No identity (compared by value)
- Immutable (no setters)
- Self-validating
- Reusable across entities

**Structure**:
- `final readonly` class
- Private constructor
- Static factory validates
- Equality method

**Location**:
- `Shared/Entities/VO/` if used across multiple BCs
- `BC/Entities/VO/` if BC-specific only

**Examples**: ResourceUuid, NameField, EmailField, PriceField

---

### UseCase

**Definition**: Business operation encapsulating single use case logic

**Characteristics**:
- Single responsibility (one business operation)
- Orchestrates domain entities
- Uses Repository (commands) or Finder (queries)
- No infrastructure concerns

**Structure**:
- `final readonly` class
- Constructor injection (Repository XOR Finder)
- Single `execute(Request)` method

**Location**: `BC/UseCases/{Entity}/{ActionEntity}/`

**Examples**: CreateArticle, RenameSupplier, FindArticlesByName

---

### Request

**Definition**: Data Transfer Object (DTO) carrying UseCase input

**Characteristics**:
- Pure data structure (no logic)
- Public readonly fields
- No validation (validation in UseCase or Entity)

**Structure**:
- `final readonly` class
- Public fields only
- No methods

**Location**: `BC/UseCases/{Entity}/{ActionEntity}/{ActionEntity}Request.php`

---

### Repository

**Definition**: Interface for command operations (Create, Update, Delete) on entities

**Type**: Port (hexagonal architecture)

**Characteristics**:
- Methods MUST throw exception if entity not found
- Methods: `get*()`, `save()`, `delete()`
- Used by: Use cases that MODIFY state

**Location**: `BC/Entities/Repository/`

**Implementation**: `BC/Adapters/Gateway/ORM/{Entity}ORMRepository.php`

**Methods**:
```php
public function getByUuid(ResourceUuid $uuid): Entity; // throws NotFound
public function create(Entity $entity): void;          // Création
public function rename(Entity $entity): void;          // Action métier spécifique
public function start(Entity $entity): void;           // Action métier spécifique
public function delete(Entity $entity): void;
public function emailExists(EmailField $email): bool;  // Read nécessaire au write (unicité)
```

**Pattern**: Prefer business-named methods (`rename`, `start`, `revaluate`) over generic (`update`).
This document allows transitions and enables optimized persistence per operation.

**Exception** : Les reads *au service* de writes (ex: `emailExists()` pour valider l'unicité avant création) restent dans le Repository. Ce sont des invariants de domaine liés à l'opération d'écriture.
**NOT**: Reads pour affichage/liste → utiliser Finder

---

### Finder

**Definition**: Interface for query operations (Read) on entities

**Type**: Port (hexagonal architecture)

**Characteristics**:
- Methods MAY return null or empty array (no exception)
- Methods: `find*()`, `findAll()`, `findBy*()`
- Used by: Use cases that READ state only

**Location**: `BC/UseCases/Gateway/Finder/`

**Implementation**: `BC/Adapters/Gateway/ORM/{Entity}ORMFinder.php`

**Methods**:
```php
public function find(ResourceUuid|string $uuid): ?Entity;
public function findAll(): array;
public function findByName(string $name): ?Entity;
```

**NOT**: Command operations (use Repository instead)

---

### Repository vs Finder (CQRS)

| Aspect | Repository | Finder |
|--------|-----------|--------|
| **Purpose** | Commands (CUD) | Queries (R) |
| **Not found** | Throws exception | Returns null/[] |
| **Methods** | `get*()`, `create()`, `rename()`, `start()`, `delete()` | `find*()`, `findAll()`, `findBy*()` |
| **Reads pour writes** | `emailExists()`, `codeExists()` | — |
| **Location** | `BC/Entities/Repository/` | `BC/UseCases/Gateway/Finder/` |
| **Used by** | Use cases modifying state | Use cases reading state |

**Rule**: Repository for writes, Finder for reads

---

### Command Gateway

**Definition**: Pattern de communication inter-BC utilisant une interface (Gateway) et un adapter pour appeler les Contracts d'un autre BC

**Type**: Inter-BC communication pattern

**Characteristics**:
- Gateway = Interface dans BC consumer (Admin)
- Adapter = Implémentation qui appelle Contract du BC provider (Auth)
- Découplage: Use case dépend de Gateway (pas directement du Contract)

**Structure**:
```php
// Admin BC: Gateway (interface)
interface UserCreatorGateway {
    public function createUser(CreateUserDTO $dto): CreatedUserDTO;
}

// Admin BC: Adapter (implementation)
#[AsAlias(UserCreatorGateway::class)]
final readonly class UserCreatorAdapter implements UserCreatorGateway {
    public function __construct(
        private CreateUserCommandHandler $userCreator, // Auth BC Contract
    ) {}
}
```

**Location**:
- Gateway: `BC/UseCases/Gateway/`
- Adapter: `BC/Adapters/Gateway/{TargetBC}/`

**Examples**: UserCreatorGateway, UserDisablerGateway

**Reference**: `docs/guides/bounded-contexts.md`, `docs/adr/ADR-008-employee-user-coupling.md`

---

### Employee (Admin BC)

**Definition**: Entité représentant un employé avec profil professionnel et liaison vers User (Auth BC)

**Type**: Entity (Aggregate Root)

**Characteristics**:
- Immutable: firstName, lastName, email, hiredAt, userUuid
- Mutable: phone, position, department
- Soft delete: disabledAt nullable
- Liaison User: userUuid référence le User créé automatiquement

**Lifecycle**:
1. Création Employee → création automatique User (Auth BC)
2. Modification Employee → aucune synchronisation User (champs distincts)
3. Désactivation Employee → désactivation cascade User

**Location**: `src/Admin/Entities/Employee/Employee.php`

**Reference**: `docs/admin-employee-management.md`, `docs/adr/ADR-008-employee-user-coupling.md`

---

### Notification Gateway

**Definition**: Abstraction pour envoi de notifications (email, SMS, push)

**Type**: Gateway (Port hexagonal architecture)

**Characteristics**:
- Interface dans UseCases
- Adapter implémente avec Symfony Mailer
- Permet de mocker l'envoi en tests unitaires

**Structure**:
```php
// Gateway (interface)
interface NotificationGateway {
    public function sendEmail(EmailPayload $payload): void;
}

// Adapter (implementation)
#[AsAlias(NotificationGateway::class)]
final readonly class NotificationProvider implements NotificationGateway {
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
    ) {}
}
```

**Location**:
- Gateway: `BC/UseCases/Gateway/NotificationGateway.php`
- Adapter: `BC/Adapters/Gateway/NotificationProvider.php`

**Usage**: CreateEmployee (envoi email de bienvenue)

**Reference**: `docs/admin-employee-management.md#5-gateways-transversaux`

---

### Soft Delete

**Definition**: Désactivation logique d'une entité via un champ `disabledAt` nullable au lieu d'une suppression physique

**Type**: Design pattern

**Characteristics**:
- Conservation des données (audit, RGPD)
- Réversible (réactivation possible)
- Filtrage nécessaire: queries doivent filtrer `disabledAt IS NULL`

**Pattern**:
```php
final class Entity {
    private ?\DateTimeImmutable $disabledAt = null;

    public function disable(): void {
        if (!$this->isActive()) {
            throw new EntityAlreadyDisabled($this->uuid);
        }
        $this->disabledAt = ClockFactory::clock()->now();
    }

    public function isActive(): bool {
        return !$this->disabledAt instanceof \DateTimeImmutable;
    }
}
```

**Usage**: Employee, User

**Reference**: `docs/adr/ADR-004-employee-soft-delete.md`

**Future Enhancement (HR Compliance):**
- Ajouter `disabledBy` (ResourceUuid) : tracer qui a effectué l'action
- Ajouter `disabledReason` (string) : documenter le motif RH
- Use case : Employee, potentiellement Supplier si modèle RH étendu
- Référence : PR #255 review, point 3

---

### Double Email Validation (Inter-BC Pattern)

**Definition:** Pattern architectural où deux Bounded Contexts valident indépendamment l'unicité d'un email, garantissant la cohérence de chaque domaine tout en évitant les race conditions via une transaction globale.

**Characteristics:**
- Chaque BC maintient sa propre règle d'unicité
- Validation séquentielle : BC consommateur vérifie d'abord, BC fournisseur ensuite
- Transaction atomique cross-BC pour garantir l'atomicité
- Protection contre race conditions sans lock distribué

**Why this pattern?**
- **Autonomie BC** : Admin BC ne doit pas dépendre de Auth BC pour valider sa cohérence
- **DDD correctness** : Chaque domaine reste responsable de ses invariants
- **Transaction atomicity** : Si Auth.User échoue, Admin.Employee rollback automatiquement
- **No distributed lock needed** : Transaction Doctrine gère la cohérence

**Structure:**
```php
// Admin BC - Vérification locale
if ($this->repository->emailExists($email)) {
    throw new EmployeeAlreadyExists($email);  // Cohérence Admin
}

// Auth BC - Vérification via Gateway (peut lever EmailAlreadyExists)
$this->userCreatorGateway->createUser(...);  // Cohérence Auth

// Si exception : rollback automatique via TransactionGateway
```

**Location:**
- `src/Admin/UseCases/Employee/CreateEmployee/CreateEmployee.php:59-70`
- `src/Auth/UseCases/User/CreateUser/CreateUser.php` (validation côté Auth)

**Examples:**
- CreateEmployee → vérifie Admin.Employee + Auth.User
- Future : CreateSupplier → vérifie Admin.Supplier + Auth.User (même pattern)

**Related Concepts:**
- [Transaction Gateway](#transaction-gateway)
- [Command Gateway](#command-gateway)

---

### Transaction Gateway

**Definition**: Abstraction pour gestion des transactions DB (commit/rollback atomique)

**Type**: Gateway (Port hexagonal architecture)

**Characteristics**:
- Interface dans UseCases
- Adapter implémente avec Doctrine EntityManager
- Rollback automatique si exception levée dans l'operation

**Structure**:
```php
// Gateway (interface)
interface TransactionGateway {
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function wrapInTransaction(callable $operation): mixed;
}

// Adapter (implementation)
#[AsAlias(TransactionGateway::class)]
final readonly class DoctrineTransactionAdapter implements TransactionGateway {
    public function wrapInTransaction(callable $operation): mixed {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
```

**Location**:
- Gateway: `BC/UseCases/Gateway/TransactionGateway.php`
- Adapter: `BC/Adapters/Gateway/DoctrineTransactionAdapter.php`

**Usage**: CreateEmployee (rollback si échec User ou Employee)

**Reference**: `docs/admin-employee-management.md#5-gateways-transversaux`

---

### User (Auth BC)

**Definition**: Entité représentant un compte d'accès système avec authentification et autorisation

**Type**: Entity (Aggregate Root)

**Characteristics**:
- Email: Identifiant de connexion (unique)
- Password: Hashé avec Argon2id (HashedPassword VO)
- Roles: Array de `Role` enum (ROLE_USER, ROLE_ADMIN)
- Soft delete: disabledAt nullable
- Normalisation: ROLE_USER toujours présent automatiquement

**Behavior**:
```php
$user->disable();                   // Soft delete
$user->isActive();                  // Vérifie disabledAt
$user->hasRole(Role::ADMIN);        // Vérifie role spécifique
$user->isAdmin();                   // Raccourci hasRole(ROLE_ADMIN)
$user->changePassword($newPassword);// Change password hashé
```

**Location**: `src/Auth/Entities/User.php`

**Reference**: `docs/auth-authentication-authorization.md`, `docs/adr/ADR-008-employee-user-coupling.md`

---

## Inter-BC Communication

### Contract

**Definition**: Interface exposing BC capabilities to other BCs

**Type**: Public API of a BC

**Characteristics**:
- Interface only (no implementation)
- Defines Provider methods
- May include exceptions and DTOs

**Location**: `BC/Contracts/`

**Examples**: ArticleProvider, ZoneStorageProvider

**Consumer**: Other BCs (Adapters layer only)

---

### Provider

**Definition**: Implementation of Contract interface

**Characteristics**:
- Implements Contract interface
- Uses Finder (NOT Repository) for data access
- Converts Entity to Data DTO
- Readonly class

**Location**: `BC/Adapters/Contracts/`

**Naming**: `{BC}{ContractName}` (e.g., AdminArticleProvider)

**Structure**:
```php
final readonly class BC1EntityProvider implements EntityProvider {
    public function __construct(private EntityFinder $finder) {} // Finder, NOT Repository
    public function provide(string $uuid): EntityData {}
}
```

**NOT**: Use Repository (Providers are read-only)

---

### Data (DTO)

**Definition**: Simple data structure for inter-BC data transfer

**Characteristics**:
- Public readonly properties
- No methods, no validation
- Located in Contracts namespace

**Location**: `BC/Contracts/DTO/` or inline in Contract

**Example**:
```php
final readonly class ArticleData {
    public function __construct(
        public string $uuid,
        public string $name,
    ) {}
}
```

---

## Testing Concepts

### Unit Test

**Definition**: Test of business logic in isolation (mocked dependencies)

**Characteristics**:
- No database, no HTTP
- Mock repositories/finders
- Fast (~5ms)

**Base class**: `PHPUnit\Framework\TestCase`

**Setup**: DataBuilder (NOT Foundry)

**Location**: `BC/Tests/UseCases/`

---

### Functional Test

**Definition**: Test of HTTP endpoints with database

**Characteristics**:
- Real HTTP requests
- Database purged before each test
- Foundry for fixtures
- Medium speed (~550ms)

**Base class**: `Shared\Tests\BaseFunctionalTestCase`

**Setup**: Foundry (auto-persist)

**Client**: `$this->client` (NOT `static::createClient()`)

**Location**: `BC/Tests/Adapters/Controller/`

---

### E2E Test

**Definition**: Test of full user workflows in browser

**Characteristics**:
- Real browser (Firefox)
- Database purged before each test
- Foundry for fixtures + `flushAndClearEntityManager()`
- MUST start from `/`
- Slow (~700ms)

**Base class**: `Shared\Tests\BasePantherTestCase`

**Critical rule**: ALWAYS `$client->request('GET', '/')` first

**Location**: `BC/Tests/EndToEnd/`

---

## Development Workflow

### TDD (Test-Driven Development)

**Definition**: Development cycle where tests are written before implementation

**Phases**:
1. **RED**: Write failing test
2. **GREEN**: Minimal implementation (test passes)
3. **REFACTOR**: Clean code (tests still pass)
4. **VALIDATE**: Quality gates (make qa)

**Mandatory**: For all business logic implementation

**Skill**: `.claude/skills/tdd-workflow/`

---

### Skill

**Definition**: Reusable workflow for code generation or process automation

**Characteristics**:
- Step-by-step instructions
- Templates references
- Validation checklist

**Location**: `.claude/skills/{skill-name}/`

**Files**:
- `README.md`: Human documentation
- `workflow.txt`: LLM-optimized instructions
- `metadata.yml`: Machine-readable metadata

---

## Tools & Commands

### make qa

**Definition**: Full quality validation (all gates)

**Gates**: PHPStan, CS-Fixer, PHPCS, Rector, Deptrac, Tests

**When**: Before commit/push

---

### make ta

**Definition**: Run all tests (unit + functional)

**When**: After implementation, before refactor

---

### make cs-fixer

**Definition**: Auto-fix code style (PSR-12)

**When**: Before static analysis, after code changes

---

### make stan

**Definition**: Run PHPStan static analysis (level 9)

**When**: After cs-fixer, before tests

---

### make deptrac

**Definition**: Validate architecture dependencies

**When**: After structural changes (new BC, new dependency)

---

## Deptrac Rules

### Layers

```
Entities  → Shared\Entities only
UseCases  → Entities + Shared\Entities
Adapters  → UseCases + Entities + Shared + OtherBC\Contracts
```

**Rule**: Inner layers NEVER depend on outer layers

### Inter-BC

```
OK: BC1\Adapters → BC2\Contracts (interface only)
NO: BC1 → BC2\Entities
NO: BC1 → BC2\UseCases
```

**Rule**: Only Contracts allowed for inter-BC dependencies

---

## Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Entity | `{Name}` | Article |
| Value Object | `{Name}Field` | NameField |
| UseCase | `{Action}{Entity}` | CreateArticle |
| Request | `{UseCase}Request` | CreateArticleRequest |
| Test | `{UseCase}Test` | CreateArticleTest |
| Repository | `{Entity}Repository` | ArticleRepository |
| Finder | `{Entity}Finder` | ArticleFinder |
| Contract | `{Entity}Provider` | ArticleProvider |
| Provider | `{BC}{Contract}` | AdminArticleProvider |
| Exception | `{Entity}{Reason}` | ArticleNotFound |

---

## Common Confusions Resolved

### Q: Repository vs Finder - which one to use?

**A**: Repository for use cases that MODIFY state (Create, Update, Delete). Finder for use cases that READ state only.

**Mnemonic**: Repository = Write + Reads nécessaires aux writes, Finder = Read pour affichage/liste

**Exception** : Un Repository PEUT contenir des reads si ce sont des invariants liés à l'opération d'écriture (ex: `emailExists()` pour valider l'unicité avant `create()`). Ce n'est PAS du Finder — c'est un prérequis du write.

---

### Q: When to use Entity vs Value Object?

**A**: Entity = has identity (UUID) and lifecycle. Value Object = no identity, compared by value, immutable.

**Examples**:
- Entity: Article (has UUID, can be renamed, deleted)
- Value Object: NameField (just a validated string, no lifecycle)

---

### Q: Shared or BC-specific Value Object?

**A**: Shared if used across multiple BCs (ResourceUuid, EmailField). BC-specific if domain-specific to one BC (ArticleReference, TaxRate).

---

### Q: Can Finder throw exceptions?

**A**: NO. Finder methods return null or empty array. Only Repository methods throw (because `get*` prefix implies exception if not found).

---

### Q: Peut-on mettre un `find*()` dans un Repository ?

**A**: Oui, si ce read est un prérequis d'un write (validation d'unicité, vérification d'état).
Exemple : `emailExists()` avant `create()` = invariant du domaine de création.
NON si c'est pour afficher/lister des données → utiliser Finder.

---

### Q: Can Provider use Repository?

**A**: NO. Provider is read-only, uses Finder only. Providers expose data to other BCs, never modify state.

---

### Q: Where to put inter-BC exceptions?

**A**: Two options:
1. In `BC/Contracts/Exception/` (exception is part of public API)
2. Consumer BC catches and maps to its own exception

**Recommended**: Option 1 (exception in Contract)

---

**Total entries**: 30+ concepts
**Lines**: 350
**Estimated tokens**: ~4000
**Purpose**: Eliminate all ambiguities
