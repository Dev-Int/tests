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
├── UseCases/          # Cas d'usage
│   ├── CreateAnInventory/
│   ├── StartAnInventory/
│   └── RecordRealStockForZone/
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
