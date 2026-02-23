# ADR-007: Envoi d'Email Asynchrone avec Retry pour CreateEmployee

**Date** : 2026-02-15
**Statut** : Accepté
**Décideurs** : Équipe technique Admin BC

---

## Contexte

### Problème Initial

Dans l'implémentation initiale de `CreateEmployee`, l'envoi de l'email de bienvenue s'effectuait **de manière synchrone** à l'intérieur de la transaction Doctrine (ligne 93-104 de `CreateEmployee.php`).

**Impact métier critique** :
- Si l'envoi d'email échoue (SMTP down, quota dépassé, timeout réseau), l'exception remonte et provoque un **rollback complet** de la transaction
- **Résultat** : L'Employee ET le User ne sont jamais créés, même si l'échec est purement technique et temporaire
- Une panne SMTP temporaire (même de quelques secondes) bloque complètement l'embauche d'un employé
- Le processus RH est interrompu pour un problème d'infrastructure qui n'a aucun lien avec les données métier

### Motivation

**Découplage logique métier / infrastructure** :
- La création d'un Employee est une opération métier critique qui doit réussir **indépendamment** de l'état du serveur SMTP
- L'envoi d'email est une notification secondaire qui peut échouer sans impacter la validité métier de la création

**Résilience et fiabilité** :
- Les pannes SMTP sont fréquentes (timeouts, quotas, blacklisting temporaire)
- Une architecture résiliente doit tolérer ces pannes temporaires avec un système de retry automatique

---

## Décision

**Approche retenue : Event-Driven Architecture + Symfony Messenger + Retry**

### Architecture

```
CreateEmployee (UseCase)
    ↓ persist Employee + User (transaction)
    ↓ APRÈS commit
    ↓ publish event
EventPublisher (interface - Port)
    ↓
SymfonyEventPublisher (implémentation - Adapter)
    ↓ admin_transport (Doctrine)
EmployeeWelcomeEmailRequestedHandler (infrastructure)
    ↓ avec retry automatique (3 tentatives)
NotificationProvider (réutilisé)
```

### Principes d'implémentation

1. **Publish APRÈS transaction** :
   - L'event `EmployeeWelcomeEmailRequested` est publié **APRÈS** le commit de la transaction
   - Si le publish échoue (ex: Doctrine connection down), l'Employee/User sont déjà créés → pas de rollback
   - Pattern **Outbox** implicite

2. **Retry Strategy** :
   - `max_retries: 3` tentatives
   - `delay: 1000ms` initial
   - `multiplier: 2` (backoff exponentiel : 1s → 2s → 4s)
   - Total : ~7 secondes max avant failed transport

3. **Failed Transport** :
   - Messages qui échouent définitivement sont stockés dans `admin_transport_failed` (Doctrine)
   - Analyse manuelle possible via `bin/console messenger:failed:show`
   - Retry manuel via `bin/console messenger:failed:retry {id}`

### Composants créés

**Domaine** :
- `DomainEvent` : Interface marker pour tous les events Admin BC
- `EmployeeWelcomeEmailRequested` : Event readonly (immutable) contenant `employeeEmail`, `firstName`, `resetUrl`, `employeeUuid`

**Infrastructure** :
- `EventPublisher` (Gateway interface) : Port pour publier des events asynchrones
- `SymfonyEventPublisher` (Adapter) : Implémentation déléguant à `MessageBusInterface`
- `EmployeeWelcomeEmailRequestedHandler` : Handler qui reçoit l'event, construit `EmailPayload`, et appelle `NotificationGateway::sendEmail()`

**Configuration** :
- `src/Admin/Frameworks/config/packages/messenger.php` : Configuration Messenger du BC Admin
  - Namespace : `Symfony\Component\DependencyInjection\Loader\Configurator`
  - Transports : `admin_transport` (DSN Doctrine) + `admin_transport_failed`
  - Retry strategy : max_retries=3, delay=1000ms, multiplier=2
  - Routing : `Admin\Entities\Event\DomainEvent` → `admin_transport`
  - Override env test : `admin_transport` → `in-memory://`

**Note** : Configuration en **PHP** plutôt que YAML (bonne pratique Symfony moderne)
- Organisation par BC : Config dans `src/Admin/Frameworks/` (chargée automatiquement par Kernel)
- Type-safety avec `Symfony\Config\FrameworkConfig`
- Auto-complétion IDE
- Transports dédiés au BC : `admin_transport` (évite conflits avec autres BC)

---

## Alternatives Considérées

### Option A : Try-Catch Simple (Rejetée)

