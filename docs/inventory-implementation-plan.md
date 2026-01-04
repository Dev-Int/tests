# Plan d'Implémentation - Bounded Context Inventory

**Date de création** : 2025-12-13  
**Bounded Context** : Inventory (Comptage Physique)  
**Approche** : 🔴 TDD - Red/Green/Refactor - Itérations Verticales  
**Status** : 🟢 En cours d'implémentation

---

## 🎯 Vision du Bounded Context

### Objectif
Implémenter un système de comptage physique des stocks permettant de :
- Créer des sessions d'inventaire périodiques
- Comparer le stock théorique avec le stock réel compté
- Ajuster automatiquement les stocks dans le système
- Tracer les écarts et valoriser les différences

### Approche Progressive

**Phase 1 (ACTUELLE)** : **Inventory** = Comptage physique périodique
- Workflow : DRAFT → IN_PROGRESS → REVIEW → COMPLETED
- Saisie par zone de stockage (un inventaire = une zone)
- Chargement automatique de tous les articles de la zone
- Phase de révision des écarts avant finalisation
- Agrégation multi-zones si article dans plusieurs zones
- Ajustement des stocks Article lors de la finalisation

**Phase 2 (FUTURE)** : **StockManagement** = Gestion quotidienne
- Mouvements de stock en temps réel (entrées/sorties/transferts)
- Calcul automatique du stock théorique
- Le comptage physique servira de vérification/correction

---

## 🔴🟢🔵 Approche TDD : Développement par Itérations Verticales

### Principe : Red → Green → Refactor

Contrairement aux approches traditionnelles qui créent d'abord toutes les entités, puis tous les use cases, puis l'infrastructure, **cette approche TDD suit des itérations verticales** :

1. **🔴 RED** : Écrire le test du UseCase en premier (il échoue)
2. **🟢 GREEN** : Implémenter le minimum pour que le test passe
   - Créer les entités/VOs nécessaires au fur et à mesure
   - Créer les exceptions au besoin
   - Mock repository pour tests unitaires
3. **Adapter** : Formulaire + Controller + Template
4. **Persistence** : Migration + ORM + Repository impl
5. **E2E** : Test bout en bout
6. **🔵 REFACTOR** : Nettoyer, optimiser, extraire

**Avantages** :
- ✅ Code guidé par les tests
- ✅ Pas de code inutile (YAGNI)
- ✅ Feedback rapide
- ✅ Features livrables incrémentalement

---

## 🎯 Itérations (Feature Slices)

### ✅ Itération 0 : Setup Infrastructure
**Status** : ✅ Terminé  
**GitHub Issue** : N/A

**Réalisations** :
- [x] Arborescence `src/Inventory/` créée
- [x] Autoload PSR-4 configuré dans `composer.json`
- [x] `src/Inventory/Frameworks/config/services.yaml` créé
- [x] `src/Inventory/Frameworks/config/routes.yaml` créé
- [x] Deptrac configuré pour Inventory BC

**Critères validés** :
- ✅ Autoload fonctionne
- ✅ Symfony détecte le module
- ✅ Deptrac analyse le BC sans erreur

---

### ✅ Itération 1 : Créer un Inventaire
**Status** : ✅ Terminé  
**GitHub Issue** : #163  
**Estimation** : 1 jour

#### 1.1 - 🔴 RED : Test CreateInventory UseCase
**Fichier** : `src/Inventory/Tests/UseCases/CreateInventory/CreateInventoryTest.php`  
**Status** : ✅ Terminé

**Scénarios testés** :
- [x] Créer inventaire avec succès
- [x] Refuser date dans le passé (dataProvider)
- [x] Refuser si inventaire actif existe déjà pour la zone
- [x] Générer UUID automatiquement
- [x] Status initial = DRAFT
- [x] Amount initial = 0.0
- [x] CreatedAt/UpdatedAt générés

---

#### 1.2 - 🟢 GREEN : Implémenter CreateInventory
**Status** : ✅ Terminé

