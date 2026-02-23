# ADR-006: Logging in Adapters Layer

**Status:** Accepted

**Date:** 2026-02-12

## Context

Le système nécessite un audit trail des opérations métier pour :
1. **Traçabilité** : Savoir qui a effectué quelle action et quand
2. **Débogage** : Faciliter la résolution de problèmes en production
3. **Sécurité** : Détecter les activités suspectes ou non autorisées
4. **Conformité** : Répondre aux exigences de conformité (RGPD, audit)

### Problème identifié

**Implémentation initiale (REJETÉE) :**
- LoggerInterface injecté dans les UseCases (couche métier)
- Logs émis dans les UseCases après succès des opérations

**Violations constatées :**
```php
// ❌ INCORRECT - UseCase dépend de PSR-3 (infrastructure)
final readonly class CreateEmployee
{
    public function __construct(
        private EmployeeRepository $repository,
        private LoggerInterface $logger,  // ← Violation Clean Architecture
    ) {}

    private function createNewEmployee(CreateEmployeeRequest $request): \Closure
    {
        return function () use ($request): CreateEmployeeResponse {
            // ... logique métier ...

            $this->logger->info('Employee created', [...]); // ← Couplage infrastructure

            return new CreateEmployeeResponse($employee);
        };
    }
}
```

**Problèmes architecturaux :**
1. **Violation Clean Architecture** : UseCase (couche métier) dépend de LoggerInterface (infrastructure PSR-3)
2. **Violation Dependency Rule** : Couche interne (UseCases) dépend de couche externe (infrastructure)
3. **Contexte HTTP manquant** : Impossible d'enrichir les logs avec user_id, IP, user_agent (disponibles uniquement dans Controller)
4. **Tests complexes** : Nécessite mock LoggerInterface dans tous les tests UseCases
5. **Inconsistance** : Admin BC a logs, Auth BC n'en a pas (duplication partielle)

### Contraintes métier

- **Contexte riche** : Logs doivent contenir user_id, IP, user_agent pour audit
- **Traçabilité complète** : Toutes les opérations CRUD doivent être loggées
- **Performance** : Logging ne doit pas impacter les performances (async si besoin)

### Contraintes techniques

- **Clean Architecture** : Respect de la Dependency Rule (inner layers indépendantes)
- **Testabilité** : UseCases doivent être testables sans infrastructure
- **Maintenabilité** : Pas de duplication excessive du code de logging

## Decision

**Déplacer les logs de la couche UseCases vers la couche Adapters (Controllers).**

### Pattern: Logging in Controllers

```php
// ✅ CORRECT - Controller (Adapters) gère le logging
final class CreateEmployeeController extends AbstractController
{
    public function __construct(
        private readonly CreateEmployee $useCase,
        private readonly LoggerInterface $logger,  // ← Logging dans Adapter
    ) {}

    public function __invoke(Request $request): Response
    {
        try {
            $response = $this->useCase->execute($apiRequest);

            // ✅ Logging APRÈS succès UseCase, avec contexte HTTP riche
            $this->logger->info('Employee created successfully', [
                'employee_uuid' => $response->employee->uuid()->toString(),
                'user_uuid' => $response->employee->userUuid()->toString(),
                'email' => $response->employee->contactInformation()->email()->toString(),
                'created_by_user_id' => $this->getUser()?->getUserIdentifier(),  // ← Contexte HTTP
                'ip_address' => $request->getClientIp(),                          // ← Contexte HTTP
                'user_agent' => $request->headers->get('User-Agent'),             // ← Contexte HTTP
            ]);

            $this->addFlash('success', 'Employee created successfully');
            return $this->redirectToRoute('admin_employees_index');
        } catch (EmployeeAlreadyExists $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_employees_index');
        }
    }
}
```

