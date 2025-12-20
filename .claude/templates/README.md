# Templates

Centralized code templates for DDD patterns

---

## Template Variables

All templates use these placeholders:

| Variable           | Description               | Example                      |
|--------------------|---------------------------|------------------------------|
| `{BC}`             | Bounded Context           | `Admin`, `Inventory`         |
| `{Entity}`         | Entity name               | `Article`, `Supplier`        |
| `{Action}`         | Action verb (infinitive)  | `Create`, `Rename`, `Delete` |
| `{Name}`           | Contract/Provider name    | `Article`, `ZoneStorage`     |
| `{ProviderBC}`     | BC providing the contract | `Admin`                      |
| `{ConsumerBC}`     | BC consuming the contract | `Inventory`                  |
| `{ControllerName}` | Controller class name     | `GetArticlesController`      |
| `{WorkflowName}`   | E2E workflow name         | `CreateArticle`              |
| `{actionMethod}`   | Method name on entity     | `rename`, `update`           |
| `{param}`          | Parameter name            | `name`, `price`              |

---

## Available Templates

### Domain Layer

| Template               | Usage                           | Skills                 |
|------------------------|---------------------------------|------------------------|
| `entity.php.tpl`       | Immutable entity                | `create-entity`        |
| `value-object.php.tpl` | Immutable Value Object          | `create-value-object`  |
| `repository.php.tpl`   | Repository interface (commands) | `create-repository`    |
| `finder.php.tpl`       | Finder interface (queries)      | `create-repository`    |

### Use Cases

| Template           | Usage           | Skills            |
|--------------------|-----------------|-------------------|
| `use-case.php.tpl` | UseCase class   | `create-use-case` |
| `request.php.tpl`  | UseCase request | `create-use-case` |

### Contracts (Inter-BC)

| Template           | Usage                   | Skills            |
|--------------------|-------------------------|-------------------|
| `contract.php.tpl` | Contract interface      | `add-bc-contract` |
| `provider.php.tpl` | Contract implementation | `add-bc-contract` |

### Tests

| Template                  | Usage                  | Skills                            |
|---------------------------|------------------------|-----------------------------------|
| `test-unit.php.tpl`       | Unit test (UseCase)    | `create-use-case`, `tdd-workflow` |
| `test-functional.php.tpl` | Functional test (HTTP) | `create-functional-test`          |
| `test-e2e.php.tpl`        | E2E test (Panther)     | -                                 |

---

## Usage in Skills

Skills orchestrate these templates. Example:

```markdown
# Skill: create-use-case

## Process

1. Generate from templates:
   - use-case.php.tpl → {BC}/UseCases/{Entity}/{Action}{Entity}/{Action}{Entity}.php
   - request.php.tpl → {BC}/UseCases/{Entity}/{Action}{Entity}/{Action}{Entity}Request.php
   - test-unit.php.tpl → {BC}/Tests/UseCases/{Entity}/{Action}{Entity}/{Action}{Entity}Test.php

2. Replace placeholders
3. Run TDD cycle (skill `tdd-workflow`)
4. Validate (`make qa`)
```

---

## Maintenance

**Critical**: When updating a pattern, update ONLY the template here. All skills will benefit.

**Benefits**:
- Single source of truth
- Consistent code generation
- Easy updates (change template once)