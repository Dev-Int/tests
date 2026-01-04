# ADR-003: Decisions architecturales du BC Inventory

**Date**: 2026-01-04  
**Statut**: Accepte  
**Contexte**: BC Inventory - Milestone Inventory

## Contexte

Le Bounded Context Inventory gère le processus d'inventaire physique des stocks. Son implementation implique plusieurs choix architecturaux majeurs concernant :
1. L'immutabilité des entités
2. La performance du traitement par zone
3. Le workflow de statuts
4. L'intégration avec le BC Admin
5. La valorisation des écarts
6. La gestion des articles multi-zones

## Decisions

### 1. Immutabilité des entités - Pattern `withXXX()`

**Decision** : Utiliser des classes `final readonly` avec des méthodes `withXXX()` pour les entités du domaine Inventory.

```php
final readonly class InventoryItem
{
    public function withRealStock(Quantity $realStock, RealStockComponents $components): self
    {
        return new self(
            article: $this->article,
            articleName: $this->articleName,
            zoneStorage: $this->zoneStorage,
            price: $this->price,
            theoreticalStock: $this->theoreticalStock,
            realStock: $realStock,                    // Modifié
            realStockComponents: $components,         // Modifié
            amount: $this->amount,
            packaging: $this->packaging,
            countedAt: ClockFactory::clock()->now(),  // Auto-génère
            reviewed: $this->reviewed,
            reviewNotes: $this->reviewNotes,
            actionPlan: $this->actionPlan,
        );
    }
}
```

**Justification** :
- Thread-safety : pas d'effets de bord lors des modifications
- Traçabilité : chaque version est une nouvelle instance
- Testabilité : pas d'etat partage entre tests
- Contraste intentionnel avec Admin BC qui utilise des mutations directes

**Alternatives considérées** :
- Mutations directes (comme Admin BC) : rejeté, car le processus d'inventaire nécessite plus de traçabilité
- Event Sourcing : trop complexe pour ce cas d'usage

### 2. Batch par zone - Performance optimisée

**Decision** : Enregistrer les stocks par zone avec remplacement indexé plutôt qu'item par item.

```php
// UseCase: traite une zone complete en un appel
$items = $inventory->recordRealStocks($articlesData, $zoneStorageUuid);

// Collection: remplacement O(n) au lieu de O(n*m)
public function replace(array $newItems): void
{
    // Index par identifiant unique (article_uuid_zone_uuid)
    $indexedNewItems = [];
    foreach ($newItems as $newItem) {
        $indexedNewItems[$newItem->identifier()] = $newItem;
    }

    foreach ($this->items as $key => $item) {
        if (isset($indexedNewItems[$item->identifier()])) {
            $this->items[$key] = $indexedNewItems[$item->identifier()];
        }
    }
}
```

**Justification** :
- Performance : ~1000x gain vs recherche linéaire pour chaque item
- UX : l'opérateur saisit tous les articles d'une zone en une seule operation
- Atomicité : tous les articles d'une zone sont mis à jour ensemble

**Alternatives considérées** :
- Item par item : O(n*m) complexité, rejeté pour performance
- Map persistante : overhead mémoire inutile pour ce volume

### 3. Workflow de statuts - Machine d'etat

**Decision** : Implementer une machine d'etat avec transitions validées et retour possible depuis REVIEW.

```
    DRAFT
      |
      | startProcessing()
      v
  IN_PROGRESS <----+
      |            |
      | finishCounting()    resumeCounting()
      v            |
    REVIEW --------+
      |
      | complete()
      v
  COMPLETED

  (CANCELLED accessible depuis DRAFT, IN_PROGRESS, REVIEW)
```

```php
enum InventoryStatus: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'inProgress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function isCancellable(): bool
    {
        return \in_array($this->value, self::ACTIVE_STATUSES, true);
    }

    public function isResumable(): bool
    {
        return $this === self::REVIEW;
    }
}
```

**Justification** :
- Processus metier : le comptage peut reveler des erreurs nécessitant un recomptage
- Retour REVIEW → IN_PROGRESS : permet de corriger une zone sans annuler tout l'inventaire
- Annulation possible : jusqu'à la completion pour gérer les cas d'urgence

**Alternatives considérées** :
- Workflow linéaire sans retour : rejeté car trop rigide pour le metier
- États supplémentaires (PAUSED, etc.) : YAGNI, pas de besoin exprime

### 4. Integration Admin BC – Gateways et Contracts

**Decision** : Communiquer avec Admin via des interfaces Contracts (Provider pattern).