```php
// ✅ CORRECT - UseCase pur (pas de dépendance infrastructure)
final readonly class CreateEmployee
{
    public function __construct(
        private EmployeeRepository $repository,
        private UserCreatorGateway $userCreatorGateway,
        private TransactionGateway $transactionGateway,
        // PAS de LoggerInterface  ← UseCase pur
    ) {}

    private function createNewEmployee(CreateEmployeeRequest $request): \Closure
    {
        return function () use ($request): CreateEmployeeResponse {
            // ... pure business logic ...
            // PAS de logging ici

            return new CreateEmployeeResponse($employee);
        };
    }
}
```

### Implémentation Admin BC

**Controllers modifiés (3 fichiers) :**
- `CreateEmployeeController.php` - Logging avec contexte HTTP après succès
- `UpdateEmployeeController.php` - Logging avec contexte HTTP après succès
- `DisableEmployeeController.php` - Logging avec contexte HTTP après succès

**UseCases nettoyés (3 fichiers) :**
- `CreateEmployee.php` - Retiré LoggerInterface
- `UpdateEmployee.php` - Retiré LoggerInterface
- `DisableEmployee.php` - Retiré LoggerInterface

**Tests simplifiés (5 fichiers) :**
- Retrait de tous les mocks LoggerInterface
- Tests UseCases ~30% plus simples (moins de setup)

## Consequences

### Positive ✅

1. **Respect Clean Architecture** : UseCases restent purs business logic, pas de dépendance infrastructure
2. **Contexte riche** : Accès à user_id, IP, user_agent, request headers (audit trail complet)
3. **Flexibilité** : Chaque adapter peut logger différemment (HTTP verbose, CLI silencieux, Queue minimal)
4. **Testabilité UseCases** : Pas besoin de mocker le logger, tests plus simples
5. **Separation of Concerns** : Logging est une préoccupation d'infrastructure, pas de domaine
6. **Performance** : Logging après transaction (pas de overhead dans transaction)

### Negative ⚠️

1. **Duplication** : Chaque adapter doit penser à logger (HTTP Controller, CLI Command, Queue Handler)
2. **Risque d'oubli** : Facile d'oublier de logger dans un nouvel adapter
3. **Pas atomique** : Log émis APRÈS le UseCase (peut échouer même si UseCase réussit)
4. **Pas de garantie** : Si UseCase appelé depuis un test ou un script custom, pas de logs
5. **Maintenance** : Code de logging répété dans plusieurs fichiers (3 controllers actuellement)

### Trade-offs acceptés

- **Duplication vs Clean Architecture** : Duplication acceptable (3 controllers) pour respecter la Dependency Rule
- **Logs potentiellement manquants** : Acceptable car les adapters principaux (HTTP) sont couverts
- **Non-atomicité** : Acceptable car logging n'est pas critique (échec log ≠ échec métier)

## Alternatives Considered

### 1. Garder LoggerInterface dans UseCases (REJETÉE)

**Description** : Conserver l'implémentation actuelle avec logger dans UseCases.

**Rejected because** :
- **Violation Clean Architecture** : UseCase (couche métier) dépend de LoggerInterface (infrastructure PSR-3)
- **Couplage infrastructure** : UseCases ne sont plus "purs" business logic
- **Contexte HTTP manquant** : Pas d'accès à user_id, IP, user agent
- **Testabilité réduite** : Tests UseCases doivent mocker le logger (bruit)

### 2. Event-Driven Logging (Domain Events)

**Description** : Émettre des Domain Events (EmployeeCreated) et logger via Event Listeners.

**Rejected because** :
- **Over-engineering** : Complexité excessive pour le besoin actuel
- **Contexte HTTP manquant** : Events ne contiennent pas Request/Security context
- **Synchronisation** : Event handlers asynchrones = logs différés (problème audit)
- **Debugging complexe** : Chaîne d'exécution indirecte (UseCase → Event → Listener)

**Pourrait être reconsidéré si** :
- Besoin de logging multi-destination (DB + File + Syslog)
- Besoin de logging asynchrone pour performance
- Besoin de réutiliser events pour d'autres use cases

### 3. Decorator Pattern (LoggingDecorator)

