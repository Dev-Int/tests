# AI Skills & Workflows

Index of available skills, workflows, and templates for AI-assisted development.

**Documentation**: See [../docs/INDEX.md](../docs/INDEX.md) for complete project documentation

---

## Directory Structure

```
.claude/
├─ README.md          # This file (skills index)
├─ skills/            # AI workflows and skills
│   ├─ tdd-workflow/         # TDD workflow (MANDATORY)
│   ├─ create-use-case/      # Create use case
│   ├─ create-paginated-list/   # Create paginated list
│   ├─ add-bc-contract/      # Add BC contract
│   ├─ create-functional-test/  # Create functional test
│   ├─ create-entity/        # Create domain entity
│   ├─ create-repository/    # Create Repository + Finder
│   └─ create-value-object/  # Create Value Object
├─ templates/         # Code templates
│   └─ *.tpl         # PHP templates (Entity, UseCase, etc.)
├─ project/          # Active project files
│   └─ TODO.md       # Current tasks
└─ archive/          # Archived files
```

---

## Available Skills

### Workflows

#### tdd-workflow (MANDATORY)

**Location**: skills/tdd-workflow/  
**Type**: Workflow  
**Usage**: All implementations (MANDATORY)

**Process**: Red → Green → Refactor → Validate

**When to use**: Every code implementation  
**Prerequisites**: Basic PHP, PHPUnit  
**Outputs**: Test, Request, UseCase

**Reference**: skills/tdd-workflow/SKILL.md

---

#### create-use-case

**Location**: skills/create-use-case/  
**Type**: Generator  
**Usage**: New business use case

**Generates**:
- UseCase class
- UseCase Request
- Unit test

**When to use**: Creating new business logic  
**Prerequisites**: TDD workflow  
**Related**: tdd-workflow

**Reference**: skills/create-use-case/SKILL.md

---

#### add-bc-contract

**Location**: skills/add-bc-contract/  
**Type**: Generator  
**Usage**: Inter-BC communication

**Generates**:
- Contract interface
- Provider implementation
- Service configuration
- Deptrac configuration

**When to use**: Exposing data from one BC to another  
**Prerequisites**: Inter-BC communication understanding  
**Related**: None

**Reference**: skills/add-bc-contract/SKILL.md

---

#### create-functional-test

**Location**: skills/create-functional-test/  
**Type**: Generator  
**Usage**: HTTP/LiveComponent test

**Generates**:
- Functional test class

**When to use**: Testing controllers, HTTP endpoints  
**Prerequisites**: HTTP, Symfony forms  
**Related**: tdd-workflow

**Reference**: skills/create-functional-test/SKILL.md

---

#### create-entity

**Location**: skills/create-entity/  
**Type**: Generator  
**Usage**: New domain entity

**Generates**:
- Immutable entity class with domain logic

**When to use**: Creating new business entity, domain model  
**Prerequisites**: DDD understanding  
**Related**: create-value-object, create-repository

**Reference**: skills/create-entity/SKILL.md

---

#### create-repository

**Location**: skills/create-repository/  
**Type**: Generator  
**Usage**: Entity persistence layer

**Generates**:
- Repository interface (commands)
- Finder interface (queries)

**When to use**: Entity needs persistence, CQRS pattern  
**Prerequisites**: Entity exists  
**Related**: create-entity, create-use-case

**Reference**: skills/create-repository/SKILL.md

---

#### create-value-object

**Location**: skills/create-value-object/
**Type**: Generator
**Usage**: Domain value types

**Generates**:
- Immutable Value Object with validation

**When to use**: Encapsulate primitives, avoid primitive obsession
**Prerequisites**: DDD understanding
**Related**: create-entity

**Reference**: skills/create-value-object/SKILL.md

---

#### create-paginated-list

**Location**: skills/create-paginated-list/
**Type**: Generator
**Usage**: Paginated entity lists

**Generates**:
- Get{Entities}Request interface
- Get{Entities} UseCase
- Get{Entities}Response
- Unit test

**When to use**: Listing entities with pagination (page/itemsPerPage)
**Prerequisites**: Entity + Collection + Repository exist
**Related**: create-use-case, tdd-workflow

**Reference**: skills/create-paginated-list/SKILL.md

---

## Templates

**Location**: templates/

**Available templates**:

### Domain Layer

| Template | Usage | Example Output |
|----------|-------|----------------|
| entity.php.tpl | Immutable entity | BC/Entities/Article.php |
| repository.php.tpl | Repository interface (commands) | BC/Entities/Repository/ArticleRepository.php |
| finder.php.tpl | Finder interface (queries) | BC/UseCases/Gateway/Finder/ArticleFinder.php |

### Use Cases