```php
// Inventory depend de l'interface (Contract)
interface ArticleGatewayInterface
{
    public function provideForZones(array $zoneStorageUuids): iterable;
}

// L'implémentation utilise le Provider d'Admin
final readonly class ArticleGateway implements ArticleGatewayInterface
{
    public function __construct(private ArticleProvider $articleProvider) {}

    public function provideForZones(array $zoneStorageUuids): iterable
    {
        $articles = $this->articleProvider
            ->forArticles([])
            ->withFilter(ArticleFilter::ZONE_STORAGE, $zoneStorageUuids)
            ->provideAll();

        foreach ($articles as $articleResult) {
            yield new Article(/* mapping */);
        }
    }
}
```

**Justification** :
- Découplage : Inventory ne dépend pas des entités Admin
- Testabilité : mock facile du Gateway
- Contrats explicites : Admin expose ce qu'il autorise via Contracts

**Alternatives considérées** :
- Dépendance directe aux entités Admin : viole les règles Deptrac
- Messaging asynchrone : overhead inutile pour ce flux synchrone

### 5. Valorisation des écarts - discrepancyAmount

**Decision** : Calculer la valorisation financière des écarts lors de la completion.

```php
// Formule: Sum (realStock - theoreticalStock) * unitPrice
private function calculateDiscrepancyAmount(Inventory $inventory): Amount
{
    $total = Amount::zero();

    foreach ($inventory->items() as $item) {
        $difference = $item->calculateDifference();        // StockDifference
        $itemAmount = $item->price()->computeQuantity($difference);
        $total = $total->add($itemAmount);
    }

    return $total;
}

// Stockage en centimes (integer) pour précision
#[ORM\Column(name: 'discrepancy_amount', type: 'integer', options: ['default' => 0])]
private int $discrepancyAmount = 0;
```

**Justification** :
- Precision : calcul en millièmes (Quantity) et centimes (Amount) évite les arrondis
- Sémantique : positif = surplus (gain), négatif = manquant (perte)
- Audit : valeur enregistrée pour historique et reporting

**Alternatives considérées** :
- Calcul à la volee : rejeté, car l'inventaire peut être archivé
- Stockage en float : rejeté pour problèmes de précision

### 6. Aggregation multi-zones - Article dans plusieurs zones

**Decision** : Identifier chaque InventoryItem par le couple (article_uuid, zone_uuid) et agréger au complete().

```php
final readonly class InventoryItem
{
    public function identifier(): string
    {
        return "{$this->article->toString()}_{$this->zoneStorage->toString()}";
    }
}

// Agrégation lors de la completion
public function getTotalRealStockForArticle(ResourceUuid $articleUuid): Quantity
{
    $totalMilliemes = array_reduce(
        $this->items,
        static fn (int $carry, InventoryItem $item): int =>
            $item->isForArticle($articleUuid)
                ? $carry + $item->realStock()->toMilliemes()
                : $carry,
        0
    );

    return Quantity::fromMilliemes($totalMilliemes);
}
```

**Justification** :
- Réalité métier : un article peut être stocké dans plusieurs zones (reserve, rayon, etc.)
- Granularité : le comptage se fait zone par zone
- Agrégation finale : Admin reçoit un seul total par article

**Alternatives considérées** :
- Un seul item par article (somme des zones) : perd la granularité du comptage
- Entité ZoneStock séparée : complexité accrue sans bénéfice

## Consequences

### Positives
- **Immutabilité** : traçabilité complete, pas d'effets de bord
- **Performance** : batch par zone avec remplacement indexe O(n)
- **Flexibilité** : workflow permet retour et correction
- **Découplage** : Inventory independent d'Admin via Contracts
- **Precision** : calculs en milliemes/centimes, pas de perte d'arrondi
- **Granularité** : comptage par zone, agrégation à la fin

### Negatives
- **Mémoire** : création de nouvelles instances (acceptable avec GC PHP)
- **Complexité** : machine d'etat à maintenir
- **Couplage temporel** : articles chargés au démarrage (pas de refresh)

## References

### Entités et VO
- `src/Inventory/Entities/Inventory.php`
- `src/Inventory/Entities/InventoryItem.php`
- `src/Inventory/Entities/InventoryItemCollection.php`
- `src/Inventory/Entities/VO/InventoryStatus.php`
- `src/Inventory/Entities/VO/StockDifference.php`

### UseCases
- `src/Inventory/UseCases/RecordRealStockForZone/RecordRealStockForZone.php`
- `src/Inventory/UseCases/CompleteInventory/CompleteInventory.php`
- `src/Inventory/UseCases/FinishCounting/FinishCounting.php`
- `src/Inventory/UseCases/ResumeCountingFromReview/ResumeCountingFromReview.php`

### Contracts et Gateways
- `src/Admin/Contracts/Services/Provider/Article/ArticleProvider.php`
- `src/Admin/Contracts/Services/Updater/Article/ArticleQuantityUpdater.php`
- `src/Inventory/Adapters/Gateway/ArticleGateway.php`
- `src/Inventory/Adapters/Gateway/ArticleStockUpdater.php`

### ADR connexes
- ADR-002: Patterns d'implémentation pour la completion d'inventaire