**Description** : Wrapper les UseCases avec un LoggingDecorator.

```php
final class LoggingCreateEmployeeDecorator implements CreateEmployeeInterface
{
    public function __construct(
        private CreateEmployee $decorated,
        private LoggerInterface $logger,
    ) {}

    public function execute(CreateEmployeeRequest $request): CreateEmployeeResponse
    {
        $response = $this->decorated->execute($request);
        $this->logger->info('Employee created', [...]);
        return $response;
    }
}
```

**Rejected because** :
- **Contexte HTTP manquant** : Decorator n'a pas accès à Request/Security
- **Boilerplate** : Nécessite interface + decorator pour chaque UseCase
- **Indirect** : Logging séparé de la logique controller (moins lisible)
- **Maintenance** : 2 fichiers à maintenir par UseCase (decorator + usecase)

### 4. Middleware HTTP

**Description** : Logger les requêtes HTTP via un Symfony EventSubscriber (kernel.response).

**Rejected because** :
- **Contexte métier manquant** : Middleware ne connaît pas employee_uuid, user_uuid
- **Seulement HTTP** : Ne couvre pas CLI Commands, Queue Handlers
- **Moins précis** : Log HTTP générique, pas spécifique aux opérations métier

## Implementation

### Code Locations

**Controllers (Adapters) :**
- `src/Admin/Adapters/Controller/Symfony/Controller/Employee/CreateEmployee/CreateEmployeeController.php`
- `src/Admin/Adapters/Controller/Symfony/Controller/Employee/UpdateEmployee/UpdateEmployeeController.php`
- `src/Admin/Adapters/Controller/Symfony/Controller/Employee/DisableEmployee/DisableEmployeeController.php`

**UseCases (purs) :**
- `src/Admin/UseCases/Employee/CreateEmployee/CreateEmployee.php`
- `src/Admin/UseCases/Employee/UpdateEmployee/UpdateEmployee.php`
- `src/Admin/UseCases/Employee/DisableEmployee/DisableEmployee.php`

**Tests :**
- `src/Admin/Tests/UseCases/Employee/CreateEmployee/CreateEmployeeTest.php` (simplifié)
- `src/Admin/Tests/UseCases/Employee/UpdateEmployee/UpdateEmployeeTest.php` (simplifié)
- `src/Admin/Tests/UseCases/Employee/DisableEmployee/DisableEmployeeTest.php` (simplifié)

### Logging Configuration

**Monolog Configuration (exemple) :**
```yaml
# config/packages/prod/monolog.yaml
monolog:
    handlers:
        # Logs métier (audit trail)
        business:
            type: stream
            path: "%kernel.logs_dir%/business.log"
            level: info
            channels: ["!event", "!doctrine"]

        # Logs applicatifs (errors)
        main:
            type: fingers_crossed
            action_level: error
            handler: nested
```

### Pattern de logging recommandé

**Template standard pour Controllers :**
```php
try {
    $response = $this->useCase->execute($request);

    $this->logger->info('[Operation] succeeded', [
        'entity_uuid' => $response->entity()->uuid()->toString(),
        // ... autres champs métier ...
        'performed_by_user_id' => $this->getUser()?->getUserIdentifier(),
        'ip_address' => $request->getClientIp(),
        'user_agent' => $request->headers->get('User-Agent'),
    ]);

    $this->addFlash('success', '...');
    return $this->redirectToRoute('...');
} catch (DomainException $e) {
    // Pas de log ici (exception déjà loggée par Monolog)
    $this->addFlash('error', $e->getMessage());
    return $this->redirectToRoute('...');
}
```

### Tests

**Validation complète :**
- ✅ 417 tests unitaires (Admin BC) - PASS
- ✅ 4 tests d'intégration Employee - PASS
- ✅ PHPStan level 9 - PASS
- ✅ PHP CS Fixer - PASS