| Template | Usage | Example Output |
|----------|-------|----------------|
| use-case.php.tpl | UseCase class | BC/UseCases/Article/RenameArticle/RenameArticle.php |
| request.php.tpl | UseCase request | BC/UseCases/Article/RenameArticle/RenameArticleRequest.php |

### Contracts (Inter-BC)

| Template | Usage | Example Output |
|----------|-------|----------------|
| contract.php.tpl | Contract interface | BC/Contracts/ArticleProvider.php |
| provider.php.tpl | Contract implementation | BC/Adapters/Contracts/BCArticleProvider.php |

### Tests

| Template | Usage | Example Output |
|----------|-------|----------------|
| test-unit.php.tpl | Unit test (UseCase) | BC/Tests/UseCases/Article/RenameArticle/RenameArticleTest.php |
| test-functional.php.tpl | Functional test (HTTP) | BC/Tests/Adapters/Controller/.../ControllerTest.php |
| test-e2e.php.tpl | E2E test (Panther) | BC/Tests/EndToEnd/.../WorkflowTest.php |

**Reference**: templates/README.md

---

## Template Variables

All templates use these placeholders:

| Variable | Description | Example |
|----------|-------------|---------|
| {BC} | Bounded Context | Admin, Inventory |
| {Entity} | Entity name | Article, Supplier |
| {Action} | Action verb (infinitive) | Create, Rename, Delete |
| {Name} | Contract/Provider name | Article, ZoneStorage |
| {ProviderBC} | BC providing contract | Admin |
| {ConsumerBC} | BC consuming contract | Inventory |

---

## Skill Structure

Each skill directory contains:

```
skills/SKILL_NAME/
├─ SKILL.md          # Detailed documentation (human-readable)
└─ workflow.txt      # AI-optimized instructions (to be created)
```

**SKILL.md**: Human-readable with examples, rules, process  
**workflow.txt**: Token-optimized for AI parsing (Phase 2)

---

## Usage Patterns

### For AI Assistants

**Workflow**:
1. User requests feature implementation
2. Identify applicable skill from table above
3. Read skill from skills/SKILL_NAME/SKILL.md
4. Follow process step-by-step
5. Use templates from templates/
6. Validate with make qa

**Example**:
```
User: "Create use case to rename article"
→ Use skill: create-use-case
→ Read: skills/create-use-case/SKILL.md
→ Follow: TDD workflow (skills/tdd-workflow/SKILL.md)
→ Templates: test-unit.php.tpl, request.php.tpl, use-case.php.tpl
→ Validate: make qa
```

---

### For Humans

**Browse skills**: Read skills/*/SKILL.md files  
**Use templates**: Copy from templates/ and replace variables  
**Follow workflows**: Read SKILL.md for step-by-step process

---

## Project Management

### Active Tasks

**File**: project/TODO.md

**Content**: Current sprint tasks, priorities

**Format**:
```markdown
## Priority High
- [ ] Task 1

## Priority Medium
- [ ] Task 2
```

---

### Archive

**Location**: archive/

**Content**: Completed tasks, old documentation versions

---

## Skill Development (Future)

### Planned Skills (Phase 2)

| Skill | Purpose | Status |
|-------|---------|--------|
| create-entity | Generate immutable entity | Planned |
| create-repository | Generate Repository + Finder | Planned |
| create-value-object | Generate Value Object | Planned |
| create-exception | Generate domain exception | Planned |
| create-twig-component | Generate TwigComponent | Planned |

---

## Integration with Documentation

**Main docs**: ../docs/  
**Quick reference**: ../CLAUDE.md  
**Complete index**: ../docs/INDEX.md

**Skills reference docs**:
- Architecture: ../docs/architecture.md
- Testing: ../docs/testing.md
- Workflow: ../docs/workflow.md
- Commands: ../docs/reference.md
- BC Guide: ../docs/guides/bounded-contexts.md

---

## Best Practices

### Using Skills

1. **ALWAYS** use tdd-workflow for implementations
2. **READ** skill documentation before using
3. **FOLLOW** process step-by-step
4. **VALIDATE** with make qa after completion
5. **REFERENCE** templates instead of copying code

### Creating Skills

1. **IDENTIFY** repetitive pattern (5+ occurrences)
2. **DOCUMENT** in SKILL.md (human-readable)
3. **CREATE** workflow.txt (AI-optimized) - Phase 2
4. **ADD** to this index
5. **TEST** with real use case

---

## Maintenance

### Updating Skills

**Rule**: Skills are single source of truth for workflows

When updating:
1. Update SKILL.md only
2. Verify templates referenced correctly
3. Test with make qa
4. Update this index if needed

**Don't**:
- Duplicate skill content in main docs
- Create variations of same skill
- Add skills for 1-2 line tasks

---

## References

- Main documentation: ../docs/INDEX.md
- Project overview: ../CLAUDE.md
- Templates: templates/README.md

---

**Last updated**: 2025-12-20