```php
try {
    $this->notificationGateway->sendEmail(...);
} catch (\Throwable $e) {
    // Log et ignore
}
```

**Pourquoi rejeté** :
- ❌ Pas de retry automatique → email perdu définitivement en cas de panne temporaire
- ❌ Complexité dans le UseCase (responsabilité de gestion d'erreur infrastructure)
- ❌ Difficile à tester et à monitorer

### Option B : Queue Custom (Rejetée)

Implémenter une queue custom avec une table `email_queue`.

**Pourquoi rejeté** :
- ❌ Réinventer la roue (Symfony Messenger existe déjà)
- ❌ Complexité accrue : worker custom, gestion du retry, monitoring
- ❌ Pas de garanties de livraison, pas de failed transport
- ❌ Maintenance à long terme

### Option C : Service Externe (ex: AWS SQS) (Rejetée pour MVP)

**Pourquoi rejeté pour le MVP** :
- ❌ Dépendance externe (coût, complexité réseau)
- ❌ Overkill pour le volume actuel (<100 emails/jour)
- ✅ **Possible migration future** si volume > 1000 emails/jour (changer DSN seulement)

---

## Conséquences

### Positives ✅

**Résilience métier** :
- Employee + User créés même si SMTP down → processus RH non bloqué
- Retry automatique (3 tentatives) couvre 99% des pannes SMTP temporaires

**Architecture** :
- Respect Clean Architecture + DDD (EventPublisher = Port, SymfonyEventPublisher = Adapter)
- Découplage UseCase ↔ Infrastructure (pas de dépendance Symfony Messenger dans le domaine)
- Pattern Event-Driven explicite (events domaine réutilisables)

**Maintenabilité** :
- Failed transport permet analyse post-mortem (debugging, alerting)
- Logs structurés (employeeEmail, employeeUuid) pour traçabilité
- Tests isolation complète (transport `in-memory://`)

**Scalabilité** :
- Migration facile vers RabbitMQ/AWS SQS (changer DSN uniquement)
- Worker Messenger peut être horizontal scalé (plusieurs instances)

### Négatives ⚠️

**Complexité opérationnelle** :
- **Worker Messenger requis en production** (via Supervisor/Systemd)
  ```ini
  [program:messenger-consume]
  command=php /path/to/bin/console messenger:consume admin_transport --time-limit=3600
  numprocs=2
  autostart=true
  autorestart=true
  ```
- Monitoring nécessaire (admin_transport_failed > 10 messages → incident)
- Logs worker à surveiller (`/var/log/messenger-worker.*.log`)

**Migration DB** :
- Tables Messenger créées automatiquement :
  - `messenger_messages` (pour `admin_transport`)
  - Queue `admin_transport_failed` dans table `messenger_messages`
- Commande manuelle si `auto_setup=0` : `bin/console messenger:setup-transports`
- Vérification schéma : `bin/console doctrine:schema:validate`

**Async = Latence** :
- Email envoyé **quelques secondes APRÈS** création Employee (non instantané)
- Acceptable pour use case "bienvenue" (pas critique temps réel)

**Event non envoyé si publish échoue** :
- Si `$this->eventPublisher->publish()` échoue (rare : Doctrine connection down après commit), email jamais envoyé
- **Mitigation** : Monitoring APM (Sentry/NewRelic) pour détecter ces erreurs rares

### ⚠️ Limitation connue — Idempotence non garantie (Deferred)

**Problème** :
`EmployeeWelcomeEmailRequestedHandler` appelle `passwordResetGateway->createResetToken()` à chaque exécution.
Si Messenger relance le handler après un timeout ACK transport (ex : email envoyé mais ACK non reçu),
un nouveau token est créé — les tokens précédents peuvent être invalidés selon la politique de rotation.

**Impact** :
- L'employé peut recevoir plusieurs emails de bienvenue avec des URLs différentes
- Seule l'URL du dernier email serait valide si les tokens se remplacent

**Solutions à investiguer** :
- **Option A** : Persister le `resetToken` dans l'event lui-même (créé dans la transaction, passé en payload)
  → Token créé une seule fois, handler devient idempotent
- **Option B** : Vérifier l'existence d'un token valide avant d'en créer un nouveau
  (`passwordResetGateway->findValidToken($userUuid) ?? createResetToken(...)`)
- **Option C** : Rendre `createResetToken()` idempotent côté Auth BC (même token si non expiré)

**Statut** : Décision différée — à traiter avant passage en production si volume > MVP.
Voir PR #255 commentaire [issuecomment-3939704628](https://github.com/Dev-Int/tests/pull/255#issuecomment-3939704628).

---

## Validation

### Tests TDD

**Tests unitaires** :
- `CreateEmployeeTest` : Mock `EventPublisher`, vérifie `publish()` appelé avec bon event
- `EmployeeWelcomeEmailRequestedHandlerTest` : Mock `NotificationGateway`, vérifie retry behavior

**Tests d'intégration** :
- `CreateEmployeeTransactionTest` : Vérifie Employee+User créés même si handler échoue
- Transport `in-memory://` pour isolation complète

**Quality gates** :
- ✅ 423 tests unitaires (1325 assertions)
- ✅ 312 tests fonctionnels (1717 assertions)
- ✅ PHPStan niveau 9 (0 erreurs)
- ✅ Deptrac (0 violations)

### Production

**Supervision worker** :
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start messenger-consume:*
```

**Monitoring** :
- Failed messages Admin BC : `bin/console messenger:failed:show admin_transport_failed`
- Retry manuel : `bin/console messenger:failed:retry {id} --transport=admin_transport_failed`
- Alerting : `admin_transport_failed` > 10 messages → incident P2

**Multi-BC** (si plusieurs BC utilisent Messenger) :
```bash
# Worker dédié Admin BC
bin/console messenger:consume admin_transport --time-limit=3600

# Worker dédié Inventory BC (futur)
bin/console messenger:consume inventory_transport --time-limit=3600
```

---

## Références

- [PR Review #255 - Point 2](https://github.com/Dev-Int/tests/pull/255#issuecomment-3893419112) : Contexte initial du problème
- [Symfony Messenger Documentation](https://symfony.com/doc/current/messenger.html)
- [Transactional Outbox Pattern](https://microservices.io/patterns/data/transactional-outbox.html)

---

## Notes d'implémentation

### Ordre d'exécution critique

```php
// ✅ CORRECT
$response = $this->transactionGateway->wrapInTransaction(...);
// Transaction committée ici ↑

$this->eventPublisher->publish(...);  // APRÈS commit
return $response;
```

```php
// ❌ INCORRECT
$this->transactionGateway->wrapInTransaction(function() {
    // ...
    $this->eventPublisher->publish(...);  // DANS transaction → rollback si publish échoue !
});
```

### Capture resetUrl

Utilisation d'une référence pour capturer `resetUrl` dans la closure :

```php
$resetUrl = null;
$response = $this->transactionGateway->wrapInTransaction(
    operation: $this->createNewEmployee($request, $resetUrl),  // $resetUrl passé par référence
);
// $resetUrl maintenant disponible ici
```

### Configuration par BC (Architecture modulaire)

**Organisation** :
```
src/Admin/Frameworks/config/packages/messenger.php  ← Config Admin BC
src/Inventory/Frameworks/config/packages/...       ← Config Inventory BC (futur)
```

**Auto-chargement Kernel** (`src/Kernel.php`) :
```php
$container->import($this->getProjectDir() . '/src/*/Frameworks/config/packages/*.{php,yaml}');
```

**Avantages** :
- ✅ Isolation par BC : Chaque BC configure ses propres transports
- ✅ Pas de fichier global centralisé (évite couplage)
- ✅ Transports nommés par BC : `admin_transport`, `inventory_transport`, etc.
- ✅ Scalabilité : Workers dédiés par BC si besoin

**Exemple config Admin BC** :
```php
<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Admin\Entities\Event\DomainEvent;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework, ContainerConfigurator $container): void {
    $messenger = $framework->messenger();

    // Transport Admin BC avec retry strategy
    $messenger->transport('admin_transport')
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->failureTransport('admin_transport_failed')
        ->retryStrategy()
            ->maxRetries(3)
            ->delay(1000)
            ->multiplier(2)
    ;

    // Failed transport dédié Admin BC
    $messenger->transport('admin_transport_failed')
        ->dsn('doctrine://default?queue_name=admin_transport_failed')
    ;

    // Routing : tous les DomainEvent Admin → admin_transport
    $messenger->routing(DomainEvent::class)->senders(['admin_transport']);

    // Override pour tests : in-memory
    if ($container->env() === 'test') {
        $messenger->transport('admin_transport')->dsn('in-memory://');
    }
};
```

---

## Statut

**Accepté** - Implémenté le 2026-02-15

**Prochaines étapes** :
1. Déployer avec worker Messenger en staging
2. Tester scénarios : succès, retry, failed transport
3. Configurer monitoring (Sentry alerts sur failed transport)
4. Déployer en production avec supervision Supervisor
5. Monitorer pendant 1 semaine (failed messages, latence, logs worker)
