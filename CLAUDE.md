# Project Context

Symfony 7.4 | PHP 8.2+ | DDD + Clean Architecture | Modular Monolith

---

## Language

Use French and unformal language to communicate with the team. Developer name: Laurent.

---

## Stack

- Architecture: Modular Monolith (Bounded Contexts)
- Modules: Admin, Inventory, Shared
- Main branch: develop
- Tests: TDD mandatory (Red-Green-Refactor)

---

## Structure

```
BC/
├─ Contracts/   # Inter-BC interfaces
├─ Entities/    # Domain (immutable)
├─ UseCases/    # Business logic + Gateways
├─ Adapters/    # Controllers, Forms, ORM
├─ Frameworks/  # Symfony config
└─ Tests/       # All test types
```

---

## Dependency Rules (Deptrac)

```
Entities  → Shared\Entities only
UseCases  → Entities + Shared\Entities
Adapters  → UseCases + Entities + Shared + OtherBC\Contracts
```

Inner layers NEVER depend on outer layers

### Controllers et UseCases

**Les Controllers appellent DIRECTEMENT les UseCases** - c'est intentionnel et correct.

```php
// ✅ CORRECT - Controller dépend directement du UseCase
public function __construct(
    private readonly CreateInventory $useCase,
)

// ✅ CORRECT - UseCase dépend d'interfaces Gateway
public function __construct(
    private readonly InventoryRepository $repository,  // Interface
)
```

**Ne PAS créer d'interfaces pour les UseCases** - ce serait du boilerplate inutile.
Les UseCases sont la frontière applicative, pas des détails d'implémentation.

---

## Inter-BC

```
OK: BC1\Adapters → BC2\Contracts
NO: BC1 → BC2\Entities | BC2\UseCases
```

---

## Quick Reference

**Patterns & Decision Trees**: `docs/QUICK_REF.md` (load first, 80% cases)  
**Definitions**: `docs/GLOSSARY.md` (resolve ambiguities)

Common questions:
- Create Entity/VO/UseCase? → See decision tree in QUICK_REF.md
- Repository vs Finder? → GLOSSARY.md#repository-vs-finder
- Which test type? → See decision tree in QUICK_REF.md

---

## Skills vs Makers

**Skills** (IA workflows) : Processus guidés TDD complets (RED-GREEN-REFACTOR)
**Makers** (Symfony CLI) : Générateurs rapides de fichiers (`bin/console make:...`)

**Quand utiliser** :
- Skills → Process complet avec tests (recommandé pour IA)
- Makers → Génération rapide manuelle (si tu codes toi-même)

| Skill | Usage | Ref |
|-------|-------|-----|
| tdd-workflow | MANDATORY for all | .claude/skills/tdd-workflow/ |
| create-use-case | New use case | .claude/skills/create-use-case/ |
| add-bc-contract | Inter-BC | .claude/skills/add-bc-contract/ |
| create-functional-test | HTTP test | .claude/skills/create-functional-test/ |

Full index: `.claude/README.md`

---

## Documentation

- Quick Ref: `docs/QUICK_REF.md`
- Glossary: `docs/GLOSSARY.md`
- Architecture: `docs/architecture.md`
- Testing: `docs/testing.md`
- Workflow: `docs/workflow.md`
- Commands: `docs/reference.md`

---

## Implementation Plans

Recent implementation plans:
- [Inventory BC Implementation](~/.claude/plans/compressed-gathering-engelbart.md) - Value Objects, settled_at nullable, stocks INTEGER (millièmes)

---

## Workflow

Before: `make cs-fixer && make stan`  
After: `make ta && make qa`

TDD: Use skill tdd-workflow (MANDATORY)

---

## Commands (Quick)

```bash
# Code Generation (Symfony Makers)
bin/console make:bounded-context:init <name>   # Create new BC
bin/console make:use-case:create <bc> <name>   # Create Use Case

# Quality & Tests
make zsh         # Connect to container (run commands here)
make ta          # Tests
make qa          # Quality gates
make cs-fixer    # Auto-fix
make stan        # PHPStan level 9
make reload      # Reset DB + fixtures
```

Full reference: `docs/reference.md` (includes Makers details)

---

## Autoloading (PSR-4)

```
Admin\     → src/Admin/
Inventory\ → src/Inventory/
Shared\    → src/Shared/
```

---

## Outils à privilégier

### MCP PhpStorm (OBLIGATOIRE)

**Toujours utiliser les outils MCP JetBrains** pour les opérations IDE :

| Action | Outil MCP |
|--------|-----------|
| Renommer symbole | `mcp__jetbrains-phpstorm__rename_refactoring` |
| Rechercher dans le code | `mcp__jetbrains-phpstorm__search_in_files_by_text` |
| Erreurs PHPStan/IDE | `mcp__jetbrains-phpstorm__get_file_problems` |
| Trouver fichiers | `mcp__jetbrains-phpstorm__find_files_by_name_keyword` |
| Reformater fichier | `mcp__jetbrains-phpstorm__reformat_file` |

### Commandes PHP (via docker compose)

Exécuter les commandes Make dans le container :

```bash
docker compose exec php make tu          # Tests unitaires
docker compose exec php make tf          # Tests fonctionnels
docker compose exec php make ta          # Tous les tests
docker compose exec php make stan        # PHPStan level 9
docker compose exec php make cs-fixer    # PHP CS Fixer
docker compose exec php make qa          # Quality gates complet
```

**Note** : `make sh` et `make zsh` s'exécutent sur l'hôte (pour entrer dans le container)

**Référence** : Toujours utiliser les targets du Makefile plutôt que les commandes brutes
