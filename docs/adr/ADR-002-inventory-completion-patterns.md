# ADR-002: Patterns d'implémentation pour la completion d'inventaire

**Date**: 2025-12-30  
**Statut**: Accepte  
**Contexte**: Complete Inventory (PR #191)

## Contexte

L'implémentation de la fonctionnalité "Complete Inventory" implique plusieurs choix architecturaux concernant :
1. La séparation entre entités Domain et ORM
2. La gestion des événements metier (LowStockDetected)
3. L'atomicité des operations cross-BC

## Decisions

### 1. Separation Domain/ORM pour resetQuantities

**Decision**: Utiliser l'entité Domain pour la logique metier, puis mettre a jour l'entite ORM directement.

```php
// Domain: logique metier (generation d'événement)
$articleDomain = $articleOrm->toDomain();
$event = $articleDomain->resetQuantity($update['quantity']);

// ORM: persistance directe
$articleOrm->setQuantity($update['quantity']->toUnit());
```

**Justification**:
- La reconversion domain→ORM ajouterait de la complexité pour un simple setter
- L'événement metier est correctement généré via le domain
- Le pattern évite le N+1 query (batch load + direct update)

**Alternatives considérées**:
- `Article::updateFromDomain()` : overhead inutile pour un seul champ
- Reconvertir domain→ORM : complexité accrue, mapping bidirectionnel

### 2. Gestion des événements LowStockDetected

**Decision**: Logger les événements pour le monitoring. EventDispatcher prévu pour une itération future.

**Implementation actuelle**:
```php
foreach ($lowStockEvents as $event) {
    $this->logger->warning('Article sous stock minimum', [...]);
}
```

**Justification**:
- Le logging fournit une traçabilité immédiate
- L'EventDispatcher Symfony nécessite une architecture reactive plus complete
- Iteration future : notifications (email, Slack), actions automatiques

**Evolution prévue**:
- Injecter `EventDispatcherInterface`
- Créer `LowStockListener` pour les notifications
- Conserver le logging comme fallback

### 3. Transaction atomique via TransactionalExecutorInterface

**Decision**: Wrapper l'exécution du UseCase dans une transaction pour garantir l'atomicité.

```php
return $this->transactionalExecutor->execute(function () use ($request) {
    // stock updates + inventory save = atomic
});
```

**Justification**:
- Évite l'inconsistance si `inventoryRepository->save()` échoue après les stocks updates
- Interface abstraite permet le test unitaire (mock passthrough)
- Implementation Doctrine utilise `wrapInTransaction()`

### 4. DEFAULT 0 sur discrepancy_amount

**Decision**: Conserver le DEFAULT 0 dans la migration.

**Justification**:
- Permet la migration sur donnees existantes sans erreurs
- Valeur sémantiquement correcte (0 = pas d'écart)
- Mesure defensive acceptable

## Consequences

### Positives
- Performance : 1 query batch au lieu de 2N queries
- Testabilité : TransactionalExecutor mockable
- Traçabilité : événements loggés

### Negatives
- Le setter ORM direct bypass la validation domain (acceptable ici car quantité deja validée)
- Les événements ne déclenchent pas d'actions réactives (iteration future)

## References

- PR #191: Complete Inventory implementation
- `src/Admin/Adapters/Gateway/ORM/Repository/DoctrineArticleRepository.php`
- `src/Admin/Adapters/Gateway/Contracts/Updater/Article/ArticleQuantityUpdater.php`
- `src/Inventory/UseCases/CompleteInventory/CompleteInventory.php`
