# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Internal communication

Use French and unformal language to communicate with the team.

## Project Overview

This is a Symfony 7.4 application developed as a learning project focused on DDD (Domain-Driven Design), testing practices (ATDD, TDD, BDD), and clean architecture. The project domain involves restaurant/inventory management with use cases around configuration, inventory, and order management.

## Architecture

The codebase follows a modular monolith structure with clean architecture principles:

### Module Structure

Each module (e.g., `Admin`, `Shared`) is organized with the following layers:

```
src/
  Admin/                  # Business module
    Entities/            # Domain entities and value objects
    UseCases/            # Application use cases (business logic)
      Gateway/           # Repository interfaces (ports)
    Adapters/            # Implementation details
      Controller/        # Symfony controllers
      Form/             # Symfony form types
      Gateway/          # Repository implementations (Doctrine)
    Frameworks/          # Framework-specific configuration
      config/
      templates/
    Tests/               # Module-specific tests
  Shared/                # Shared domain components
    Entities/            # Shared entities and value objects (VO)
```

### Dependency Rules (Enforced by Deptrac)

- **Entities**: Can only depend on `Shared\Entities`
- **UseCases**: Can depend on `Entities`
- **Adapters**: Can depend on `Entities` and `UseCases`

The inner layers (Entities, UseCases) must not depend on outer layers (Adapters, Frameworks).

### Key Patterns

- **Entities**: Immutable domain objects with factory methods (`create()`) and private constructors
- **Value Objects**: Located in `Shared/Entities/VO/` (e.g., `NameField`, `EmailField`, `ContactAddress`)
- **Use Cases**: Request/Response pattern with dependency injection of Gateway interfaces
- **Repositories**: Interfaces in `UseCases/Gateway/`, implementations in `Adapters/Gateway/ORM/Repository/`
- **Controllers**: Single-action controllers extending `AbstractController`, using `__invoke()` method

## Development Commands

### Setup & Installation

```bash
# With Docker (recommended)
make init        # Initialize project (copies .dist files, builds, starts containers)
make build       # Build Docker images
make up          # Start containers
make down        # Stop containers
make sh          # Connect to PHP container (bash)
make zsh         # Connect to PHP container (zsh as www-data)

# Without Docker
composer install
```

### Running Tests

Test files are organized by type within each module:
- Unit tests: `src/**/Tests/UseCases/` and `src/**/Tests/Entities/`
- Functional tests: `src/**/Tests/Adapters/` and `src/**/Tests/Twig/`
- E2E tests: `src/**/Tests/EndToEnd/`

```bash
make tu          # Run unit tests only
make tf          # Run functional tests (resets test database)
make ta          # Run unit and functional tests (resets test database)
make e2e         # Run E2E tests with Panther (resets test database)
make tc          # Run all tests with coverage report

# Run specific test groups
php bin/phpunit --group=unitTest
php bin/phpunit --group=functionalTest
php bin/phpunit --group=e2eTest

# Run specific test file
php bin/phpunit src/Admin/Tests/UseCases/Tax/CreateTaxTest.php
```

### Database Operations

```bash
make load-fixtures         # Reset schema and load fixtures (dev)
make reload                # Alias for load-fixtures
make clean-db-test         # Reset test database and run migrations
make schema-validate       # Validate Doctrine schema

# Direct Symfony commands
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
bin/console doctrine:fixtures:load
```

### Code Quality

```bash
make qa          # Run all quality checks (cs-fixer, phpcs, stan, rector, deptrac, lint)
make phpcs       # Run PHP_CodeSniffer
make cs-fixer    # Run PHP-CS-Fixer (auto-fixes code style)
make stan        # Run PHPStan analysis
make rector      # Run Rector (dry-run)

# Individual tools
composer phpcs
composer php-cs-fixer
composer phpstan
composer rector
bin/console lint:yaml config
bin/console lint:twig templates/
bin/deptrac analyse --config-file=deptrac.yaml
```

### Cache & Assets

```bash
make cc          # Clear cache
make purge       # Remove all var/cache/* and var/logs/*
make assets      # Install assets with symlinks
```

## Test Configuration

- **PHPUnit config**: `phpunit.xml` (generated from `phpunit.xml.dist`)
- **Test environment**: Uses `APP_ENV=test`
- **Database**: Automatically reset before functional/E2E tests
- **E2E testing**: Uses Symfony Panther with Chrome/Firefox drivers
- **Test helpers**: DataBuilders in `src/**/Tests/DataBuilder/`

**For detailed testing strategy, best practices, and E2E guidelines, see [.claude/TESTING_STRATEGY.md](.claude/TESTING_STRATEGY.md)**

## Autoloading

PSR-4 autoloading with module namespaces:
- `App\` → `src/`
- `Admin\` → `src/Admin/`
- `Shared\` → `src/Shared/`
- `App\Tests\` → `tests/`
- `Admin\Tests\` → `src/Admin/Tests/`
- `Shared\Tests\` → `src/Shared/Tests/`

## Configuration Files

- `.php-cs-fixer.php`: PHP-CS-Fixer configuration
- `phpcs.xml`: PHP_CodeSniffer rules
- `phpstan.neon`: PHPStan configuration
- `deptrac.yaml`: Architecture dependency rules
- `rector.php`: Rector refactoring rules
- `compose.yaml`: Docker Compose configuration

## Important Notes

- **Main branch**: `develop` (not `main` or `master`)
- PHP version: 8.2+
- Symfony version: 7.4.*
- All dist files (`.php-cs-fixer.php.dist`, `phpcs.xml.dist`, `phpunit.xml.dist`) must be copied to their non-dist versions before use (handled by `make init`)
- Tests use DAMA Doctrine Test Bundle for transaction-based test isolation (currently commented out in phpunit.xml)
