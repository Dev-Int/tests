# Documentation Review - Analyse Critique et Propositions

**Date**: 2025-12-20
**Objectif**: Améliorer clarté, réduire tokens, accélérer réponses IA, rendre agnostique LLM

---

## 1. Analyse des Problèmes Actuels

### 1.1 Redondance (Impact: ↓50% tokens potentiel)

| Problème | Fichiers concernés | Impact tokens | Impact clarté |
|----------|-------------------|---------------|---------------|
| **Duplication testing** | `docs/testing.md` + `.claude/TESTING_STRATEGY.md` | ~400 lignes | Confusion source vérité |
| **Duplication commands** | `docs/reference.md` + `.claude/COMMANDS.md` | ~150 lignes | Maintenance double |
| **Duplication workflow** | `docs/workflow.md` + `.claude/GITHUB_ISSUES.md` | ~200 lignes | Incohérences |
| **Bounded Contexts x3** | `docs/architecture.md` + `.claude/BOUNDED_CONTEXTS_QUICK.md` + `.claude/BOUNDED_CONTEXTS_EXAMPLES.md` | ~600 lignes | Navigation complexe |
| **Core patterns répétés** | Dans CLAUDE.md + docs/architecture.md + skills | ~200 lignes | Verbosité |

**Total estimé**: ~1550 lignes dupliquées = ~50% réduction tokens possible

### 1.2 Ambiguïté (Impact: Erreurs IA, lenteur)

**Problème**: Quelle doc fait autorité?

```
Testing:
  - docs/testing.md (anglais, détaillé, 160 lignes)
  - .claude/TESTING_STRATEGY.md (français, détaillé, 275 lignes)
  → Lequel suivre? Différences subtiles créent confusion

Architecture:
  - docs/architecture.md (anglais, patterns)
  - .claude/BOUNDED_CONTEXTS_QUICK.md (français, quick ref)
  - .claude/BOUNDED_CONTEXTS_EXAMPLES.md (français, exemples)
  → 3 niveaux créent surcharge cognitive
```

**Impact**:
- IA doit parser 3x plus de contenu
- Risque incohérences entre versions
- Maintenance x3

### 1.3 Verbosité (Impact: ↓30% tokens)

**Exemples inutilement longs**:

```php
// Répété 8x dans la doc
final class Article {
    private function __construct(
        private ResourceUuid $uuid,
        private NameField $name,
    ) {}

    public static function create(string $name): self {
        return new self(
            ResourceUuid::generate(),
            NameField::fromString($name)
        );
    }
    // ... 20 lignes
}
```

**Solution**: Référencer exemple central, pas répéter

**Emojis partout** (consomment tokens):
```
❌ ✅ 🎯 🔴 🟡 🟢 📝 🏷️ 📋 🐳 🧪 📊 💾 🧹 🔗
→ ~2-3 tokens chacun x100 occurrences = 200-300 tokens
```

**Tableaux surdimensionnés**:
```markdown
| Skill | When to use | Time | Related | Category | Complexity |
→ 6 colonnes dont 3 peu utiles (Time, Category, Complexity)
```

### 1.4 Structure (Impact: Navigation, maintenance)

**Organisation incohérente**:

```
.claude/
├── TODO.md              # Fichier actif projet
├── TESTING_STRATEGY.md  # Doc (doublon docs/)
├── BOUNDED_CONTEXTS_QUICK.md  # Doc
├── BOUNDED_CONTEXTS_EXAMPLES.md  # Doc
├── GITHUB_ISSUES.md     # Doc (doublon docs/)
├── COMMANDS.md          # Doc (doublon docs/)
├── skills/              # Skills IA
├── templates/           # Templates code
└── archive/             # Archive
```

**Problèmes**:
- Mélange doc statique + fichiers actifs (TODO.md)
- Doublons avec `docs/`
- Pas de hiérarchie claire

### 1.5 Optimisation Tokens (Impact: ↓40% tokens)

**CLAUDE.md trop chargé**: 222 lignes dans contexte système

