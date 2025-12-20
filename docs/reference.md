# Reference - Commands & Checklists

Quick reference for make commands and validation checklists

---

## Prerequisites

**IMPORTANT**: Run commands (except Docker) **inside PHP container**:

```bash
make zsh  # Then: make, bin/console, etc.
```

Service: `test-php-1` (service `php` in compose.yaml)

---

## Symfony Makers (Code Generation)

### Create Bounded Context

```bash
bin/console make:bounded-context:init <name>
```

**Example**:
```bash
bin/console make:bounded-context:init Catalog
```

**Generated**:
- Directory structure: `UseCases/`, `Adapters/`, `Entities/`, `Tests/`
- Configuration: `Frameworks/config/services.yaml`
- Architecture validation: `Frameworks/deptrac.yaml`
- Updates root `deptrac.yaml` with import

**Use case**: Initialize new module/BC in modular monolith

---

### Create Use Case

```bash
bin/console make:use-case:create <bc> <use-case>
```

**Example**:
```bash
bin/console make:use-case:create Admin CreateArticle
```

**Generated**:
- `Admin/UseCases/CreateArticle/CreateArticle.php` (UseCase class)
- `Admin/UseCases/CreateArticle/CreateArticleRequest.php` (Request DTO)
- `Admin/UseCases/CreateArticle/CreateArticleResponse.php` (Response DTO)
- `Admin/UseCases/CreateArticle/CreateArticlePresenter.php` (Presenter interface)
- `Admin/Tests/UseCases/CreateArticle/CreateArticleTest.php` (Unit test)

**Pattern**: Request → UseCase → Response → Presenter (hexagonal architecture port)

**Alternative**: Skills `create-use-case` generates simpler Request → UseCase → Test (no Response/Presenter)

**Location**: `src/Shared/Adapters/Symfony/Maker/`

---

## Docker Commands

```bash
make init        # Initialize project (copy .dist, build, start)
make build       # Build Docker images
make up          # Start containers
make down        # Stop containers
make sh          # Connect to PHP container (bash)
make zsh         # Connect to PHP container (zsh as www-data)
```

---

## Test Commands

```bash
make tu          # Unit tests only
make tf          # Functional tests (resets test DB)
make ta          # Unit + Functional tests (resets test DB)
make e2e         # E2E tests with Panther (resets test DB)
make tc          # All tests with coverage report

# Run specific test file
php bin/phpunit path/to/Test.php
```

---

## Quality Commands

```bash
make qa                 # All quality checks (PHPStan + CS-Fixer + PHPCS + Rector + Deptrac + Lint + Templates)

# Individual tools
make phpcs              # Run PHP_CodeSniffer
make cs-fixer           # Run PHP-CS-Fixer (auto-fixes code style)
make stan               # Run PHPStan analysis
make rector             # Run Rector (dry-run)
make validate-templates # Validate PHP syntax of .claude/templates/*.tpl files

# Alternative commands
composer phpcs
composer php-cs-fixer
composer phpstan
composer rector
bin/console lint:yaml config
bin/console lint:twig templates/
bin/deptrac analyse --config-file=deptrac.yaml
```

---

## Database Commands

```bash
make load-fixtures       # Reset schema and load fixtures (dev)
make reload              # Alias for load-fixtures
make clean-db-test       # Reset test database and run migrations
make schema-validate     # Validate Doctrine schema

# Direct Symfony commands
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
bin/console doctrine:fixtures:load
```

---

## Cache & Assets

```bash
make cc          # Clear cache
make purge       # Remove all var/cache/* and var/logs/*
make assets      # Install assets with symlinks
```

---

## Workflow Checklists

### Before Code Modification

```bash
make cs-fixer stan
```

### After Implementation

```bash
make ta    # Tests (unit + functional)
make e2e   # E2E tests (if applicable)
make qa    # Full validation
```

### Complete Use Case Workflow

```bash
# 1. Generate code (UseCase + Request + Tests)
# 2. Format
make cs-fixer
# 3. Static analysis
make stan
# 4. Run tests
make ta
# 5. Final validation
make qa
```

### TDD Cycle

See skill `tdd-workflow`

```bash
# Red: Write failing test
php bin/phpunit path/to/Test.php  # Should fail

# Green: Implement
php bin/phpunit path/to/Test.php  # Should pass

# Refactor: Clean
make cs-fixer stan ta

# Validate
make qa
```

---

## Quality Gates

### PHPStan

- Level: 9
- Tolerance: 0 errors

### CS-Fixer

- Config: `.php-cs-fixer.php.dist`
- Auto-fix: `make cs-fixer`

### Deptrac

- Validates: Entities ← UseCases ← Adapters
- Config: `{BC}/Frameworks/deptrac.yaml` + root `deptrac.yaml`

### Templates Validation

- Validates: PHP syntax of all `.claude/templates/*.tpl` files
- Script: `bin/validate-templates`
- Process: Replaces placeholders ({BC}, {Entity}, etc.) then runs `php -l`
- Exit code: 0 (success) / 1 (syntax errors)
- Integrated in: `make qa`

### Tests

- DB reset: Auto before functional/E2E
- Coverage: `make tc` generates report

---

## Notes

- **Tests**: DB test automatically reset before functional/E2E tests
- **PHPStan**: Level 9, 0 errors tolerated
- **Deptrac**: Validates DDD architecture
- **CS-Fixer**: Auto-fixes code style per `.php-cs-fixer.php`
