# GitHub Issues - Convention et Guidelines

**Dernière mise à jour** : 2025-12-06

---

## Convention de nommage des issues

Toutes les issues doivent suivre ce format pour le titre :

```
[scope] Description courte et claire
```

### Scopes disponibles

#### Modules
- `[Admin]` - Module Admin (configuration, gestion)
- `[Shared]` - Module partagé (Value Objects, entités communes)

#### Entités du domaine
- `[Article]` - Entité Article
- `[Supplier]` - Entité Fournisseur
- `[Tax]` - Entité Taxe
- `[Unit]` - Entité Unité
- `[FamilyLog]` - Entité Famille Logistique
- `[ZoneStorage]` - Entité Zone de Stockage
- `[Company]` - Entité Entreprise

#### Architecture & Technique
- `[DDD]` - Architecture Domain-Driven Design
- `[Tests]` - Tests (Unit, Functional, E2E)
- `[DX]` - Developer Experience (tooling, workflow, CI/CD)
- `[UX]` - User Experience (interface, navigation, ergonomie)
- `[chore]` - Tâches techniques (dépendances, configuration, mise à jour)

### Exemples de titres

✅ **Bon** :
- `[Article] Ajouter validation du stock minimum`
- `[Tests] Créer tests E2E pour modification Supplier`
- `[DDD] Refactorer ArticleRepository en interface du domaine`
- `[chore] Upgrade PHP 8.2 vers 8.3`
- `[UX] Améliorer le feedback visuel lors de la suppression`

❌ **Mauvais** :
- `Ajouter validation` (pas de scope)
- `[Article][Tests] Ajouter tests` (trop de scopes)
- `Fix bug` (pas assez descriptif)

---

## Labels

### Labels de type (catégorie)

| Label | Description | Quand l'utiliser |
|-------|-------------|------------------|
| `backend` | Issue backend | Code PHP, use cases, repositories, entities |
| `frontend` | Issue frontend | Templates Twig, LiveComponents, JavaScript |
| `quality` | Amélioration qualité | Refactoring, amélioration du code, dette technique |
| `use case` | Nouveau cas d'usage | Nouveau use case métier à implémenter |
| `bug` | Bug à corriger | Quelque chose ne fonctionne pas comme prévu |
| `documentation` | Documentation | Ajout/mise à jour de docs (README, CLAUDE.md, etc.) |
| `dependencies` | Dépendances | Mise à jour de composer.json, package.json |
| `DX` | Developer Experience | Outils dev, workflow, scripts make, CI/CD |
| `UX` | User Experience | Interface utilisateur, ergonomie, navigation |

### Labels de statut

| Label | Description |
|-------|-------------|
| `Backlog` | Issue dans le backlog à prioriser |
| `good first issue` | Bon pour débuter (nouveaux contributeurs) |
| `help wanted` | Aide externe souhaitée |
| `wontfix` | Ne sera pas traité |
| `duplicate` | Issue dupliquée |
| `invalid` | Issue invalide |
| `question` | Demande d'information |

### Combinaisons courantes

- `[Article]` + `backend` + `use case` → Nouveau use case pour Article
- `[Tests]` + `quality` → Amélioration de la couverture de tests
- `[DDD]` + `quality` + `backend` → Refactoring architecture
- `[chore]` + `dependencies` → Mise à jour dépendances
- `[Supplier]` + `frontend` + `UX` → Amélioration UI Supplier

---

## Sub-issues (Sous-tâches)

Pour les issues complexes, GitHub permet de créer des **sub-issues** (sous-tâches) qui permettent de :
- ✅ Découper une grosse issue en tâches plus petites
- ✅ Réduire la taille des Pull Requests
- ✅ Suivre la progression globale
- ✅ Faciliter la revue de code

### Comment créer des sub-issues

1. **Créer l'issue parente** avec le scope principal
2. **Dans la description**, ajouter une section "Sub-issues" avec une tasklist :
   ```markdown
   ## Sub-issues
   - [ ] #XX - [scope] Sous-tâche 1
   - [ ] #YY - [scope] Sous-tâche 2
   - [ ] #ZZ - [scope] Sous-tâche 3
   ```
3. **Créer les sub-issues** une par une avec le même scope que le parent
4. **Lier les sub-issues** à l'issue parente (GitHub le fait automatiquement si vous utilisez `#XX` dans la description)

### Exemple concret

**Issue parente #150** : `[Article] Refactorer les repositories en interfaces du domaine`

**Sub-issues** :
- #151 - `[Article] Créer ArticleRepository interface dans UseCases/Gateway`
- #152 - `[Article] Migrer DoctrineArticleRepository vers Adapters/Gateway/ORM`
- #153 - `[Article] Mettre à jour les use cases pour utiliser l'interface`
- #154 - `[Article] Mettre à jour les tests pour utiliser l'interface`

### Avantages

- **PRs plus petites** : 1 sub-issue = 1 PR ciblée
- **Revue plus facile** : Moins de fichiers modifiés par PR
- **Progression visible** : On voit l'avancement global
- **Rollback plus simple** : Si une PR pose problème, on peut revenir en arrière sans tout casser

---

## Templates d'issues

Deux templates sont disponibles dans `.github/ISSUE_TEMPLATE/` :

### 1. Feature / Refactoring (`feature.yml`)