```markdown
# CLAUDE.md actuel
- 222 lignes (dont exemples code complets)
- Chargé dans CHAQUE requête
- Contient: Quick Ref + Patterns + Workflow + Commands + Autoloading
→ ~2000 tokens/requête
```

**Optimal**: 60-80 lignes max, reste externalisé

**Exemples complets répétés**:
- Entity pattern: 8 occurrences complètes
- UseCase pattern: 6 occurrences complètes
- Provider pattern: 5 occurrences complètes

### 1.6 Langage Incohérent (Impact: Clarté)

**Mélange FR/EN sans règle**:

```
docs/ → Anglais
.claude/ → Français
CLAUDE.md → Anglais
Skills → Anglais (frontmatter) + Français (contenu)
```

**Impact**: Confusion, recherche difficile

### 1.7 Agnosticité LLM (Impact: Portabilité)

**Références spécifiques Claude**:

```markdown
# Non portable
"→ Utiliser skill `add-bc-contract`"
"/skill:tdd-workflow"

# Portable
"See workflow: .claude/workflows/tdd-workflow.md"
"Reference: .claude/skills/add-bc-contract/README.md"
```

**Metadata YAML inutilisée**:

```yaml
---
name: TDD Workflow
description: use TDD workflow when create tests
---
```

→ Frontmatter présent mais jamais exploité par IA

---

## 2. Propositions d'Amélioration

### 2.1 Structure Optimale

**Nouvelle organisation**:

```
docs/
├── INDEX.md                    # Point entrée unique, table matières
├── architecture.md             # SOURCE VÉRITÉ architecture
├── testing.md                  # SOURCE VÉRITÉ testing
├── workflow.md                 # SOURCE VÉRITÉ git/github/commits
├── reference.md                # SOURCE VÉRITÉ commandes make
└── guides/                     # Guides détaillés (optionnel)
    ├── bounded-contexts.md     # Guide détaillé BC
    └── contracts-pattern.md    # Guide pattern contrats

.claude/
├── README.md                   # Index skills + workflows
├── skills/
│   ├── tdd/
│   │   ├── README.md          # Skill description
│   │   └── workflow.txt       # Prompt optimisé
│   ├── use-case/
│   │   ├── README.md
│   │   └── workflow.txt
│   ├── bc-contract/
│   │   ├── README.md
│   │   └── workflow.txt
│   └── functional-test/
│       ├── README.md
│       └── workflow.txt
├── templates/
│   ├── README.md              # Index templates
│   └── *.tpl                  # Templates existants
└── project/                   # Fichiers actifs projet
    └── TODO.md                # Moved from .claude/

CLAUDE.md                       # Point entrée IA (60-80 lignes max)
```

**Avantages**:
- 1 source vérité par sujet (docs/)
- Skills isolés, composables
- Séparation doc permanente / fichiers actifs
- Navigation claire

### 2.2 CLAUDE.md Optimisé (↓60% tokens)

**Avant**: 222 lignes, ~2000 tokens
**Après**: 70 lignes, ~800 tokens

```markdown
# Project Context

Symfony 7.4 | PHP 8.2 | DDD + Clean Architecture | Modular Monolith

## Stack

- BCs: Admin, Inventory, Shared
- Branch: develop
- Tests: TDD mandatory

## Structure

```
BC/
├─ Contracts/   # Inter-BC interfaces
├─ Entities/    # Domain (immutable)
├─ UseCases/    # Business logic
├─ Adapters/    # Controllers, Forms, ORM
└─ Tests/       # All test types
```

## Dependency Rules

```
Entities  → Shared\Entities only
UseCases  → Entities + Shared\Entities
Adapters  → UseCases + Entities + Shared + OtherBC\Contracts
```

Rule: Inner layers NEVER depend on outer layers

## Inter-BC Communication

```
OK: BC1\Adapters → BC2\Contracts
NO: BC1 → BC2\Entities
NO: BC1 → BC2\UseCases
```

## Skills

| Skill | Usage | Ref |
|-------|-------|-----|
| tdd-workflow | All implementations (MANDATORY) | .claude/skills/tdd/ |
| create-use-case | New use case | .claude/skills/use-case/ |
| add-bc-contract | Inter-BC communication | .claude/skills/bc-contract/ |
| create-functional-test | HTTP/LiveComponent test | .claude/skills/functional-test/ |

## Docs

- Architecture: docs/architecture.md
- Testing: docs/testing.md
- Workflow: docs/workflow.md
- Commands: docs/reference.md

## Workflow

Before: `make cs-fixer && make stan`
After: `make ta && make qa`

## Autoloading

```
Admin\     → src/Admin/
Inventory\ → src/Inventory/
Shared\    → src/Shared/
```

## Container

Run commands (except Docker) in container: `make zsh`
```

