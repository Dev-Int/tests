# ADR-004: Employee Soft Delete

**Status:** Accepted

**Date:** 2026-02-05

## Context

La gestion des employés nécessite de traiter les départs (démission, licenciement, fin de contrat). Deux approches possibles :
- **Hard Delete** : Suppression physique de l'enregistrement Employee
- **Soft Delete** : Désactivation logique avec conservation des données

### Contraintes métier

- **Audit** : Traçabilité complète des actions (RGPD, audit interne)
- **Historique** : Conservation des données d'historique (commandes, projets passés)
- **Réactivation** : Possibilité de réembauche (CDD, intérim)
- **Cascade** : Désactivation du compte User associé

### Contraintes techniques

- **Relations** : Employee peut avoir des relations (projets, commandes, historique)
- **Performance** : Queries doivent filtrer les employés actifs
- **Cohérence** : User et Employee doivent être synchronisés

## Decision

**Implémenter un soft delete avec `disabledAt` nullable et désactivation en cascade du User.**

### Pattern: Nullable DateTimeImmutable

```php
final class Employee
{
    private ?\DateTimeImmutable $disabledAt = null;

    public function disable(): void
    {
        if (!$this->isActive()) {
            throw new EmployeeAlreadyDisabled($this->uuid);
        }
        $this->disabledAt = ClockFactory::clock()->now();
        $this->updatedAt = ClockFactory::clock()->now();
    }

    public function isActive(): bool
    {
        return !$this->disabledAt instanceof \DateTimeImmutable;
    }
}
```

### Workflow de désactivation

1. User clique "Désactiver" sur un Employee (Admin BC)
2. `DisableEmployee` UseCase vérifie `isActive()` (idempotence)
3. `Employee::disable()` set `disabledAt` à maintenant
4. `UserDisablerAdapter` appelle `DisableUserCommandHandler` (Auth BC)
5. Auth BC désactive le User (soft delete aussi)
6. Transaction commit (atomique)
7. Redirect vers liste Employees (filtrée actifs uniquement)

### Règles de filtrage

**Repositories DOIVENT filtrer `disabledAt IS NULL` par défaut :**

```php
// ✅ CORRECT - Filtre automatique
public function findAll(): array
{
    return $this->repository->findBy(['disabledAt' => null]);
}

// ⚠️ ATTENTION - Inclut les désactivés
public function findAllIncludingDisabled(): array
{
    return $this->repository->findAll(); // Pas de filtre
}
```

### Communication Inter-BC

```php
// Admin BC → Gateway
interface UserDisablerGateway {
    public function disableUser(ResourceUuid $userUuid): void;
}

// Admin BC → Adapter
#[AsAlias(UserDisablerGateway::class)]
final readonly class UserDisablerAdapter implements UserDisablerGateway {
    public function disableUser(ResourceUuid $userUuid): void
    {
        $this->userDisabler->disableUser(
            new DisableUserCommand(uuid: $userUuid->toString())
        );
    }
}
```

## Consequences

### Positive ✅