Pour :
- Nouvelles fonctionnalités
- Refactoring
- Améliorations

Inclut :
- Choix du scope (dropdown)
- Priorité (High/Medium/Low)
- Description détaillée
- Critères d'acceptance
- Section pour sub-issues
- Fichiers concernés
- Labels suggérés

### 2. Bug Report (`bug.yml`)

Pour :
- Signalement de bugs
- Problèmes techniques

Inclut :
- Choix du scope
- Description du bug
- Étapes pour reproduire
- Comportement attendu vs actuel
- Logs / Messages d'erreur
- Contexte (environnement, version)

---

## Workflow avec Claude Code

Quand Claude Code crée des issues pour toi, il suit automatiquement ces conventions :

```bash
# Claude crée une issue comme ça :
gh issue create \
  --title "[Tests] Ajouter tests E2E pour modification Article" \
  --body "..." \
  --label "backend,quality,Backlog" \
  --assignee "Dev-Int"
```

**Important** : Toutes les nouvelles issues créées par Claude reçoivent automatiquement le label `Backlog` pour être triées et priorisées ultérieurement.

### Avec sub-issues

Si une issue est complexe, Claude peut :

1. Créer l'issue parente
2. Créer les sub-issues
3. Lier le tout automatiquement

Exemple :
```bash
# Issue parente
PARENT=$(gh issue create --title "[DDD] Refactorer repositories" --body "..." --json number -q .number)

# Sub-issue 1
gh issue create --title "[Tax] Migrer TaxRepository" --body "Parent: #$PARENT"

# Sub-issue 2
gh issue create --title "[Unit] Migrer UnitRepository" --body "Parent: #$PARENT"
```

---

## Bonnes pratiques

### Titre
- ✅ Utiliser toujours un `[scope]`
- ✅ Être concis mais descriptif (max 80 caractères)
- ✅ Utiliser l'infinitif : "Ajouter", "Refactorer", "Corriger"
- ❌ Éviter les titres vagues : "Fix bug", "Update code"

### Description
- ✅ Expliquer le **pourquoi** (contexte)
- ✅ Expliquer le **quoi** (objectif)
- ✅ Proposer le **comment** (solution)
- ✅ Lister les fichiers concernés
- ✅ Ajouter des critères d'acceptance

### Labels
- ✅ Au minimum 1 label de type (`backend`, `frontend`, etc.)
- ✅ Ajouter des labels de statut si pertinent (`help wanted`, etc.)
- ❌ Ne pas sur-labelliser (max 3-4 labels)

### Sub-issues
- ✅ Créer des sub-issues si l'issue principale > 5 fichiers modifiés
- ✅ 1 sub-issue = 1 aspect fonctionnel cohérent
- ✅ Garder le même scope pour parent et enfants
- ✅ Numéroter logiquement (ordre d'exécution)

---

## Exemples complets

### Exemple 1 : Feature simple

```
Titre: [Supplier] Ajouter validation email unique
Labels: backend, quality
Priorité: Medium

Description:
Actuellement, rien n'empêche de créer deux fournisseurs avec le même email.

Objectif: Ajouter une validation pour garantir l'unicité de l'email.

Critères d'acceptance:
- [ ] Validation ajoutée dans SupplierRepository
- [ ] Message d'erreur clair si email déjà utilisé
- [ ] Test unitaire ajouté
- [ ] Test fonctionnel ajouté

Fichiers:
- src/Admin/UseCases/Gateway/SupplierRepository.php
- src/Admin/Adapters/Gateway/ORM/Repository/DoctrineSupplierRepository.php
- src/Admin/Tests/UseCases/Supplier/
```

### Exemple 2 : Feature avec sub-issues

```
Titre: [Article] Ajouter gestion des photos d'articles
Labels: backend, frontend, use case
Priorité: High

Description:
Les articles doivent pouvoir avoir une photo pour faciliter l'identification.

Sub-issues:
- [ ] #160 - [Article] Ajouter champ photo dans Article entity
- [ ] #161 - [Article] Créer use case UploadArticlePhoto
- [ ] #162 - [Article] Ajouter formulaire upload dans CreateArticle
- [ ] #163 - [Article] Ajouter affichage photo dans liste articles
- [ ] #164 - [Article] Ajouter tests E2E upload photo

Objectif: Permettre l'upload et l'affichage d'une photo par article.
```

### Exemple 3 : Bug

```
Titre: [Tax] Calcul incorrect pour TVA à 5.5%
Labels: bug, backend
Priorité: High

Description du bug:
Le calcul de la TVA à 5.5% retourne un résultat incorrect.

Étapes pour reproduire:
1. Créer un article avec prix HT = 100€
2. Appliquer TVA 5.5%
3. Le prix TTC affiché est 106€ au lieu de 105.50€

Comportement attendu: Prix TTC = 105.50€
Comportement actuel: Prix TTC = 106€

Logs:
```
[error] Tax calculation error in Article::calculateTTC()
```

Fichiers:
- src/Admin/Entities/Article/Article.php:calculateTTC()
```

---

## Ressources

- [GitHub Issues Documentation](https://docs.github.com/en/issues)
- [Issue Templates Guide](https://docs.github.com/en/communities/using-templates-to-encourage-useful-issues-and-pull-requests)
- [Sub-issues / Tasklists](https://docs.github.com/en/issues/tracking-your-work-with-issues/about-tasklists)