**Fichiers créés** :
- [x] `src/Inventory/Entities/Inventory/Inventory.php` (entité minimale)
- [x] `src/Inventory/Entities/VO/InventoryStatus.php` (enum DRAFT)
- [x] `src/Inventory/Entities/Exception/PastDateExpected.php`
- [x] `src/Inventory/Entities/Exception/ActiveInventoryAlreadyExistsForZone.php`
- [x] `src/Inventory/Entities/Repository/InventoryRepository.php` (interface)
- [x] `src/Inventory/UseCases/CreateInventory/CreateInventoryRequest.php`
- [x] `src/Inventory/UseCases/CreateInventory/CreateInventoryResponse.php`
- [x] `src/Inventory/UseCases/CreateInventory/CreateInventory.php`

**Critères validés** :
- ✅ Tous les tests passent
- ✅ PHPStan : 0 erreur
- ✅ Deptrac : architecture respectée

---

#### 1.3 - Adapter : Formulaire + Controller
**Status** : ✅ Terminé

**Fichiers créés** :
- [x] `src/Inventory/Adapters/Form/Type/CreateInventoryType.php`
- [x] `src/Inventory/Adapters/Controller/Symfony/Controller/CreateInventory/CreateInventoryController.php`
- [x] `src/Inventory/Adapters/Controller/Symfony/Controller/CreateInventory/CreateInventoryApiRequest.php`
- [x] `src/Inventory/Frameworks/templates/inventory/create.html.twig`
- [x] Test fonctionnel : `src/Inventory/Tests/Adapters/Controller/CreateInventoryControllerTest.php`

**Critères validés** :
- [x] Formulaire affiché sans erreurs
- [x] Soumission valide crée l'inventaire
- [x] Erreurs métier affichées correctement
- [x] Flash message après création
- [x] Redirection vers page détail

---

#### 1.4 - Persistence : Migration + Repository
**Status** : ✅ Terminé

**Fichiers créés** :
- [x] Migration `Version20251214120000.php`
- [x] `src/Inventory/Adapters/Gateway/ORM/Entity/Inventory.php`
- [x] `src/Inventory/Adapters/Gateway/ORM/Repository/DoctrineInventoryRepository.php`
- [x] Tests ORM

**Critères validés** :
- [x] Migration exécutée sans erreurs
- [x] Repository persiste et récupère correctement
- [x] `hasActiveForZone()` fonctionne
- [x] Tests fonctionnels passent

---

#### 1.5 - 🔵 REFACTOR : Optimisation
**Status** : ✅ Terminé

**Actions réalisées** :
- [x] VOs extraits (InventoryStatus, Quantity, Amount, etc.)
- [x] Requêtes repository optimisées
- [x] Code nettoyé
- [x] Couverture tests > 80%

---

### ✅ Itération 2 : Démarrer l'Inventaire (Charger Articles + Transition)
**Status** : ✅ Terminé
**GitHub Issues** : #167, #165
**Estimation** : 1 jour

> **Note** : Cette itération fusionne le chargement des articles et le démarrage.
> Le UseCase `StartInventory` fait les deux opérations en une seule action.

#### 2.1 - 🔴 RED : Tests StartInventory
**Fichiers** :
- `src/Inventory/Tests/UseCases/StartInventory/StartInventoryTest.php`
- `src/Inventory/Tests/Entities/InventoryLoadArticlesTest.php`

**Scénarios testés** :
- [x] Charger articles et passer en IN_PROGRESS
- [x] Refuser si inventaire non trouvé
- [x] Refuser si aucun article dans les zones (NoArticlesToLoad)
- [x] Refuser si status ≠ DRAFT

---

#### 2.2 - 🟢 GREEN : Implémenter StartInventory
**Fichiers créés** :
- [x] `src/Inventory/Entities/Inventory.php` - méthode `loadArticles()`
- [x] `src/Inventory/Entities/Exception/NoArticlesToLoad.php`
- [x] `src/Inventory/UseCases/StartInventory/StartInventory.php`
- [x] `src/Inventory/UseCases/StartInventory/StartInventoryRequest.php`
- [x] `src/Inventory/UseCases/StartInventory/StartInventoryResponse.php`

