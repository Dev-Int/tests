# ADR-003: Employee-User Coupling

**Status:** Accepted

**Date:** 2026-02-05

## Context

Le système nécessite de gérer des employés (métier) avec des comptes d'accès au système (technique). Deux préoccupations distinctes émergent :
- **Employee (Admin BC)** : Profil professionnel (nom, poste, département, date d'embauche)
- **User (Auth BC)** : Authentification système (email, password, roles)

### Problématique

Comment coupler ces deux concepts tout en :
- Respectant la séparation des Bounded Contexts (Admin ≠ Auth)
- Garantissant la cohérence (un Employee doit avoir un User)
- Maintenant les règles Deptrac (pas de dépendance directe BC → BC)

### Contraintes

- Architecture modulaire (modular monolith)
- DDD + Clean Architecture
- Deptrac empêche `Admin\Entities` → `Auth\Entities`
- L'email est l'identifiant de connexion (unique et critique)

## Decision

**Un Employee crée automatiquement un User associé (Auth BC) via communication inter-BC.**

### Pattern choisi: Command Gateway

```php
// Admin BC → Gateway (interface)
interface UserCreatorGateway {
    public function createUser(CreateUserDTO $dto): CreatedUserDTO;
}

// Admin BC → Adapter (implémentation)
#[AsAlias(UserCreatorGateway::class)]
final readonly class UserCreatorAdapter implements UserCreatorGateway {
    public function __construct(
        private CreateUserCommandHandler $userCreator, // Auth BC Contract
    ) {}
}

// Auth BC → Contract (interface publique)
interface CreateUserCommandHandler {
    public function createUser(CreateUserCommand $command): CreatedUserResult;
}
```

### Règles d'immutabilité

- **Email** : Immutable après création (identifiant unique et clé de liaison)
- **FirstName, LastName, HiredAt** : Immutables (données d'embauche)
- **Phone, Position, Department** : Mutables (via `UpdateEmployee`)

**Rationale** : L'email est l'identifiant de connexion. Modifier l'email = créer un nouvel utilisateur.

### Workflow de création

1. User remplit formulaire Employee (Admin BC)
2. `CreateEmployee` UseCase valide l'email (unique dans Admin BC)
3. `UserCreatorAdapter` appelle `CreateUserCommandHandler` (Auth BC)
4. Auth BC crée le User avec password temporaire
5. Admin BC crée l'Employee avec `userUuid` de référence
6. Transaction commit (atomique)
7. Email de bienvenue avec lien password reset

## Consequences

### Positive ✅

- **Séparation des préoccupations** : Admin gère le métier, Auth gère l'accès
- **Respect Deptrac** : Communication via Contracts (interfaces)
- **Cohérence garantie** : Transaction atomique (rollback si échec User ou Employee)
- **Traçabilité** : `Employee.userUuid` maintient le lien
- **Sécurité** : Password temporaire + reset obligatoire à la première connexion

### Negative ⚠️

- **Synchronisation nécessaire** : Création Employee nécessite appel inter-BC
- **Complexité transactionnelle** : Rollback doit annuler User ET Employee
- **Couplage indirect** : Admin dépend du succès de Auth (disponibilité)
- **Email immutable** : Changement d'email = nouveau User (complexité métier)

### Trade-offs acceptés

- **Performance** : Appel inter-BC ajoute une latence (acceptable pour création Employee)
- **Distributed Transaction** : Simulé via Doctrine Transaction (suffisant en monolith)
- **Error Handling** : Exception Auth traduite en exception Admin

## Alternatives Considered

### 1. Employee sans User automatique

**Description** : Créer Employee sans User, ajouter User manuellement plus tard.

**Rejected because** :
- Incohérence métier (Employee sans accès système)
- Complexité de gestion (double workflow)
- Risque d'oubli (Employee jamais activé)

### 2. User avec attributs Employee

**Description** : Fusionner Employee et User dans Auth BC.

**Rejected because** :
- Violation SRP (Single Responsibility Principle)
- Auth BC pollué par logique métier Admin
- Pas de séparation des préoccupations
- Couplage fort entre Auth et métier

### 3. Event-Driven Architecture

**Description** : `EmployeeCreated` event → listener crée User asynchrone.

**Rejected because** :
- Complexité excessive pour un monolith
- Gestion d'erreur asynchrone difficile
- Pas de rollback atomique
- Over-engineering pour le besoin actuel

### 4. Shared Kernel (User dans Shared BC)

**Description** : User dans `Shared\Entities`, accessible par tous les BC.

**Rejected because** :
- Violation des règles DDD (Shared = utilities, pas de business logic)
- Couplage fort (tous les BC dépendent de User)
- Perte de l'encapsulation Auth BC

## Implementation

### Code Locations

- **Employee Entity** : `src/Admin/Entities/Employee/Employee.php`
- **CreateEmployee UseCase** : `src/Admin/UseCases/Employee/CreateEmployee/CreateEmployee.php`
- **UserCreatorGateway** : `src/Admin/UseCases/Gateway/UserCreatorGateway.php`
- **UserCreatorAdapter** : `src/Admin/Adapters/Gateway/Auth/UserCreatorAdapter.php`
- **CreateUserCommandHandler** : `src/Auth/Contracts/Services/CommandHandler/CreateUser/CreateUserCommandHandler.php`

### Tests

- **Unit Tests** : `CreateEmployeeTest` (mock UserCreatorGateway)
- **Integration Tests** : `CreateEmployeeTransactionTest` (vérifie rollback)
- **Functional Tests** : `CreateEmployeeControllerTest` (workflow HTTP complet)

### Deptrac Rules

```yaml
# ✅ AUTORISÉ
Admin\UseCases → Admin\UseCases\Gateway (interface)
Admin\Adapters → Auth\Contracts (communication inter-BC)

# ❌ INTERDIT
Admin\Entities → Auth\Entities (pas de dépendance directe)
Admin\UseCases → Auth\UseCases (pas de bypass des Contracts)
```

## References

- **Guide Inter-BC** : `docs/guides/bounded-contexts.md`
- **Architecture** : `docs/architecture.md`
- **Code Review** : `docs/CODE_REVIEW.md` (section Employee Management)
- **PR** : #256 (CRUD Employee)

## Related ADRs

- ADR-004: Employee Soft Delete (gestion désactivation)
- ADR-005: Password Reset Workflow (première connexion)
