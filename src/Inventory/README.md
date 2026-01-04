# Bounded Context : Inventory

## Vue d'ensemble

Le BC Inventory gère les inventaires de stock, permettant de comparer le stock theorique (calculé) au stock réel (compté physiquement).

## Architecture

```
Inventory/
├── Entities/          # Domaine
│   ├── Inventory.php
│   ├── InventoryItem.php
│   ├── InventoryItemCollection.php
│   ├── VO/            # Value Objects
│   └── Exception/     # Exceptions métier
├── UseCases/          # Cas d'usage (9 au total)
│   ├── CreateInventory/           # Créer un nouvel inventaire
│   ├── LoadArticlesAndStartInventory/  # Charger articles et démarrer
│   ├── RecordRealStockForZone/    # Saisir le stock réel par zone
│   ├── FinishCounting/            # Terminer le comptage (→ REVIEW)
│   ├── ReviewDiscrepancies/       # Réviser les écarts
│   ├── ResumeCountingFromReview/  # Reprendre comptage (← REVIEW)
│   ├── CompleteInventory/         # Finaliser (→ COMPLETED)
│   ├── CancelInventory/           # Annuler (→ CANCELLED)
│   └── GetInventories/            # Lister les inventaires
├── Adapters/          # Infrastructure
│   ├── Controller/
│   └── Gateway/ORM/
└── Tests/
```

## Modèle de données

### Inventory

Un inventaire contient :
- UUID unique
- Liste de zones de stockage à inventorier
- Date d'inventaire
- Status : `DRAFT` → `IN_PROGRESS` ⇄ `REVIEW` → `COMPLETED` (+ `CANCELLED`)
- Collection d'`InventoryItem`

### InventoryItem

Chaque ligne représente **un article dans une zone** :

| Champ | Type | Description |
|-------|------|-------------|
| article | ResourceUuid | Référence à l'article |
| zoneStorage | ResourceUuid | Zone de stockage |
| theoreticalStock | Quantity | Stock calculé (millièmes) |
| realStock | Quantity | Stock compté (millièmes) |
| price | Amount | Prix unitaire |
| amount | Amount | Valeur théorique |

> **Note** : Un même article peut apparaître plusieurs fois (une ligne par zone).

## Flux de traitement

### Enregistrement du stock réel par zone

```
┌─────────────────────────────────────────────────────────────┐
│  RecordRealStockForZone                                     │
├─────────────────────────────────────────────────────────────┤
│  1. Récupérer l'inventaire                                  │
│  2. Vérifier status = IN_PROGRESS                           │
│  3. Pour chaque ArticleData du batch :                      │
│     - Trouver l'item (article + zone)                       │
│     - Créer item mis à jour avec withRealStock()            │
│  4. Remplacer les items modifiés dans la collection         │
│  5. Sauvegarder (UPDATE ciblés)                             │
└─────────────────────────────────────────────────────────────┘
```

### Batch par zone

L'enregistrement se fait **par zone** pour optimiser les performances :

```php
// Request contient : inventoryUuid, zoneStorageUuid, array<ArticleData>
$inventory->recordRealStocks($articlesData, $zoneStorageUuid);
```

Chaque `ArticleData` contient :
- `articleUuid` : l'article concerné
- `realStock` : la quantité comptée (Quantity)

### Performance

| Approche | Opérations (500 items, 5 zones) |
|----------|--------------------------------|
| Item par item | 500 × (DELETE ALL + INSERT ALL) = ~500K ops |
| Batch par zone | 5 × 100 UPDATE = **500 ops** |

> **Gain : ~1000x** grâce au traitement batch et aux UPDATE ciblés.

## InventoryItemCollection

Méthodes principales :

| Méthode | Description |
|---------|-------------|
| `add(InventoryItem)` | Ajouter un item |
| `findByArticleAndZone(article, zone)` | Trouver un item spécifique |
| `replace(items[])` | Remplacer plusieurs items (batch) |
| `getTotalRealStockForArticle(article)` | Somme du realStock sur toutes les zones |
| `filterByZone(zone)` | Filtrer les items d'une zone |
| `getArticleUuids()` | Liste des articles uniques |

## Index base de données

Un index composite optimise les recherches :

```sql
CREATE INDEX idx_inventory_item_article_zone
ON inventory_item (article_id, zone_storage_id);
```

## Calcul de la différence

Chaque `InventoryItem` peut calculer la différence entre stock réel et théorique :

```php
$item->calculateDifference(); // Retourne StockDifference
```

Le `StockDifference` peut être :
- **Positif** : surplus (plus compté que prévu)
- **Négatif** : manque (moins compté que prévu)
- **Zéro** : stock conforme

## UseCases

### Workflow complet

```
                    ┌──────────────────┐
                    │  CreateInventory │
                    └────────┬─────────┘
                             │ (DRAFT)
                             ▼
              ┌───────────────────────────────┐
              │ LoadArticlesAndStartInventory │
              └──────────────┬────────────────┘
                             │ (IN_PROGRESS)
                             ▼
              ┌──────────────────────────────┐
              │    RecordRealStockForZone    │
              │   (peut être appelé N fois)  │
              └──────────────┬───────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  FinishCounting │
                    └────────┬────────┘
                             │ (REVIEW)
                             ▼
              ┌──────────────────────────────┐
              │     ReviewDiscrepancies      │
              └──────────────┬───────────────┘
                             │
              ┌──────────────┼──────────────┐
              ▼              │              ▼
┌────────────────────────┐   │     ┌────────────────────┐
│ResumeCountingFromReview│   │     │  CompleteInventory │
│  (retour IN_PROGRESS)  │   │     │    (COMPLETED)     │
└─────────────┬──────────┘   │     └────────────────────┘
              │              │
              └──────────────┘

                    ┌─────────────────┐
                    │ CancelInventory │◄── Depuis DRAFT, IN_PROGRESS ou REVIEW
                    │   (CANCELLED)   │
                    └─────────────────┘
```

### Description des UseCases

| UseCase | Description | Transition |
|---------|-------------|------------|
| **CreateInventory** | Crée un inventaire pour une date et des zones | → DRAFT |
| **LoadArticlesAndStartInventory** | Charge les articles des zones et démarre le comptage | DRAFT → IN_PROGRESS |
| **RecordRealStockForZone** | Enregistre le stock réel compté par zone (batch) | - |
| **FinishCounting** | Termine le comptage, passe en révision des écarts | IN_PROGRESS → REVIEW |
| **ReviewDiscrepancies** | Affiche et permet de valider les écarts | - |
| **ResumeCountingFromReview** | Permet de corriger des erreurs de comptage | REVIEW → IN_PROGRESS |
| **CompleteInventory** | Finalise l'inventaire et ajuste les stocks Article | REVIEW → COMPLETED |
| **CancelInventory** | Annule l'inventaire sans ajustement | * → CANCELLED |
| **GetInventories** | Liste les inventaires avec filtres | - |

### Contracts (Inter-BC)

Le BC Inventory communique avec Admin via des Contracts :

| Contract | Description |
|----------|-------------|
| `ArticleForInventory` | Récupère les articles d'une zone pour le comptage |
| `ArticleStockUpdater` | Ajuste les stocks Article lors de CompleteInventory |
| `ZoneStorageGatewayInterface` | Récupère les zones de stockage |
