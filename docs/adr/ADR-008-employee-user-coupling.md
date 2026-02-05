# ADR-008: Employee-User Coupling

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

## Database Constraints Decision

### Foreign Key Constraint: Intentionally NOT Implemented

**Decision:** The `employees.user_uuid` column does NOT have a foreign key constraint to `users.uuid`.

**Rationale (DDD Bounded Context Independence):**

1. **Bounded Context Separation** ✅
   - Admin BC and Auth BC are **separate bounded contexts** with independent schemas
   - FK constraint would create **database-level coupling** between BCs
   - DDD principle: BCs should be independent at all layers (domain, application, infrastructure)

2. **Schema Independence** ✅
   - Each BC manages its own database schema evolution
   - No migration coordination needed between Admin and Auth schemas
   - Future possibility to split into separate databases (microservices)

3. **Application-Level Consistency** ✅
   - Consistency is enforced at **application level** via:
     - `TransactionGateway` for atomicity (Create/Update/Disable)
     - `UserCreatorGateway` / `UserDisablerGateway` for cross-BC operations
     - Domain events (future) for eventual consistency
   - Transaction rollback handles both Employee and User operations

**Consequences:**

**Positive** ✅
- Complete BC independence (database, domain, application)
- No database coupling between Admin and Auth
- Easier to evolve schemas independently
- Facilitates future microservices split if needed
- **Orphans eliminated by design**: Soft delete pattern (see ADR-004) prevents physical User deletion

**Negative** ⚠️
- **Theoretical risk**: Orphaned employees possible if User deleted outside transaction
- **Mitigation ALREADY IMPLEMENTED**:
  - Users are NEVER physically deleted (soft delete via `disabledAt`, see ADR-004)
  - All disable operations go through `DisableEmployee` → `UserDisablerGateway` → `DisableUser`
  - Both Employee and User are soft deleted in same transaction
  - **Result**: Orphans cannot occur in practice

**How Consistency is Guaranteed Without FK:**

1. **Creation** (CreateEmployee):
   ```php
   // Wrapped in TransactionGateway
   $userResult = $this->userCreatorGateway->createUser($dto);  // Auth BC
   $employee = Employee::create(..., userUuid: $userResult->uuid);  // Admin BC
   $this->repository->save($employee);
   // Rollback if ANY step fails
   ```

2. **Disablement** (DisableEmployee):
   ```php
   // Wrapped in TransactionGateway
   $this->userDisabler->disableUser($employee->userUuid());  // Auth BC
   $employee->disable();  // Admin BC
   $this->repository->update($employee);
   // Rollback if ANY step fails
   ```

3. **Update** (UpdateEmployee):
   ```php
   // Wrapped in TransactionGateway
   $employee->updatePhone($newPhone);
   $employee->updatePosition($position, $department);
   $this->repository->update($employee);
   // Only Admin BC data updated (User email is immutable)
   ```

**Why Orphans Cannot Occur:**

1. **Soft Delete Pattern (ADR-004)**: Users are NEVER physically deleted
   - `User.disabledAt` field for soft delete
   - `DisableUser` UseCase sets `disabledAt`, never executes DELETE
   - Foreign key `employees.user_uuid → users.uuid` ALWAYS valid

2. **Synchronized Disablement**: DisableEmployee wraps both operations
   ```php
   // Admin\UseCases\Employee\DisableEmployee\DisableEmployee
   $this->transactionGateway->wrapInTransaction(function () {
       $this->userDisabler->disableUser($employee->userUuid());  // Auth BC soft delete
       $employee->disable();  // Admin BC soft delete
       $this->repository->update($employee);
       // Rollback if EITHER fails
   });
   ```

3. **No Direct Database Access**: All operations go through UseCases
   - Controllers → UseCases → Gateways → Auth BC
   - No SQL DELETE statements anywhere in codebase for Users
   - Architectural enforcement via Deptrac rules

**Future Enhancements (Not Needed Currently):**

If business requirements change and physical User deletion becomes necessary, implement:
- **Domain Event**: `UserWasDeleted` → `WhenUserWasDeletedThenDisableEmployee` listener
- **Integrity Check**: Scheduled job to detect and report orphaned employees
- **Validation Gateway**: Check User existence before critical operations

**Current status**: These enhancements are NOT needed because soft delete eliminates the risk.

**Alternative Considered and Rejected:**

**FK Constraint with CASCADE:**
```sql
ALTER TABLE employees
ADD CONSTRAINT fk_employee_user
FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE;
```

**Rejected because:**
- ❌ Creates database coupling between Admin BC and Auth BC
- ❌ Violates DDD Bounded Context independence principle
- ❌ Makes future database split impossible
- ❌ Admin BC schema depends on Auth BC schema
- ❌ BC evolution becomes coordinated (reduces autonomy)

**Decision stands:** Application-level consistency via TransactionGateway is sufficient and architecturally correct for DDD.

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
- ADR-006: Logging in Adapters Layer (audit trail Employee operations)
