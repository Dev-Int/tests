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
- **Performance acceptable** : Index sur `disabledAt` rend le filtre rapide

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
    updated_at DATETIME NOT NULL,
    INDEX idx_disabled_at (disabled_at) -- Performance pour filtrage
);

-- User table (Auth BC)
CREATE TABLE users (
    uuid VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    disabled_at DATETIME DEFAULT NULL, -- Cascade soft delete
    created_at DATETIME NOT NULL
);
```

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

## References

- **Guide Inter-BC** : `docs/guides/bounded-contexts.md`
- **Architecture** : `docs/architecture.md`
- **RGPD Compliance** : (à ajouter si docs légales)
- **PR** : #256 (CRUD Employee)

## Related ADRs

- ADR-003: Employee-User Coupling (création automatique User)
- ADR-005: Password Reset Workflow (désactivation invalide les tokens)
