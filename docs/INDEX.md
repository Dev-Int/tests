# Documentation Index

Complete documentation index for the project.

**Quick start**: Read [CLAUDE.md](../CLAUDE.md) first

---

## Core Documentation

### 1. Architecture

**File**: [architecture.md](architecture.md)

**Content**:
- Bounded Contexts structure
- Clean Architecture patterns
- Dependency rules (Deptrac)
- Entity, UseCase, Adapter patterns
- Repository vs Finder
- Inter-BC communication (Contract pattern)

**When to read**: Understanding project architecture, implementing new features

---

### 2. Testing

**File**: [testing.md](testing.md)

**Content**:
- Test types (Unit, Functional, E2E)
- Isolation strategy (LiipTestFixturesBundle)
- Critical rules (E2E navigation, Foundry setup)
- Test templates
- Performance benchmarks

**When to read**: Writing tests, debugging test issues

---

### 3. Workflow

**File**: [workflow.md](workflow.md)

**Content**:
- Git configuration (main branch: develop)
- GitHub issues conventions (scopes, labels, sub-issues)
- Commit conventions
- Pull request process
- Git safety protocol

**When to read**: Creating issues, commits, or PRs

---

### 4. Commands Reference

**File**: [reference.md](reference.md)

**Content**:
- Docker commands (make zsh, make up/down)
- Test commands (make ta, make e2e)
- Quality commands (make qa, make cs-fixer, make stan)
- Database commands (make reload)
- Workflow checklists

**When to read**: Daily development, running quality checks

---

## Detailed Guides

### Bounded Contexts Communication

**File**: [guides/bounded-contexts.md](guides/bounded-contexts.md)

**Content**:
- Complete inter-BC communication guide
- Pattern 1: Query Contract (Provider)
- Pattern 2: TwigComponent Provider
- Pattern 3: Exception handling
- Configuration (Autowiring, Deptrac)
- Complete examples
- Anti-patterns
- Testing contracts

**When to read**: Implementing inter-BC communication, creating Contracts

---

## AI Skills & Workflows

**Index**: [../.claude/README.md](../.claude/README.md)

**Available skills**:
- tdd-workflow (MANDATORY for all implementations)
- create-use-case (new business use case)
- add-bc-contract (inter-BC communication)
- create-functional-test (HTTP/LiveComponent test)

**Templates**: [../.claude/templates/](../.claude/templates/)

**When to use**: Automating repetitive tasks, following TDD workflow

---

## Quick Maps

### Feature → Documentation

| Feature | Primary Doc | Skill | Template |
|---------|-------------|-------|----------|
| Create Entity | architecture.md#entity | - | entity.php.tpl |
| Create UseCase | architecture.md#usecase | create-use-case | use-case.php.tpl |
| Add BC Contract | guides/bounded-contexts.md | add-bc-contract | contract.php.tpl |
| Write Unit Test | testing.md#unit | tdd-workflow | test-unit.php.tpl |
| Write Functional Test | testing.md#functional | create-functional-test | test-functional.php.tpl |
| Write E2E Test | testing.md#e2e | - | test-e2e.php.tpl |
| Create Repository | architecture.md#repository | - | repository.php.tpl |
| Create Finder | architecture.md#finder | - | finder.php.tpl |

---

### Command → Purpose → Doc

| Command | Purpose | Documentation |
|---------|---------|---------------|
| make qa | Full quality check | reference.md#quality |
| make ta | Run unit + functional tests | testing.md#commands |
| make e2e | Run E2E tests | testing.md#e2e |
| make cs-fixer | Auto-fix code style | reference.md#quality |
| make stan | Run PHPStan level 9 | reference.md#quality |
| make deptrac | Validate architecture | architecture.md#deptrac |
| make reload | Reset DB + fixtures | reference.md#database |
| make zsh | Connect to container | reference.md#prerequisites |

---

### Workflow → Steps → Doc

| Workflow | Steps | Documentation |
|----------|-------|---------------|
| **TDD Cycle** | Red → Green → Refactor → Validate | testing.md#tdd, .claude/skills/tdd/ |
| **Create Use Case** | Test → Request → UseCase → Validate | architecture.md#usecase, .claude/skills/use-case/ |
| **Add BC Contract** | Contract → Provider → Config → Validate | guides/bounded-contexts.md, .claude/skills/bc-contract/ |
| **Create Issue** | Title → Labels → Description | workflow.md#issues |
| **Create Commit** | Stage → Message → Verify | workflow.md#commits |
| **Create PR** | Understand → Draft → Create | workflow.md#pull-requests |

---

## Navigation Guide

### I want to...

**...understand the architecture**
→ Read: architecture.md
→ Then: guides/bounded-contexts.md (for inter-BC)

**...write tests**
→ Read: testing.md
→ Use skill: tdd-workflow (MANDATORY)
→ Templates: .claude/templates/test-*.php.tpl

**...create a use case**
→ Read: architecture.md#usecase
→ Use skill: create-use-case
→ Follow: TDD workflow

**...communicate between BCs**
→ Read: guides/bounded-contexts.md
→ Use skill: add-bc-contract
→ Templates: contract.php.tpl, provider.php.tpl

**...run quality checks**
→ Read: reference.md#quality
→ Run: make qa

**...create a GitHub issue**
→ Read: workflow.md#issues
→ Format: [scope] Description

**...make a commit**
→ Read: workflow.md#commits
→ Follow: Git safety protocol

**...create a PR**
→ Read: workflow.md#pull-requests
→ Analyze: Full branch history

**...find a command**
→ Read: reference.md
→ Or: CLAUDE.md#quick-commands

---

## Learning Path

### Onboarding (New to project)

1. Read CLAUDE.md (quick overview)
2. Read architecture.md (understand structure)
3. Read testing.md (TDD mandatory)
4. Read reference.md (daily commands)
5. Read workflow.md (git/github conventions)

**Time**: ~1-2 hours

---

### Advanced (Inter-BC communication)

1. Read guides/bounded-contexts.md
2. Review architecture.md#inter-bc
3. Practice: Use skill add-bc-contract
4. Validate: make deptrac

**Time**: ~1 hour

---

### Mastery (Contributing)

1. Review all docs/ files
2. Study .claude/skills/ workflows
3. Understand templates system
4. Practice: Create custom skill

**Time**: ~2-3 hours

---

## Project Files

### Active Project Management

- TODO: [../.claude/project/TODO.md](../.claude/project/TODO.md)
- Archive: [../.claude/archive/](../.claude/archive/)

---

## Maintenance

### Updating Documentation

**Rule**: 1 source of truth per concept

When updating:
1. Identify source file (this index)
2. Update source only
3. Verify no duplicates exist
4. Test all links: `make qa`

**Don't**:
- Create duplicate content
- Copy/paste between docs
- Add examples without referencing templates

---

## External Resources

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [DDD - Bounded Context](https://martinfowler.com/bliki/BoundedContext.html)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Symfony Panther](https://github.com/symfony/panther)
- [LiipTestFixturesBundle](https://github.com/liip/LiipTestFixturesBundle)
- [Foundry](https://github.com/zenstruck/foundry)
- [Deptrac](https://github.com/qossmic/deptrac)

---

**Last updated**: 2025-12-20
**Maintained by**: Development team