**Réduction**: 222 → 70 lignes (-68%)

### 2.3 Skills Restructurés (↓40% tokens, +clarté)

**Problème actuel**: Frontmatter YAML inutilisé, contenu verbeux

**Nouvelle structure**:

```
.claude/skills/tdd/
├── README.md           # Pour humains (détaillé)
└── workflow.txt        # Pour IA (optimisé tokens)
```

**workflow.txt optimisé** (exemple TDD):

```
TDD Workflow (MANDATORY for all implementations)

Process: Red → Green → Refactor → Validate

1. RED: Write failing test
   - Create test: BC/Tests/UseCases/Entity/ActionEntity/ActionEntityTest.php
   - Template: .claude/templates/test-unit.php.tpl
   - Run: php bin/phpunit path/to/Test.php
   - Expected: FAILURE

2. GREEN: Minimal implementation
   - Create Request: BC/UseCases/Entity/ActionEntity/ActionEntityRequest.php
   - Create UseCase: BC/UseCases/Entity/ActionEntity/ActionEntity.php
   - Templates: .claude/templates/request.php.tpl, use-case.php.tpl
   - Run: php bin/phpunit path/to/Test.php
   - Expected: OK

3. REFACTOR: Clean code
   - make cs-fixer
   - make stan
   - make ta

4. VALIDATE: Full QA
   - make qa

Rules:
- ALWAYS start with test
- Test MUST fail before implementation
- Minimal code in Green phase
- NEVER commit without make qa

Templates: .claude/templates/test-unit.php.tpl, request.php.tpl, use-case.php.tpl
```

**Avantages**:
- Format texte brut → parsing simple
- Pas d'emojis → -30 tokens
- Concis → -60 tokens
- Agnostique LLM

### 2.4 Éliminer Redondance

**Fichiers à supprimer/fusionner**:

```bash
# Supprimer (doublons docs/)
rm .claude/TESTING_STRATEGY.md
rm .claude/COMMANDS.md
rm .claude/GITHUB_ISSUES.md

# Fusionner
.claude/BOUNDED_CONTEXTS_QUICK.md + _EXAMPLES.md → docs/guides/bounded-contexts.md
```

**Sources vérité uniques**:

| Sujet | Source vérité | Suppression |
|-------|---------------|-------------|
| Testing | docs/testing.md | .claude/TESTING_STRATEGY.md |
| Commands | docs/reference.md | .claude/COMMANDS.md |
| Workflow Git | docs/workflow.md | .claude/GITHUB_ISSUES.md |
| Architecture BC | docs/architecture.md + docs/guides/bounded-contexts.md | .claude/BOUNDED_CONTEXTS_*.md |

### 2.5 Langage Unifié

**Convention proposée**:

```
ANGLAIS:
- docs/ (documentation permanente)
- CLAUDE.md
- Skills (.claude/skills/*/workflow.txt)
- Templates (.claude/templates/)

FRANÇAIS:
- Fichiers projet actifs (.claude/project/TODO.md)
- Commits, issues (contexte métier français)

README.md:
- Bilingue si public
- Français si interne
```

**Justification**:
- Anglais = standard tech, portable, concis
- Français = métier projet, communication équipe

### 2.6 Optimisation Tokens Avancée

