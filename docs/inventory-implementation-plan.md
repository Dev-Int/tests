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

### 🔄 Itération 1 : Créer un Inventaire
**Status** : 🔄 En cours  
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
**Status** : 🔄 En cours

**Fichiers à créer** :
- [ ] `src/Inventory/Adapters/Form/Type/CreateInventoryType.php`
  - Champs : `date` (DateType), `zoneStorageId` (ChoiceType)
- [ ] `src/Inventory/Adapters/Controller/Symfony/Controller/CreateInventory/CreateInventoryController.php`
  - Route : `/inventory/create` (GET/POST)
  - Flash message succès/erreur
- [ ] `src/Inventory/Adapters/Controller/Symfony/Controller/CreateInventory/CreateInventoryApiRequest.php`
  - Implémenter `CreateInventoryRequest`
  - Mapper form → request
- [ ] `src/Inventory/Frameworks/templates/inventory/create.html.twig`
- [ ] Test fonctionnel : `src/Inventory/Tests/Adapters/Controller/CreateInventoryControllerTest.php`

**Critères de validation** :
- [ ] Formulaire affiché sans erreur
- [ ] Soumission valide crée l'inventaire
- [ ] Erreurs métier affichées correctement
- [ ] Flash message après création
- [ ] Redirection vers page détail (ou liste)

---

#### 1.4 - Persistence : Migration + Repository
**Status** : ⬜ À faire

**Fichiers à créer** :
- [ ] Migration `Version20251214120000.php`
  - Table `inventory` (uuid, date, status, amount, zone_id, created_at, updated_at)
- [ ] `src/Inventory/Adapters/Gateway/ORM/Entity/Inventory.php`
  - Mapping Doctrine
  - Méthodes `fromDomain()` / `toDomain()`
- [ ] `src/Inventory/Adapters/Gateway/ORM/Repository/DoctrineInventoryRepository.php`
  - Implémenter `save()`, `getByUuid()`, `hasActiveForZone()`
- [ ] Test : `src/Inventory/Tests/Adapters/Gateway/ORM/Repository/DoctrineInventoryRepositoryTest.php`

**Commandes** :
```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
bin/console doctrine:schema:validate
```

**Critères de validation** :
- [ ] Migration exécutée sans erreur
- [ ] Repository persiste et récupère correctement
- [ ] `hasActiveForZone()` fonctionne
- [ ] Tests fonctionnels passent

---

#### 1.5 - 🔵 REFACTOR : Optimisation
**Status** : ⬜ À faire

**Actions** :
- [ ] Extraire VOs si répétition (InventoryDate ?)
- [ ] Optimiser requêtes repository
- [ ] Nettoyer imports inutiles
- [ ] Vérifier couverture tests > 80%

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
**Status** : ⬜ À faire

**Fichiers à créer** :
- [ ] Controller (bouton "Démarrer l'inventaire")
- [ ] Template (confirmation + affichage résultat)
- [ ] Flash message succès/erreur

---

#### 2.4 - Persistence : Table inventory_item
**Status** : ⬜ À faire

**Actions** :
- [ ] Migration pour `inventory_item`
- [ ] ORM mapping
- [ ] Repository implementation

---

### ⬜ Itération 3 : Saisir Stock Réel
**Status** : ⬜ À faire  
**GitHub Issue** : #164  
**Estimation** : 1 jour

#### 4.1 - 🔴 RED : Test RecordRealStockForZone
- Enregistrer realStock
- Calculer différence automatiquement
- Refuser si status ≠ IN_PROGRESS
- Refuser si realStock < 0

---

#### 4.2 - 🟢 GREEN : Implémenter RecordRealStockForZone
- VO RealStock avec validation
- VO StockDifference calculé
- Méthode `InventoryItem->recordRealStock()`

---

#### 4.3 - Adapter : Formulaire Saisie Stock
- Liste items avec formulaire inline
- Sauvegarde AJAX (optionnel)
- Affichage écart en temps réel

---

### ⬜ Itération 4 : Finaliser l'Inventaire (CRITIQUE)
**Status** : ⬜ À faire  
**GitHub Issue** : #170  
**Estimation** : 2 jours

#### 5.1 - 🔴 RED : Test CompleteInventory
- Transition IN_PROGRESS → COMPLETED
- Ajuster Article.quantity dans Admin
- Calculer amount (écarts valorisés)
- Refuser si items non comptés

---

#### 5.2 - 🟢 GREEN : Implémenter CompleteInventory
- Méthode `Inventory->complete()`
- Agrégation multi-zones (différentiel)
- Transaction Doctrine pour ajustement stocks

**Intégration Admin** :
- Créer `Article->adjustQuantity(difference)` dans Admin BC