**Workflow du UseCase** :
1. Récupère l'inventaire
2. Vérifie status DRAFT
3. Charge les articles via `ArticleGateway`
4. `inventory->loadArticles()` (crée InventoryItems)
5. `inventory->startProcessing()` (DRAFT → IN_PROGRESS)
6. Sauvegarde

**Critères validés** :
- ✅ Tous les tests passent
- ✅ PHPStan : 0 erreur
- ✅ Deptrac : architecture respectée

---

#### 2.3 - Adapter : Bouton "Démarrer"
**Status** : ✅ Terminé

**Fichiers créés** :
- [x] Controller `LoadArticlesAndStartInventoryController.php`
- [x] Template avec confirmation
- [x] Flash messages

---

#### 2.4 - Persistence : Table inventory_item
**Status** : ✅ Terminé

**Actions réalisées** :
- [x] Migration pour `inventory_item`
- [x] ORM mapping
- [x] Repository implementation

---

### ✅ Itération 3 : Saisir Stock Réel
**Status** : ✅ Terminé  
**GitHub Issue** : #164  
**Estimation** : 1 jour

#### 3.1 - 🔴 RED : Test RecordRealStockForZone
**Status** : ✅ Terminé

**Scénarios testés** :
- [x] Enregistrer realStock
- [x] Calculer différence automatiquement
- [x] Refuser si status ≠ IN_PROGRESS
- [x] Refuser si realStock < 0

---

#### 3.2 - 🟢 GREEN : Implémenter RecordRealStockForZone
**Status** : ✅ Terminé

**Fichiers créés** :
- [x] `src/Inventory/UseCases/RecordRealStockForZone/`
- [x] VO Quantity avec validation
- [x] Méthode `InventoryItem->withRealStock()`

---

#### 3.3 - Adapter : Formulaire Saisie Stock
**Status** : ✅ Terminé

- [x] Liste items avec formulaire
- [x] Affichage écart en temps réel

---

### ✅ Itération 4 : Workflow REVIEW + Finalisation
**Status** : ✅ Terminé  
**GitHub Issue** : #170  
**Estimation** : 2 jours

> **Note** : Cette itération a été enrichie avec un workflow de révision complet.

#### 4.1 - FinishCounting (IN_PROGRESS → REVIEW)
**Status** : ✅ Terminé

**UseCase** : `src/Inventory/UseCases/FinishCounting/`
- [x] Transition IN_PROGRESS → REVIEW
- [x] Vérifier tous les items comptés
- [x] Tests unitaires

---

#### 4.2 - ReviewDiscrepancies (Gestion écarts)
**Status** : ✅ Terminé

**UseCase** : `src/Inventory/UseCases/ReviewDiscrepancies/`
- [x] Afficher écarts entre stock théorique et réel
- [x] Permettre validation des écarts
- [x] Tests unitaires

---

#### 4.3 - ResumeCountingFromReview (REVIEW → IN_PROGRESS)
**Status** : ✅ Terminé

**UseCase** : `src/Inventory/UseCases/ResumeCountingFromReview/`
- [x] Permettre retour en comptage pour corrections
- [x] Transition REVIEW → IN_PROGRESS
- [x] Tests unitaires

---

#### 4.4 - CompleteInventory (REVIEW → COMPLETED)
**Status** : ✅ Terminé

**UseCase** : `src/Inventory/UseCases/CompleteInventory/`
- [x] Transition REVIEW → COMPLETED
- [x] Ajuster Article.quantity dans Admin
- [x] Calculer amount (écarts valorisés)
- [x] Agrégation multi-zones
- [x] Tests unitaires

---

#### 4.5 - E2E : Workflow Complet
**Status** : ✅ Terminé

**Scénarios testés** :
- [x] Create → Start → Count → Review → Complete
- [x] Create → Start → Count → Review → Resume → Count → Review → Complete
- [x] Vérification ajustement stocks Article

---

### ✅ Itération 5 : Annuler un Inventaire
**Status** : ✅ Terminé  
**GitHub Issue** : #166  
**Estimation** : 0.5 jour

#### 5.1 - 🔴 RED : Test CancelInventory
**Status** : ✅ Terminé