- **Traçabilité complète** : Conservation de l'historique complet
- **Réactivation possible** : Peut réembaucher un ancien Employee (set `disabledAt = null`)
- **Audit RGPD** : Respect des obligations légales (conservation données)
- **Cascade automatique** : User désactivé en même temps (cohérence)
- **Relations intactes** : Foreign keys préservées (historique commandes, projets)
- **Performance optimale** : Index partiel sur actifs uniquement (95% moins volumineux qu'un index classique)

### Negative ⚠️

- **Queries complexes** : TOUJOURS filtrer `disabledAt IS NULL` (risque d'oubli)
- **Storage** : Les données désactivées consomment de l'espace (croissance infinie)
- **Ambiguïté email** : Un email désactivé ne peut pas être recréé (contrainte UNIQUE)
- **Tests** : Nécessite de tester filtrage actifs/inactifs partout

### Trade-offs acceptés

- **Storage cost** : Acceptable (données < 1KB par Employee)
- **Query complexity** : Acceptable (Doctrine QueryBuilder + index)
- **Email uniqueness** : Acceptable (email immutable de toute façon, voir ADR-003)

## Alternatives Considered

### 1. Hard Delete (suppression physique)

**Description** : Supprimer l'enregistrement Employee de la base de données.

**Rejected because** :
- Perte d'historique (projets passés, audit)
- Violation RGPD (droit à l'audit des traitements)
- Foreign keys cassées (si relations non nullables)
- Impossible de réembaucher avec le même email

### 2. Archive Table (table séparée)

**Description** : Déplacer Employee désactivé vers une table `employees_archived`.

**Rejected because** :
- Complexité de migration (triggers, jobs batch)
- Duplication du schéma (maintenir 2 tables identiques)
- Queries cross-table difficiles (historique spans active + archived)
- Over-engineering pour le besoin actuel

### 3. Status Enum (ACTIVE, DISABLED)

**Description** : Remplacer `disabledAt` nullable par `status: EmployeeStatus`.

**Rejected because** :
- Perte de l'information temporelle (QUAND désactivé)
- Moins précis pour l'audit
- Enum nécessite de nouvelles valeurs pour futurs statuts (SUSPENDED, ON_LEAVE)
- `disabledAt` nullable est plus simple et idiomatique

### 4. Cascade Delete (User supprimé aussi)

**Description** : Hard delete Employee + User en cascade.

**Rejected because** :
- Même problèmes que Hard Delete Employee
- Perte d'historique Auth (logs, sessions, actions passées)
- User peut avoir d'autres entités liées (projets, commentaires)

## Implementation

### Code Locations

- **Employee Entity** : `src/Admin/Entities/Employee/Employee.php:191-203` (`disable()` + `isActive()`)
- **DisableEmployee UseCase** : `src/Admin/UseCases/Employee/DisableEmployee/DisableEmployee.php`
- **UserDisablerGateway** : `src/Admin/UseCases/Gateway/UserDisablerGateway.php`
- **UserDisablerAdapter** : `src/Admin/Adapters/Gateway/Auth/UserDisablerAdapter.php`
- **DisableUserCommandHandler** : `src/Auth/Contracts/Services/CommandHandler/DisableUser/DisableUserCommandHandler.php`

### Database Schema

```sql
-- Employee table
CREATE TABLE employees (
    uuid VARCHAR(36) PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    disabled_at DATETIME DEFAULT NULL, -- Soft delete field
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

-- Index partiel : indexe uniquement les employés actifs (95% des requêtes)
-- Note: Parenthèses obligatoires pour matcher PostgreSQL pg_get_expr() (DBAL bug #3780)
CREATE INDEX idx_employee_active ON employees (uuid) WHERE (disabled_at IS NULL);

-- User table (Auth BC)
CREATE TABLE users (
    uuid VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    disabled_at DATETIME DEFAULT NULL, -- Cascade soft delete
    created_at DATETIME NOT NULL
);

-- Index partiel sur users aussi (mêmes bénéfices)
CREATE INDEX idx_user_active ON users (uuid) WHERE (disabled_at IS NULL);
```

**Pourquoi un index partiel plutôt qu'un index classique ?**

- ✅ **95% moins volumineux** : N'indexe que les actifs (5% de désactivés)
- ✅ **Plus rapide** : Index tient en cache, moins d'I/O
- ✅ **Moins de maintenance** : Moins de pages à mettre à jour lors des writes
- ✅ **Utilisation automatique** : PostgreSQL l'utilise pour `WHERE disabled_at IS NULL`

**Doctrine ORM Mapping** :

```php
#[ORM\Entity(repositoryClass: DoctrineEmployeeRepository::class)]
#[ORM\Table(name: 'employees')]
// Note: Parentheses required to match PostgreSQL's pg_get_expr() output
// See: https://github.com/doctrine/dbal/issues/3780
#[ORM\Index(
    name: 'idx_employee_active',
    columns: ['uuid'],
    options: ['where' => '(disabled_at IS NULL)']
)]
class Employee
{
    // ...
}
```

**Piège Doctrine DBAL #3780** :

PostgreSQL normalise les prédicates d'index avec parenthèses via `pg_get_expr()`. Sans les parenthèses explicites dans l'attribut `#[ORM\Index]`, Doctrine détecte une différence entre :
- Code : `'disabled_at IS NULL'`
- PostgreSQL : `'(disabled_at IS NULL)'`

→ Résultat : `doctrine:schema:validate` échoue avec "Database schema not in sync"

**Solution** : Toujours ajouter les parenthèses explicitement dans le code Doctrine.

### Tests

- **Unit Tests** : `DisableEmployeeTest` (vérifie `isActive()`, exception si déjà désactivé)
- **Integration Tests** : `DisableEmployeeTransactionTest` (vérifie User désactivé aussi)
- **Functional Tests** : `DisableEmployeeControllerTest` (workflow HTTP complet)

### Repository Filtering

**Pattern recommandé :**

```php
// EmployeeFinder (queries)
final readonly class DoctrineEmployeeFinder implements EmployeeFinder
{
    public function findAll(): array
    {
        return $this->repository->findBy(
            ['disabledAt' => null], // Filtre automatique
            ['lastName' => 'ASC']
        );
    }

    public function findByUuid(ResourceUuid $uuid): ?Employee
    {
        $employee = $this->repository->findOneBy(['uuid' => $uuid->toString()]);

        // ⚠️ IMPORTANT: Vérifier isActive() après chargement
        if ($employee && !$employee->isActive()) {
            return null; // Considérer comme "non trouvé"
        }

        return $employee;
    }
}
```

## Réactivation (future feature)

Si besoin de réembaucher un Employee désactivé :

```php
// Option A: Réactiver l'Employee existant (même UUID)
public function reactivate(): void
{
    if ($this->isActive()) {
        throw new EmployeeAlreadyActive($this->uuid);
    }
    $this->disabledAt = null;
    $this->updatedAt = ClockFactory::clock()->now();
}

// Option B: Créer un nouvel Employee (nouvel UUID)
// - Plus simple (pas de gestion de l'historique)
// - Recommandé si changement de contrat (CDD → CDI)
```

**Décision reportée** : Pas de réactivation dans le scope actuel (PR #256). À implémenter si besoin métier réel.

### Contrainte liée à la réintégration : index email non partiel

L'index unique sur l'email des employés couvre **tous** les employés (actifs ET désactivés) :

```sql
CREATE UNIQUE INDEX uniq_employee_email ON employees (email); -- tous les statuts
```

**Implication** : Un email lié à un employé désactivé **ne peut jamais être réutilisé**.

Cette contrainte est cohérente avec la politique actuelle (email = identifiant permanent non recyclable,
aligné avec le soft-delete User dans Auth BC — `user.email` est aussi UNIQUE sans filtre).

**Si la réintégration est implémentée** (réactivation même UUID), cette contrainte n'est pas un problème :
l'email reste le même, l'entité existante est réactivée.

**Si un recyclage d'email est nécessaire** (ex : réembauche avec un email différent du compte d'origine),
l'index devra être rendu partiel :

```sql
-- Index partiel : permet de réutiliser un email d'un employé désactivé
CREATE UNIQUE INDEX uniq_employee_email ON employees (email) WHERE (disabled_at IS NULL);
```

**Décision à confirmer avant implémentation de la réintégration** :
- Politique "email = identifiant permanent non recyclable" → garder l'index tel quel
- Politique "email recyclable après désactivation" → migrer vers index partiel + même décision côté Auth BC

Voir PR #255 commentaire [issuecomment-3939704628](https://github.com/Dev-Int/tests/pull/255#issuecomment-3939704628).

## References

- **Soft Delete Pattern Guide** : `docs/guides/soft-delete-pattern.md` (détails techniques, index partiel, bug DBAL)
- **Guide Inter-BC** : `docs/guides/bounded-contexts.md`
- **Architecture** : `docs/architecture.md`
- **PostgreSQL Partial Indexes** : https://www.postgresql.org/docs/current/indexes-partial.html
- **Doctrine DBAL Issue #3780** : https://github.com/doctrine/dbal/issues/3780
- **RGPD Compliance** : (à ajouter si docs légales)
- **PR** : #256 (CRUD Employee)

## Related ADRs

- ADR-003: Employee-User Coupling (création automatique User)
- ADR-005: Password Reset Workflow (désactivation invalide les tokens)