---

#### 5.3 - Adapter : Bouton Finaliser + Confirmation
- Modal confirmation avec récap écarts
- Affichage amount total
- Workflow irreversible (status COMPLETED)

---

#### 5.4 - E2E : Workflow Complet
**Fichier** : `src/Inventory/Tests/EndToEnd/CompleteInventoryWorkflowE2ETest.php`

**Scénario** :
1. Créer inventaire
2. Charger 3 articles
3. Démarrer
4. Saisir stocks réels (avec écarts)
5. Finaliser
6. Vérifier Article.quantity ajusté dans Admin

---

### ⬜ Itération 5 : Annuler un Inventaire
**Status** : ⬜ À faire  
**GitHub Issue** : #166  
**Estimation** : 0.5 jour

#### 6.1 - 🔴 RED : Test CancelInventory
- Transition → CANCELLED
- Aucun ajustement stock
- Refuser si COMPLETED

---

#### 6.2 - 🟢 GREEN : Implémenter CancelInventory
- Méthode `Inventory->cancel()`

---

#### 6.3 - Adapter : Bouton Annuler
- Confirmation modal
- Gestion permissions (admin seulement ?)

---

### ⬜ Itération 6 : Finitions
**Status** : ⬜ À faire  
**Estimation** : 1 jour

- [ ] **Liste Inventaires** : Page index avec filtres (status, date)
- [ ] **Détail Inventaire** : Page show avec items et écarts
- [ ] **DataBuilders + Factories** (Foundry)
- [ ] **Documentation** (`docs/inventory-implementation.md`)
- [ ] **QA Complète** (phpstan, cs-fixer, deptrac, coverage)

---

## 📊 Suivi de l'Avancement

### Métriques par Itération

| Itération | GitHub Issue | Status | Étapes | Avancement |
|-----------|--------------|--------|--------|------------|
| Itération 0 : Setup Infrastructure | N/A | ✅ Terminé | 1/1 | 100% |
| Itération 1 : Créer un Inventaire | #163 | 🔄 En cours | 2/5 | 40% |
| Itération 2 : Démarrer Inventaire | #167, #165 | ✅ Terminé | 2/4 | 50% |
| Itération 3 : Saisir Stock Réel | #164 | ⬜ À faire | 0/3 | 0% |
| Itération 4 : Finaliser (CRITIQUE) | #170 | ⬜ À faire | 0/4 | 0% |
| Itération 5 : Annuler Inventaire | #166 | ⬜ À faire | 0/3 | 0% |
| Itération 6 : Finitions | N/A | ⬜ À faire | 0/5 | 0% |
| **TOTAL** | | | **5/25** | **20%** |

### Légende Status
- ⬜ À faire
- 🔄 En cours
- ✅ Terminé
- ⚠️ Bloqué

### Prochaines Actions
1. ✅ ~~Test CreateInventory UseCase~~ (Terminé)
2. ✅ ~~Implémenter CreateInventory~~ (Terminé)
3. 🔄 **Formulaire + Controller CreateInventory** (En cours - Laurent)
4. ⬜ Migration + Repository Doctrine
5. ⬜ Tests E2E CreateInventory

---

## 🎯 Critères de Succès Globaux

### Fonctionnalités Métier
- [x] Setup infrastructure (autoload, config, deptrac)
- [x] UseCase CreateInventory avec tests unitaires
- [ ] Formulaire + Controller pour créer inventaire
- [ ] Migration + Repository Doctrine
- [x] UseCase StartInventory (charge articles + DRAFT → IN_PROGRESS)
- [ ] UseCase RecordRealStockForZone (saisie comptage)
- [ ] UseCase CompleteInventory (ajustement stocks Article)
- [ ] UseCase CancelInventory
- [ ] Page liste inventaires
- [ ] Page détail inventaire avec items

### Architecture & Qualité
- [x] Deptrac : architecture Inventory validée
- [x] PHPStan : 0 erreur pour code existant
- [ ] Tests unitaires : couverture > 80%
- [ ] Tests fonctionnels : tous les controllers testés
- [ ] Test E2E : workflow complet (create → load → start → count → complete)
- [ ] CS-Fixer : code formaté
- [ ] Documentation : `docs/inventory-implementation.md` créée
- [ ] CLAUDE.md : section Inventory ajoutée

### Intégration Admin BC
- [ ] ArticleRepository : méthode `findByZone()` ajoutée
- [ ] Article : méthode `adjustQuantity()` créée (pour ajustement stock)
- [ ] Anti-Corruption Layer : Inventory ne dépend que des UUID d'Article

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
**Dernière mise à jour** : 2025-12-14 (Refonte TDD)  
**Maintenu par** : Claude Code + Laurent