**Scénarios testés** :
- [x] Transition → CANCELLED
- [x] Aucun ajustement stock
- [x] Refuser si COMPLETED

---

#### 5.2 - 🟢 GREEN : Implémenter CancelInventory
**Status** : ✅ Terminé

**Fichiers créés** :
- [x] `src/Inventory/UseCases/CancelInventory/`
- [x] Méthode `Inventory->cancel()`

---

#### 5.3 - Adapter : Bouton Annuler
**Status** : ✅ Terminé

- [x] Confirmation modal
- [x] Intégration UI

---

### ✅ Itération 6 : Finitions
**Status** : ✅ Terminé  
**Estimation** : 1 jour

- [x] **Liste Inventaires** : Page index avec filtres (status, date) - `GetInventories`
- [x] **Détail Inventaire** : Page show avec items et écarts
- [x] **DataBuilders + Factories** (Foundry)
- [x] **QA Complète** (phpstan, cs-fixer, deptrac, coverage)

---

## 📊 Suivi de l'Avancement

### Métriques par Itération

| Itération | GitHub Issue | Status | Avancement |
|-----------|--------------|--------|------------|
| Itération 0 : Setup Infrastructure | N/A | ✅ Terminé | 100% |
| Itération 1 : Créer un Inventaire | #163 | ✅ Terminé | 100% |
| Itération 2 : Démarrer Inventaire | #167, #165 | ✅ Terminé | 100% |
| Itération 3 : Saisir Stock Réel | #164 | ✅ Terminé | 100% |
| Itération 4 : Workflow REVIEW + Finalisation | #170 | ✅ Terminé | 100% |
| Itération 5 : Annuler Inventaire | #166 | ✅ Terminé | 100% |
| Itération 6 : Finitions | N/A | ✅ Terminé | 100% |
| **TOTAL** | | | **100%** |

### Légende Status
- ⬜ À faire  
- 🔄 En cours  
- ✅ Terminé  
- ⚠️ Bloqué

### UseCases Implémentés (9 total)
1. ✅ **CreateInventory** - Créer un nouvel inventaire
2. ✅ **LoadArticlesAndStartInventory** - Charger articles et démarrer
3. ✅ **RecordRealStockForZone** - Saisir le stock réel par zone
4. ✅ **FinishCounting** - Terminer le comptage (→ REVIEW)
5. ✅ **ReviewDiscrepancies** - Réviser les écarts
6. ✅ **ResumeCountingFromReview** - Reprendre le comptage (← REVIEW)
7. ✅ **CompleteInventory** - Finaliser l'inventaire (→ COMPLETED)
8. ✅ **CancelInventory** - Annuler l'inventaire (→ CANCELLED)
9. ✅ **GetInventories** - Lister les inventaires

---

## 🎯 Critères de Succès Globaux

### Fonctionnalités Métier
- [x] Setup infrastructure (autoload, config, deptrac)
- [x] UseCase CreateInventory avec tests unitaires
- [x] Formulaire + Controller pour créer inventaire
- [x] Migration + Repository Doctrine
- [x] UseCase LoadArticlesAndStartInventory (charge articles + DRAFT → IN_PROGRESS)
- [x] UseCase RecordRealStockForZone (saisie comptage)
- [x] UseCase FinishCounting (IN_PROGRESS → REVIEW)
- [x] UseCase ReviewDiscrepancies (gestion écarts)
- [x] UseCase ResumeCountingFromReview (REVIEW → IN_PROGRESS)
- [x] UseCase CompleteInventory (ajustement stocks Article)
- [x] UseCase CancelInventory
- [x] UseCase GetInventories (page liste)
- [x] Page détail inventaire avec items

### Architecture & Qualité
- [x] Deptrac : architecture Inventory validée
- [x] PHPStan : 0 erreur
- [x] Tests unitaires : couverture > 80%
- [x] Tests fonctionnels : tous les controllers testés
- [x] Test E2E : workflow complet (create → start → count → review → complete)
- [x] CS-Fixer : code formaté