**Exemples de code**:

```markdown
# Avant (répété 8x dans doc)
```php
final class Article {
    private function __construct(
        private ResourceUuid $uuid,
        private NameField $name,
    ) {}
    // ... 30 lignes
}
```

# Après (référence centralisée)
Entity pattern: see .claude/templates/entity.php.tpl
```

**Gain**: 30 lignes x 8 occurrences = 240 lignes → 1 ligne x 8 = 8 lignes

**Tableaux optimisés**:

```markdown
# Avant
| Skill | When to use | Time | Category | Complexity | Prerequisites |

# Après
| Skill | Usage | Ref |
```

**Suppression emojis**: -200 tokens

**Total gain estimé**: -2500 tokens (~40%)

### 2.7 Agnosticité LLM

**Metadata exploitable** (.claude/skills/*/README.md):

```yaml
---
skill: tdd-workflow
type: workflow
category: development
prerequisites: [php, phpunit]
outputs: [test, request, usecase]
templates: [test-unit.php.tpl, request.php.tpl, use-case.php.tpl]
---
```

**Références portables**:

```markdown
# Non portable
→ Utiliser skill `add-bc-contract`

# Portable
See: .claude/skills/bc-contract/workflow.txt
```

**Format interopérable**: TXT brut, Markdown standard, YAML frontmatter

---

## 3. Skills Identifiés et Conception

### 3.1 Skills Existants (À Refactorer)

| Skill actuel | Problème | Solution |
|--------------|----------|----------|
| tdd-workflow | Verbeux, emojis, FR/EN mixte | Créer workflow.txt optimisé |
| create-use-case | Metadata YAML non exploitée | Ajouter workflow.txt + exploiter metadata |
| add-bc-contract | Exemples trop longs | Créer workflow.txt + référencer templates |
| create-functional-test | Verbeux | workflow.txt optimisé |

### 3.2 Nouveaux Skills Identifiables

**Analyse**: Patterns répétitifs détectés dans commits et code

| Skill proposé | Responsabilité | Inputs | Outputs | Quand utiliser |
|---------------|----------------|--------|---------|----------------|
| **create-entity** | Créer entité immutable DDD | bc, entity, fields | Entity.php, DataBuilder | Nouvelle entité métier |
| **create-repository** | Créer Repository + Finder | bc, entity | Repository.php, Finder.php | Besoin persistence |
| **create-value-object** | Créer Value Object | name, type, validation | VOClass.php | Nouveau VO réutilisable |
| **create-exception** | Créer exception domaine | bc, entity, type | Exception.php | Nouvelle exception métier |
| **create-twig-component** | Créer TwigComponent | bc, component, type | Component.php, .twig | Nouveau composant UI |
| **add-migration** | Créer migration DB | description | MigrationXXX.php | Changement schéma DB |

**Skills non pertinents** (trop simples):
- create-getter (1 ligne)
- add-method (trop variable)
- rename-class (IDE fait mieux)

### 3.3 Template Skill Standardisé

**Structure** (.claude/skills/SKILL_NAME/):

```
README.md           # Documentation détaillée (humains)
workflow.txt        # Instructions IA (optimisé tokens)
examples/           # Exemples concrets (si complexe)
  before.php
  after.php
```

**README.md** (template):

```yaml
---
skill: skill-name
type: [generator|workflow|transformer]
category: [domain|testing|architecture]
prerequisites: []
inputs: [param1, param2]
outputs: [file1, file2]
templates: [template1.tpl]
related_skills: [other-skill]
---

# Skill: skill-name

Description détaillée pour humains.

## Inputs

| Param | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| bc | string | Yes | Bounded context | Admin |

## Outputs

[...]

## Process

[...]

## Rules

[...]

## Examples

[...]
```

**workflow.txt** (template):

```
[SKILL_NAME] - [One-line description]

Inputs: param1, param2
Outputs: file1, file2

Process:

1. [Step 1]
   - Action
   - Expected result

2. [Step 2]
   - Action
   - Expected result

3. Validate
   - make cs-fixer
   - make stan
   - make qa

Rules:
- Rule 1
- Rule 2

Templates: template1.tpl, template2.tpl
Reference: docs/related-doc.md
```

### 3.4 Skills Composables

**Composition example**:

```
create-use-case (skill)
├─ Utilise: tdd-workflow
└─ Génère: UseCase + Request + Test

add-bc-contract (skill)
├─ Utilise: create-use-case (pour tests)
└─ Génère: Contract + Provider + Tests
```

**Avantage**: Réutilisabilité, DRY, maintenance

---

## 4. Alignement Doc ↔ Commandes ↔ Skills

### 4.1 Mapping Actuel

```
Documentation → Commandes → Skills

docs/testing.md → make ta, make e2e → tdd-workflow, create-functional-test
docs/architecture.md → make deptrac → add-bc-contract
docs/workflow.md → git, gh → (aucun skill)
docs/reference.md → make * → (tous skills)
```

### 4.2 Gaps Détectés

**Commandes sans doc**:

```bash
make tc (coverage)
make phpcs (mentionné mais peu documenté)
make rector (mentionné mais peu documenté)
```

**Skills manquants**:

```
create-github-issue (mentionné "à créer Phase 3")
create-commit (pattern existe dans workflow.md mais pas skill)
create-pr (pattern existe mais pas skill)
```

**Doc sans skill ni commande**:

```
BOUNDED_CONTEXTS_EXAMPLES.md → beaucoup de patterns sans automation
```

### 4.3 Proposition Alignement

**Matrice complète**:

| Concept | Doc | Commande | Skill | Action |
|---------|-----|----------|-------|--------|
| TDD Workflow | docs/testing.md | make ta | tdd-workflow | ✅ OK |
| Create UseCase | docs/architecture.md | - | create-use-case | ✅ OK |
| BC Contract | docs/architecture.md | - | add-bc-contract | ✅ OK |
| Functional Test | docs/testing.md | make ta | create-functional-test | ✅ OK |
| Entity Creation | docs/architecture.md | - | - | ➕ Créer skill |
| Repository | docs/architecture.md | - | - | ➕ Créer skill |
| GitHub Issue | docs/workflow.md | gh issue create | - | ➕ Créer skill |
| Commit | docs/workflow.md | git commit | - | 🤔 Optionnel |
| PR | docs/workflow.md | gh pr create | - | 🤔 Optionnel |
| Coverage | - | make tc | - | 📝 Documenter |
| Rector | - | make rector | - | 📝 Documenter |

### 4.4 Index Centralisé

**Créer**: docs/INDEX.md

```markdown
# Documentation Index

## Quick Start

1. [Architecture](architecture.md) - DDD, Clean Arch, BC patterns
2. [Testing](testing.md) - Unit, Functional, E2E strategy
3. [Workflow](workflow.md) - Git, GitHub, commits, PRs
4. [Commands](reference.md) - Make commands reference

## Guides Détaillés

- [Bounded Contexts Communication](guides/bounded-contexts.md)
- [Contract Pattern](guides/contracts-pattern.md)

## Skills IA

See [.claude/README.md](.claude/README.md) for:
- Available skills
- Workflows
- Templates

## Maps

### Feature → Skill

| Feature | Skill | Doc |
|---------|-------|-----|
| Create use case | .claude/skills/use-case/ | docs/architecture.md#use-cases |
| Add BC contract | .claude/skills/bc-contract/ | docs/architecture.md#inter-bc |
| TDD workflow | .claude/skills/tdd/ | docs/testing.md#tdd |
| Functional test | .claude/skills/functional-test/ | docs/testing.md#functional |

### Command → Purpose

| Command | Purpose | Doc |
|---------|---------|-----|
| make qa | Full quality check | docs/reference.md#quality |
| make ta | Run all tests | docs/testing.md#commands |
| make cs-fixer | Auto-fix code style | docs/reference.md#quality |
```

---

## 5. Plan d'Action

### Phase 1: Restructuration (Priorité HAUTE)

**Impact**: ↓50% tokens, +clarté

1. **Créer nouvelle structure**
   ```bash
   mkdir -p docs/guides
   mkdir -p .claude/project
   mkdir -p .claude/skills/{tdd,use-case,bc-contract,functional-test}
   ```

2. **Migrer et fusionner**
   ```bash
   # Fusionner bounded contexts
   cat .claude/BOUNDED_CONTEXTS_QUICK.md .claude/BOUNDED_CONTEXTS_EXAMPLES.md \
     > docs/guides/bounded-contexts.md

   # Déplacer fichiers actifs
   mv .claude/TODO.md .claude/project/

   # Supprimer doublons
   rm .claude/TESTING_STRATEGY.md
   rm .claude/COMMANDS.md
   rm .claude/GITHUB_ISSUES.md
   rm .claude/BOUNDED_CONTEXTS_*.md
   ```

3. **Optimiser CLAUDE.md**
   - Réduire de 222 → 70 lignes
   - Supprimer exemples code complets
   - Garder uniquement structure + références

4. **Créer INDEX.md**
   - Point entrée unique documentation
   - Mapping complet doc/commandes/skills

**Validation**: make qa + test lecture docs

### Phase 2: Skills (Priorité HAUTE)

**Impact**: +réutilisabilité, -répétition

1. **Refactorer skills existants**
   - Créer workflow.txt optimisé pour chaque skill
   - Exploiter metadata YAML
   - Supprimer emojis

2. **Créer nouveaux skills**
   - create-entity
   - create-repository
   - create-value-object

3. **Standardiser structure**
   - Template skill unifié
   - Exemples before/after si besoin

**Validation**: Test génération code avec nouveau format

### Phase 3: Uniformisation Langage (Priorité MOYENNE)

**Impact**: +clarté, -confusion

1. **Traduire en anglais**
   ```bash
   # Traduire
   docs/ → tout en anglais
   .claude/skills/ → tout en anglais
   CLAUDE.md → vérifier anglais
   ```

2. **Garder français**
   ```bash
   .claude/project/TODO.md → français OK (fichier actif)
   ```

**Validation**: Vérification cohérence langue

### Phase 4: Optimisation Tokens (Priorité MOYENNE)

**Impact**: ↓40% tokens total

1. **Centraliser exemples code**
   - 1 exemple par pattern dans templates/
   - Référencer au lieu de répéter

2. **Supprimer emojis**
   - Remplacer par text brut
   - Gain: ~200-300 tokens

3. **Optimiser tableaux**
   - Réduire colonnes inutiles
   - Syntaxe markdown minimale

**Validation**: Mesure tokens avant/après

### Phase 5: Tests et Documentation (Priorité BASSE)

1. **Tester avec différents LLM**
   - Claude
   - GPT-4
   - Gemini
   - Mistral

2. **Documenter changements**
   - CHANGELOG.md
   - Migration guide

**Validation**: Tests utilisateur IA

---

## 6. Métriques de Succès

### Tokens

| Métrique | Avant | Après | Gain |
|----------|-------|-------|------|
| CLAUDE.md | ~2000 | ~800 | -60% |
| Total docs | ~8000 | ~4000 | -50% |
| Skill moyen | ~400 | ~200 | -50% |
| **TOTAL** | **~10000** | **~5000** | **-50%** |

### Clarté

| Métrique | Avant | Après | Amélioration |
|----------|-------|-------|--------------|
| Sources vérité | Multiple par sujet | 1 par sujet | Confusion éliminée |
| Navigation | 3-4 clics | 1-2 clics | +simplicité |
| Langues | FR/EN mixte | Anglais unifié | +cohérence |

### Vitesse IA

| Métrique | Avant | Après | Gain |
|----------|-------|-------|------|
| Parsing initial | 3-5s | 1-2s | -60% |
| Recherche info | 2-3 docs | 1 doc | -66% |
| Réponse complète | 10-15s | 5-8s | -50% |

### Maintenance

| Métrique | Avant | Après | Gain |
|----------|-------|-------|------|
| Update pattern | 3-5 fichiers | 1 fichier | -70% |
| Détection doublons | Manuelle | Impossible | +fiabilité |
| Onboarding IA | Complex | Simple | +rapidité |

---

## 7. Recommandations Finales

### Priorité 1 (URGENT)

1. **Restructurer .claude/** (1-2h)
   - Séparer doc / projet / skills
   - Supprimer doublons

2. **Optimiser CLAUDE.md** (30min)
   - Passer de 222 → 70 lignes
   - Conserver uniquement structure + refs

3. **Créer INDEX.md** (1h)
   - Point entrée unique
   - Mapping complet

### Priorité 2 (IMPORTANT)

4. **Refactorer skills** (2-3h)
   - Créer workflow.txt pour chaque
   - Standardiser structure
   - Supprimer emojis

5. **Fusionner docs BC** (1h)
   - Créer docs/guides/bounded-contexts.md
   - Supprimer .claude/BOUNDED_CONTEXTS_*.md

### Priorité 3 (UTILE)

6. **Uniformiser langue** (2-3h)
   - Tout en anglais sauf .claude/project/

7. **Centraliser exemples** (2h)
   - 1 exemple par pattern
   - Références au lieu répétition

### Ne PAS faire

- ❌ Réécrire toute la doc en une fois
- ❌ Ajouter plus de complexité
- ❌ Créer de nouveaux doublons
- ❌ Ajouter metadata complexe non exploitée

### Règles Maintenance Continue

1. **1 source de vérité** par concept
2. **Pas d'emojis** dans docs techniques
3. **Exemples centralisés** dans templates/
4. **Skills = workflow.txt** + README.md
5. **Anglais** pour doc permanente
6. **Max 80 lignes** pour CLAUDE.md

---

## Annexes

### A. Avant/Après Exemples

**CLAUDE.md**:
```
Avant: 222 lignes, exemples complets, mix concepts
Après: 70 lignes, structure + références, focus essentiel
Gain: -68% tokens, +clarté
```

**Skill tdd-workflow**:
```
Avant: SKILL.md 170 lignes, emojis, FR/EN mixte
Après: workflow.txt 60 lignes, anglais, pas emojis
Gain: -65% tokens
```

**Bounded Contexts**:
```
Avant: 3 fichiers (QUICK, EXAMPLES, docs/architecture.md)
Après: 2 fichiers (docs/architecture.md + guides/bounded-contexts.md)
Gain: -33% fichiers, source vérité claire
```

### B. Templates Optimisés

Voir section 2.2, 2.3, 3.3 pour templates complets.

### C. Checklist Migration

```
Phase 1:
- [ ] Créer structure .claude/skills/*/
- [ ] Créer .claude/project/
- [ ] Créer docs/guides/
- [ ] Fusionner BOUNDED_CONTEXTS
- [ ] Supprimer doublons .claude/
- [ ] Optimiser CLAUDE.md
- [ ] Créer INDEX.md

Phase 2:
- [ ] Refactorer skill tdd-workflow
- [ ] Refactorer skill create-use-case
- [ ] Refactorer skill add-bc-contract
- [ ] Refactorer skill create-functional-test
- [ ] Créer .claude/README.md

Phase 3:
- [ ] Traduire docs/ en anglais
- [ ] Traduire skills/ en anglais
- [ ] Vérifier CLAUDE.md anglais

Phase 4:
- [ ] Centraliser exemples Entity
- [ ] Centraliser exemples UseCase
- [ ] Centraliser exemples Provider
- [ ] Supprimer emojis docs/
- [ ] Supprimer emojis skills/
- [ ] Optimiser tableaux

Tests:
- [ ] make qa passe
- [ ] Navigation INDEX.md OK
- [ ] Tous liens fonctionnent
- [ ] Mesure tokens avant/après
- [ ] Test avec Claude
- [ ] Test avec GPT-4 (optionnel)
```

---

**Fin du rapport**