**Tests simplifiés (exemples) :**
```php
// AVANT (avec mock logger)
$logger = $this->createMock(LoggerInterface::class);
$useCase = new CreateEmployee($repository, $gateway, $logger);

// APRÈS (pas de logger)
$useCase = new CreateEmployee($repository, $gateway);
```

## Améliorations futures

### 1. Audit Trail Entity persistante

Pour traçabilité complète, stocker les logs métier en base de données :

```php
// Entité AuditLog (Admin ou Shared BC)
final class AuditLog
{
    public static function create(
        string $action,           // 'employee.created'
        ResourceUuid $entityUuid, // UUID de l'entity modifiée
        array $context,           // Données supplémentaires
        ?ResourceUuid $userId,    // User qui a effectué l'action
        ?string $ipAddress,       // IP du client
    ): self;
}

// Dans Controller
$this->auditTrail->log(
    action: 'employee.created',
    entityUuid: $response->employee->uuid(),
    context: ['email' => $email->toString()],
    userId: $this->getUser()?->getId(),
    ipAddress: $request->getClientIp(),
);
```

**Avantages :**
- Requêtable via SQL (filtres, recherches, exports)
- Immutable (pas de rotation comme les logs fichiers)
- Intégrable dans l'interface admin (historique des actions)

### 2. Structured Logging (JSON)

Utiliser Monolog avec formateur JSON pour faciliter l'analyse :

```yaml
monolog:
    handlers:
        business:
            type: stream
            path: "%kernel.logs_dir%/business.json"
            level: info
            formatter: 'monolog.formatter.json'
```

**Avantages :**
- Facilite parsing par outils (ELK, Datadog, CloudWatch)
- Requêtes structurées (filtrer par user_id, IP, etc.)

### 3. Logging Trait

Réduire duplication avec un trait réutilisable :

```php
trait LogsBusinessOperations
{
    abstract protected function getLogger(): LoggerInterface;

    protected function logSuccess(
        string $message,
        array $context,
        Request $request,
    ): void {
        $this->getLogger()->info($message, [
            ...$context,
            'performed_by_user_id' => $this->getUser()?->getUserIdentifier(),
            'ip_address' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
        ]);
    }
}

// Usage dans Controller
class CreateEmployeeController extends AbstractController
{
    use LogsBusinessOperations;

    public function __invoke(Request $request): Response
    {
        $response = $this->useCase->execute($apiRequest);

        $this->logSuccess('Employee created successfully', [
            'employee_uuid' => $response->employee->uuid()->toString(),
        ], $request);
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
```

### 4. Event Listeners pour CLI/Queue

Pour les adapters non-HTTP (CLI Commands, Queue Handlers), utiliser un pattern similaire :

```php
// CLI Command
protected function execute(InputInterface $input, OutputInterface $output): int
{
    $response = $this->useCase->execute($request);

    $this->logger->info('Employee created via CLI', [
        'employee_uuid' => $response->employee->uuid()->toString(),
        'command' => $input->getArgument('command'),
        'executed_by' => get_current_user(), // User système
    ]);

    return Command::SUCCESS;
}
```

## References

- **Clean Architecture** : Robert C. Martin - "The Clean Architecture" (2012)
- **OWASP Logging Cheat Sheet** : https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html
- **Symfony Logging** : https://symfony.com/doc/current/logging.html
- **Monolog** : https://github.com/Seldaek/monolog

## Related ADRs

- ADR-003: Employee-User Coupling (contexte métier pour logs Employee)
- ADR-005: Password Reset Workflow (logging des tentatives de reset)

## Notes

Cette décision a été prise après analyse des violations Clean Architecture constatées dans Admin BC (CreateEmployee, UpdateEmployee, DisableEmployee).

**Date de refactoring** : 2026-02-12
**Impact** : 11 fichiers modifiés (3 UseCases, 3 Controllers, 5 Tests)
**Validation** : Tous les tests passent, PHPStan level 9 OK

**Recommandation** : Appliquer ce pattern pour tous les nouveaux UseCases (Auth BC, Inventory BC, etc.)