### Intégration Admin BC
- [x] Contract ArticleForInventory : interface pour récupérer articles
- [x] Contract ArticleStockUpdater : interface pour ajuster stocks
- [x] Anti-Corruption Layer : Inventory ne dépend que des UUID d'Article

---

## 🔗 Intégration avec Admin BC

### Dépendances
- Lecture : `Article` (pour theoreticalStock)
- Écriture : `Article.quantity` (ajustement lors de CompleteInventory)
- Lecture : `ZoneStorage` (référence zone de comptage)

### Anti-Corruption Layer
```php
// Inventory BC ne manipule que des UUID d'Article
// Pas de dépendance forte au domaine Admin
$articleUuid = ResourceUuid::fromString($request->articleUuid());
$article = $this->articleRepository->getByUuid($articleUuid);
```

---

## 🚀 Phase 2 (Future) : StockManagement

Après implémentation complète d'Inventory, la roadmap prévoit :

**Bounded Context StockManagement** :
- Entités : StockMovement, StockLevel
- Use Cases : RecordEntry, RecordExit, Transfer, AdjustStock
- Calcul automatique du stock théorique
- Inventory physique devient une vérification périodique

**Bénéfice** : Stock théorique calculé en continu, Inventory = correction seulement

---

## 📝 Notes de Développement

### Décisions Techniques

**Stockage des quantités** :
- En millièmes (INTEGER) pour précision
- Pattern identique à ArticleQuantity d'Admin

**Status transitions** :
- DRAFT → IN_PROGRESS (start)
- IN_PROGRESS → REVIEW (finishCounting)
- REVIEW → IN_PROGRESS (resumeCounting) ← retour pour correction
- REVIEW → COMPLETED (complete)
- DRAFT/IN_PROGRESS/REVIEW → CANCELLED (cancel)
- COMPLETED : final, pas d'annulation possible

**Ajustement stocks** :
- Uniquement lors de CompleteInventory
- Tous les écarts doivent être reviewed avant finalisation
- Agrégation multi-zones si article dans plusieurs zones
- Stratégie : Différentiel par zone `quantity += (realStock - theoreticalStock)`
- Irréversible (transaction Doctrine)
- Traçabilité dans Inventory.amount

**Chargement des articles** :
- Automatique par zone de stockage
- Un inventaire = une zone
- Tous les articles de la zone sont chargés d'office

### Points d'Attention

⚠️ **CompleteInventory** : UC le plus critique
- Doit ajuster Article.quantity dans Admin BC
- Nécessite transaction Doctrine
- Vérifier tous items ont realStock saisi

⚠️ **Gestion concurrence** :
- Un seul inventaire actif (DRAFT ou IN_PROGRESS) à la fois
- Lock optimiste sur Inventory si nécessaire

⚠️ **Performance** :
- Index sur status, date, article_uuid, zone_uuid
- Pagination pour liste inventaires

---

## 📝 Historique des Révisions

### 2025-12-14 : Refonte TDD (Révision Majeure)
**Changement d'approche** : Abandon de l'approche "Big Design Up Front" au profit d'une **vraie approche TDD itérative**.

**Avant** (approche en couches horizontales) :
- Phase 1 : Créer TOUS les VOs
- Phase 2 : Créer TOUTES les entités
- Phase 3 : Créer TOUS les use cases
- Phase 4-7 : Infrastructure, adapters, tests

❌ **Problème** : Ce n'est **pas du TDD** ! On créait du code avant les tests.

**Après** (approche TDD par itérations verticales) :
- Itération 1 : Test CreateInventory → Implémenter → Adapter → Persister → E2E
- Itération 2 : Test LoadArticles → Implémenter → Adapter → Persister → E2E
- Etc.

✅ **Avantages** :
- Code guidé par les tests (vraie approche Red/Green/Refactor)
- Pas de code inutile (YAGNI)
- Features livrables incrémentalement
- Feedback rapide à chaque itération

**Validation** : Laurent (développeur principal)

---

**Document créé le** : 2025-12-13  
**Dernière mise à jour** : 2026-01-04 (Mise à jour statuts - Ticket #198)  
**Maintenu par** : Claude Code + Laurent
