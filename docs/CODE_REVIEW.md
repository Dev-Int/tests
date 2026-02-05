# Code Review Guidelines

Ce fichier documente les décisions architecturales validées pour éviter les faux-positifs en review.

## Décisions Architecturales Validées

| Pattern | Statut | Explication |
|---------|--------|-------------|
| Adapters → Adapters | OK | ORM/Gateway peuvent s'utiliser entre elles |
| Controller → UseCase direct | OK | Pas d'interface pour les UseCases |
| Passage `&$entity` | OK | Convention de clarté pour mutations |
| UseCase multi-étapes | OK | Fusion intentionnelle (ex: load + start) |

## Patterns Techniques Validés

| Pattern | Statut | Explication |
|---------|--------|-------------|
| Quantity + bcmath | OK | Millièmes INTEGER pour précision |
| FLOAT PackagingLevel | OK | Ratios packaging, pas des stocks |
| Doctrine 3.x séquences | OK | Auto-gérées par Doctrine |
| UNIQUE INDEX sur FK | OK | Contrainte explicite valide |

## Admin BC - Employee Management (PR #256)

**Decision**: Employee crée automatiquement un User (Auth BC) via communication inter-BC

**Rationale**:
- Séparation des préoccupations : Admin BC gère le métier (Employee), Auth BC gère l'accès (User)
- Respect Deptrac : Pas de dépendance directe `Admin\Entities → Auth\Entities`
- Communication via Contracts : `UserCreatorGateway` → `UserCreatorAdapter` → `CreateUserCommandHandler` (Auth BC Contract)
- Email immutable : Identifiant de connexion fixé à la création (changement email = nouvel Employee)
- Soft delete avec cascade : `disabledAt` nullable, désactivation Employee → désactivation User

**Implementation**:

| Pattern | Description | Code Location |
|---------|-------------|---------------|
| **Command Gateway** | Admin BC appelle Auth BC via Gateway + Adapter | `src/Admin/UseCases/Gateway/UserCreatorGateway.php` |
| **UserCreatorAdapter** | Adapter implémente Gateway, appelle Contract Auth BC | `src/Admin/Adapters/Gateway/Auth/UserCreatorAdapter.php` |
| **UserDisablerAdapter** | Désactivation cascade Employee → User | `src/Admin/Adapters/Gateway/Auth/UserDisablerAdapter.php` |
| **TransactionGateway** | Rollback atomique si échec User ou Employee | `src/Admin/UseCases/Gateway/TransactionGateway.php` |
| **NotificationGateway** | Email de bienvenue avec lien password reset | `src/Admin/UseCases/Gateway/NotificationGateway.php` |
| **PasswordResetGateway** | Token cryptographique avec expiration (1h) | `src/Admin/UseCases/Gateway/PasswordResetGateway.php` |

**Champs Employee**:

| Champ | Mutabilité | Raison |
|-------|-----------|--------|
| `email` | Immutable | Identifiant de connexion User (unique) |
| `firstName` | Immutable | Donnée d'embauche |
| `lastName` | Immutable | Donnée d'embauche |
| `hiredAt` | Immutable | Donnée d'embauche |
| `userUuid` | Immutable | Référence User (liaison) |
| `phone` | Mutable | Info de contact |
| `position` | Mutable | Évolution carrière |
| `department` | Mutable | Changement d'affectation |

**Synchronisation Employee ↔ User**:

| Action | Admin BC (Employee) | Auth BC (User) | Communication |
|--------|---------------------|----------------|---------------|
| **Création** | CreateEmployee | CreateUser | UserCreatorAdapter → CreateUserCommandHandler |
| **Modification** | UpdateEmployee (phone, position, department) | Aucune | Pas de synchronisation (champs distincts) |
| **Désactivation** | DisableEmployee (disabledAt) | DisableUser (disabledAt) | UserDisablerAdapter → DisableUserCommandHandler |

**Tests**:

| Type | Scope | Example |
|------|-------|---------|
| **Unit** | UseCase avec mocks | `CreateEmployeeTest` (mock UserCreatorGateway) |
| **Integration** | Transaction rollback | `CreateEmployeeTransactionTest` (vérifie rollback Employee + User) |
| **Functional** | Workflow HTTP complet | `CreateEmployeeControllerTest` (form + POST + redirect) |

**Status**: ✅ Merged (PR #256)

**See Also**:
- `docs/admin-employee-management.md` (guide complet)
- `docs/adr/ADR-008-employee-user-coupling.md` (décision Employee-User)
- `docs/adr/ADR-004-employee-soft-delete.md` (décision soft delete)
- `docs/adr/ADR-005-password-reset-workflow.md` (décision password reset)
- `docs/auth-authentication-authorization.md` (guide Auth BC)

---

## Checklist Avant Signalement

- [ ] Ce n'est pas un pattern validé ci-dessus
- [ ] Le test n'existe pas déjà (*Test.php, DataProvider)
- [ ] Vérifié les règles Deptrac du projet
- [ ] Lu le code réel, pas juste le diff
