# TODO List - Tâches actives

**Dernière mise à jour** : 2026-01-15

---

## 🔴 Priority High

### TwigComponents spécifiques par Bounded Context

**Status** : ⬜ À faire
**GitHub Issue** : [#195](https://github.com/Dev-Int/tests/issues/195)

**Objectif** :
Créer des TwigComponents spécifiques aux BC (avec dépendances métier) dans `BC\Twig\Components\`.

**État actuel** :
- ✅ Contracts existent : `src/Admin/Contracts/` (ZoneStorageProvider, ArticleProvider, etc.)
- ✅ Components génériques : `src/Shared/Twig/Components/` (Icons, Pagination, etc.)

**Tâches** :
- [ ] Créer `Admin\Twig\Components\` (sélecteurs d'entités, etc.)
- [ ] Créer `Inventory\Twig\Components\` (components Inventory)
- [ ] Documenter le pattern provider dans `.claude/BOUNDED_CONTEXTS_QUICK.md`

**Principe clé** :
Les appels inter-BC doivent passer **UNIQUEMENT** par `BC\Contracts`, et **JAMAIS** par `BC\Adapters`.

---

### Refactoring Packaging : utiliser consumerUnit au lieu de parcel

**Status** : ⬜ À faire
**GitHub Issue** : [#196](https://github.com/Dev-Int/tests/issues/196)
**Prérequis pour** : Fiche Recette, Tests E2E Inventory

**Contexte** :
Le code actuel de saisie des stocks (Inventory) utilise `parcel` comme niveau de packaging pour la saisie. Or, pour la **fiche recette**, les quantités seront exprimées en `consumerUnit` (unité de consommation).

**Structure Packaging** (rappel) :
```
Packaging
├── parcel (colis fournisseur) - ex: carton de 6 bouteilles
├── subParcel (sous-colis) - ex: pack de 2 bouteilles
└── consumerUnit (unité consommation) - ex: 1 bouteille ← CIBLE
```

**Tâches** :
- [ ] Refactoriser les noms de champs `_parcel` → `_consumerUnit` ou rendre générique
- [ ] Adapter les conversions de quantités (Quantity VO)
- [ ] Mettre à jour les tests fonctionnels concernés
- [ ] Saisie stock fonctionne toujours correctement

**Note** : Les tests E2E (#197) sont déjà terminés. Cette tâche impactera principalement la Fiche Recette.

---

## 🟡 Priority Medium

### ADR - Décisions architecturales BC Inventory

**Status** : ⬜ À faire
**GitHub Issue** : [#199](https://github.com/Dev-Int/tests/issues/199)

**Décisions à documenter** :
- Immutabilité des entités (withXXX pattern)
- Batch par zone pour performance
- Workflow de statuts et transitions
- Intégration avec Admin BC via Gateways
- Valorisation des écarts (discrepancyAmount)

---

### Migrer les services.yaml vers services.php

**Status** : ⬜ À faire
**GitHub Issue** : TBD

**Objectif** :
Migrer tous les fichiers `services.yaml` vers `services.php` pour suivre les futures bonnes pratiques de Symfony.

**Fichiers à migrer** :
- `src/Admin/Frameworks/config/services.yaml` → `services.php`
- `src/Inventory/Frameworks/config/services.yaml` → `services.php`
- `src/Shared/Frameworks/config/services.yaml` → `services.php`
- `config/services.yaml` → `services.php`

---

### Upgrader le code vers PHP 8.3 avec Rector

**Status** : ⬜ À faire
**GitHub Issue** : TBD

**Objectif** :
Passer Rector sur tout le codebase pour utiliser les nouvelles fonctionnalités PHP 8.3 :
- Typed constants (`private const string ROUTE_NAME = '...'`)
- Readonly properties
- New in initializers

**Commande** :
```bash
make rector
```

---

### Implémenter le logging applicatif

**Status** : ⬜ À faire
**GitHub Issue** : TBD

**Cas d'usage identifiés** :
- `ArticleProvider::forArticle()` : Logger l'UUID quand un article n'est pas trouvé
- Erreurs métier (validation, contraintes)
- Appels inter-BC (Contracts/Providers)

---

### Traduire les messages d'erreur des exceptions domaine

**Status** : ⬜ À faire
**GitHub Issue** : TBD

**Contexte** :
Les exceptions domaine (`DomainException`) ont leurs messages en anglais, mais l'UX doit être en français.

**Tâches** :
- [ ] Lister toutes les exceptions domaine et leurs messages
- [ ] Créer les clés de traduction correspondantes
- [ ] Choisir l'approche (catch spécifique vs service centralisé)

---

## 🟢 Priority Low

### End-to-End Tests Coverage - Améliorations optionnelles

**Status** : 🟢 **BONNE COUVERTURE** (améliorations optionnelles possibles)

**Tests manquants (optionnels)** :

**Article - Tests nominaux Update** :
- ❌ `ChangeArticleFinancialInformationTest`
- ❌ `ChangeArticleStorageInformationTest`
- ❌ `ReAssignArticleSupplierTest`
- ❌ `RenameArticleTest`

**Supplier - Tests nominaux Update** :
- ❌ `RenameSupplierTest`
- ❌ `ChangeDomiciliationSupplierTest`
- ❌ `ChangeContactSupplierTest`
- ❌ `ChangeDeliverySpecificationsSupplierTest`

---

### Refactoring ArticleAggregatorBuilder vers DBAL (optionnel)

**Status** : ⬜ À faire si besoin de performance
**Fichier** : `src/Admin/Adapters/Gateway/ORM/Provider/Article/DefaultArticleAggregatorBuilder.php`

**Déclencheur** : Implémenter si latence détectée sur listings articles.

---

## 🚀 Roadmap Future

### [Epic] StockManagement BC - Gestion quotidienne des stocks

**Status** : 📋 Roadmap
**GitHub Issue** : [#202](https://github.com/Dev-Int/tests/issues/202)

**Vision** :
Nouveau Bounded Context pour la gestion quotidienne des stocks :
- Mouvements de stock en temps réel (entrées, sorties, transferts)
- Calcul automatique du stock théorique
- Traçabilité complète des opérations
- Alertes sur seuils

**Dépendances** :
- ✅ BC Inventory complet
- ✅ BC Admin avec entités Article

---

## 📚 Archive

Historique des tâches complétées :
→ Voir `.claude/archive/TODO_2025.md` (2024-2025)
→ Voir `.claude/archive/TODO_2026.md` (2026)
