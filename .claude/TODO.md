# TODO List

**Dernière mise à jour** : 2025-12-06

---

## Domain Entities & Repository

### Récupération des FamilyLog avec leurs enfants depuis le domaine

**Priority** : Medium
**Context** : Domain Repository pattern
**Status** : 🔴 **TO DO**
**GitHub Issue** : [#113](https://github.com/Dev-Int/tests/issues/113)

**Issue** :
Actuellement, lorsqu'on récupère une `FamilyLog` via le `FamilyLogRepository` (interface du domaine), la méthode `children()` de l'entité retourne `null` au lieu de charger les enfants de l'arborescence.

Avec l'implémentation ORM (`DoctrineFamilyLogRepository`), les enfants étaient chargés automatiquement grâce aux relations Doctrine. Mais avec l'interface du domaine, ce chargement automatique n'existe pas.

**Impact** :
Plusieurs tests fonctionnels échouent, car ils s'attendent à ce que `children()` retourne un tableau :
- `AssignParentFamilyLogControllerTest::testAssignParentWithoutParentWithChildrenWillSucceed` (ligne 147)
- `ChangeLabelFamilyLogControllerTest::testChangeLabelFamilyLogWithChildrenWillSucceed` (ligne 161)

**Action recommandée** :
1. Décider d'une stratégie pour gérer les relations parent/enfant dans le domaine :
   - Option A : Ajouter une méthode `findByUuidWithChildren(ResourceUuid $uuid): FamilyLog` dans `FamilyLogRepository`
   - Option B : Charger explicitement les enfants dans le repository quand nécessaire
   - Option C : Modifier l'entité du domaine pour ne pas exposer `children()` directement (CQRS pattern)

2. Refactorer les tests concernés pour utiliser la nouvelle approche

3. S'assurer que tous les tests FamilyLog passent avec l'interface du domaine

**Fichiers concernés** :
- `src/Admin/UseCases/Gateway/FamilyLogRepository.php` (interface)
- `src/Admin/Entities/FamilyLog/FamilyLog.php` (entité domaine)
- `src/Admin/Adapters/Gateway/ORM/Repository/DoctrineFamilyLogRepository.php` (implémentation)
- `src/Admin/Tests/Adapters/Controller/Symfony/Controller/FamilyLog/AssignParentFamilyLog/AssignParentFamilyLogControllerTest.php`
- `src/Admin/Tests/Adapters/Controller/Symfony/Controller/FamilyLog/ChangeLabelFamilyLog/ChangeLabelFamilyLogControllerTest.php`

**Contexte** :
Les tests Cancel pour FamilyLog ont été refactorisés pour utiliser les interfaces du domaine, mais les tests concernant les relations parent/enfant ont été revert car ils nécessitent une solution architecturale pour le chargement des enfants.

**Created** : 2025-12-04

---

## End-to-End Tests Coverage

### État actuel de la couverture E2E

**Priority** : Low
**Context** : E2E testing coverage
**Status** : 🟢 **BONNE COUVERTURE** (améliorations optionnelles possibles)

#### ✅ Tests E2E créés et fonctionnels

**Company** :
- ✅ `CreateACompanyTest` - Création d'une entreprise
- ✅ `UpdateACompanyTest` - Modification d'une entreprise

**Article** :
- ✅ `CreateFirstArticleTest` - Création du premier article (workflow complet avec packaging)
- ✅ `CreateAnotherArticleTest` - Création d'un autre article
- ✅ `ArticlesPaginationTest` - Test de pagination (10 scénarios complets)

**Supplier** :
- ✅ `CreateFirstSupplierTest` - Création du premier fournisseur
- ✅ `CreateAnotherSupplierTest` - Création d'un autre fournisseur
- ✅ `SuppliersPaginationTest` - Test de pagination (13 scénarios complets)

**Tax** :
- ✅ `CreateFirstTaxTest` - Création de la première taxe avec cas d'annulation
- ✅ `CreateAnotherTaxTest` - Création d'une autre taxe avec cas d'annulation

**Unit** :
- ✅ `CreateFirstUnitTest` - Création de la première unité avec cas d'annulation
- ✅ `CreateAnotherUnitTest` - Création d'une autre unité avec cas d'annulation

**FamilyLog** :
- ✅ `CreateFirstFamilyLogTest` - Création de la première famille logistique avec cas d'annulation
- ✅ `CreateAnotherFamilyLogTest` - Création d'une autre famille logistique avec cas d'annulation

**ZoneStorage** :
- ✅ `CreateFirstZoneStorageTest` - Création de la première zone de stockage avec cas d'annulation
- ✅ `CreateAnotherZoneStorageTest` - Création d'une autre zone de stockage avec cas d'annulation

**Configuration Workflow** :
- ✅ `ConfigurationControllerTest` (tests fonctionnels) - 7 tests couvrant le workflow de configuration
  - Test page configuration vide (toutes les étapes actives)
  - Test redirection depuis `/admin/` vers `/admin/configure`
  - Test bouton retour vers home
  - Test déblocage progressif des étapes (Company → Unit → Tax → FamilyLog → ZoneStorage → Supplier)
  - Valide le verrouillage des étapes avec classe `disable-link`

#### 📝 Tests Cancel - Implémentés en tests fonctionnels

**Note importante** : Les tests d'annulation de formulaires (Cancel) ont été implémentés comme **tests fonctionnels** plutôt qu'E2E, ce qui est plus rapide et suffisant pour valider la logique de redirection.

**Tests fonctionnels Cancel pour entités simples** :
- ✅ Unit : 1 test Cancel (Rename)
- ✅ Tax : 2 tests Cancel (Rename, ChangeRate)
- ✅ FamilyLog : 2 tests Cancel (ChangeLabel, AssignParent)
- ✅ ZoneStorage : 2 tests Cancel (ChangeLabel, ChangeFamilyLog)

**Tests E2E Cancel pour entités complexes** (validation UX avec Turbo Frame) :
- ✅ Article : `RenameArticleCancelTest`, `ChangeFinancialInformationArticleCancelTest`, `ChangeStorageInformationArticleCancelTest`, `ReassignSupplierArticleCancelTest`
- ✅ Supplier : `RenameSupplierCancelTest`, `ChangeDomiciliationSupplierCancelTest`, `ChangeContactSupplierCancelTest`, `ChangeDeliverySpecificationsSupplierCancelTest`

**Corrections apportées** :
- Ajout de `turboFrame="_top"` sur tous les boutons Cancel pour garantir une navigation correcte hors du contexte Turbo Frame
- Fichiers corrigés : Formulaires Update pour Article, Supplier, Tax, FamilyLog, ZoneStorage
- Utilisation de `{{ 'cancel'|trans }}` au lieu de texte en dur pour la cohérence i18n
- Ajout de constantes `ROUTE_NAME` dans les 14 controllers Update concernés

#### ❌ Tests E2E manquants (optionnels)

**Article - Tests nominaux Update** (seuls les tests Cancel existent) :
- ❌ `ChangeArticleFinancialInformationTest` - Test nominal de modification prix/taxe réussie
- ❌ `ChangeArticleStorageInformationTest` - Test nominal de modification stockage réussie
- ❌ `ReAssignArticleSupplierTest` - Test nominal de réassignation fournisseur réussie
- ❌ `RenameArticleTest` - Test nominal de renommage réussi

**Article - Listing** :
- ❌ `GetArticlesTest` - Navigation dans la liste, recherche/filtres (pagination déjà testée)

**Supplier - Tests nominaux Update** (seuls les tests Cancel existent) :
- ❌ `RenameSupplierTest` - Test nominal de renommage réussi
- ❌ `ChangeDomiciliationSupplierTest` - Test nominal de modification domiciliation réussie
- ❌ `ChangeContactSupplierTest` - Test nominal de modification contact réussie
- ❌ `ChangeDeliverySpecificationsSupplierTest` - Test nominal de modification specs livraison réussie

**Supplier - Listing** :
- ❌ `GetSuppliersTest` - Navigation dans la liste, recherche (pagination déjà testée)

#### 🎯 Recommandations

**Priorité 1 - Tests nominaux Article Update** (si temps disponible) :
Les tests nominaux de modification d'Article (workflow complet qui réussit, pas juste Cancel) seraient les plus pertinents pour compléter la couverture. Cependant, ces workflows sont déjà testés au niveau fonctionnel.

**Priorité 2 - Tests nominaux Supplier Update** (optionnel) :
Moins critique car Supplier est plus simple qu'Article et déjà bien couvert par les tests fonctionnels.

**Priorité 3 - Tests de listing** (optionnel) :
`GetArticlesTest` et `GetSuppliersTest` pour la navigation/recherche (la pagination est déjà exhaustivement testée).

**Non prioritaire** :
- Tests E2E pour Tax, Unit, FamilyLog, ZoneStorage Update : les tests fonctionnels sont suffisants (CRUD simple)
- Workflow de configuration E2E : déjà couvert par `ConfigurationControllerTest` (tests fonctionnels)

**Conclusion** : La couverture E2E actuelle est **très bonne**. Les workflows critiques (création, pagination, annulation, configuration) sont tous testés. Les tests manquants concernent principalement les workflows de modification réussie qui sont déjà couverts par les tests fonctionnels.

**Fichiers concernés** :
- Tests E2E : `src/Admin/Tests/EndToEnd/**/*Test.php`
- Tests fonctionnels : `src/Admin/Tests/Adapters/Controller/**/*Test.php`
- Configuration workflow : `src/Admin/Tests/Adapters/Controller/Symfony/Controller/ConfigurationControllerTest.php`

**Created** : 2025-11-26
**Updated** : 2025-12-06

---

## Fixtures Architecture

### Uniformiser l'enregistrement des fixtures

**Priority** : Medium
**Context** : DataFixtures consistency
**Status** : 🔴 **TO DO**
**GitHub Issue** : [#110](https://github.com/Dev-Int/tests/issues/110)

**Issue** :
Les fixtures utilisent actuellement deux stratégies différentes pour persister les entités :
1. Certaines utilisent `persist()` directement (ex: `TaxFixtures`, `UnitFixtures`)
2. D'autres utilisent les repositories (ex: `CompanyFixtures`, `ArticleFixtures`)

Ce mix de stratégies cause des problèmes lors de l'utilisation de `loadFixtures()` avec LiipTestFixturesBundle, car les entités persistées avec `persist()` ne sont pas toujours visibles par les repositories qui font des `find()` (problème de contexte d'EntityManager).

**Options à explorer** :
1. **Option A** : Utiliser uniquement `persist()` comme dans les fixtures Doctrine standard
2. **Option B** : Utiliser Foundry (comme dans un autre projet)
   - Relation directe avec la DB
   - Factories intéressantes pour la génération de données
   - Meilleure gestion de l'état de la DB dans les tests
   - Simplification de la création d'objets avec des dépendances complexes

**Action recommandée** :
- Analyser toutes les fixtures dans `src/Admin/Adapters/DataFixtures/`
- Évaluer Foundry comme alternative moderne aux fixtures Doctrine classiques
- Choisir une stratégie unique (Foundry vs persist() classique)
- Refactoriser les fixtures pour uniformiser l'approche
- S'assurer que `ArticleFixtures` et autres fixtures complexes fonctionnent correctement avec `loadFixtures()`

**Fichiers concernés** :
- `src/Admin/Adapters/DataFixtures/TaxFixtures.php` (utilise `persist()`)
- `src/Admin/Adapters/DataFixtures/UnitFixtures.php` (utilise `persist()`)
- `src/Admin/Adapters/DataFixtures/CompanyFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/ArticleFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/FamilyLogFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/SupplierFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/ZoneStorageFixtures.php` (utilise repository)

**Bénéfices** :
- Fixtures réutilisables dans les tests E2E
- Code plus cohérent et maintenable
- Évite les bugs liés au contexte d'EntityManager

**Created** : 2025-11-29

---

## Routes Refactoring

### Refactorer les noms de routes en dur en constantes de controller

**Priority** : Medium
**Context** : Code maintainability and refactoring
**Status** : 🔴 **TO DO**
**GitHub Issue** : [#105](https://github.com/Dev-Int/tests/issues/105)

**Issue** :
Actuellement, plusieurs fichiers utilisent des noms de routes en dur (chaînes de caractères) au lieu de constantes définies dans les controllers. Cela rend le code moins maintenable et plus sujet aux erreurs lors de renommages de routes.

**Examples de routes en dur** :
- Tests E2E : `'admin_family_logs_index'`, `'admin_family_logs_create'`
- Tests fonctionnels : `'admin_units_index'`, `'admin_taxes_index'`
- Templates : `path('admin_family_logs_index')`, `path('admin_units_index')`

**Action recommandée** :
1. Identifier tous les fichiers utilisant des noms de routes en dur
2. Créer/vérifier que chaque controller expose une constante `ROUTE_NAME` (ex: `GetFamilyLogsController::ROUTE_NAME`)
3. Remplacer progressivement les chaînes en dur par les constantes
4. Mettre à jour les templates Twig pour utiliser les constantes via des variables passées au contexte si nécessaire

**Fichiers à auditer** :
- `src/Admin/Tests/EndToEnd/**/*Test.php`
- `src/Admin/Tests/Adapters/Controller/**/*Test.php`
- `src/Admin/Adapters/Controller/**/*Controller.php`

**Controllers ayant déjà des constantes ROUTE_NAME** :
- `ConfigurationController::ROUTE_NAME`
- `GetUnitsController::ROUTE_NAME`
- `CreateUnitController::ROUTE_NAME`
- `GetTaxesController::ROUTE_NAME`
- `CreateTaxController::ROUTE_NAME`
- `GetFamilyLogsController::ROUTE_NAME`
- `CreateFamilyLogController::ROUTE_NAME`
- `GetZoneStoragesController::ROUTE_NAME`
- `CreateZoneStorageController::ROUTE_NAME`
- `GetSuppliersController::ROUTE_NAME`
- `CreateSupplierController::ROUTE_NAME`
- 14 controllers Update (4 Supplier + 4 Article + 2 Tax + 2 FamilyLog + 2 ZoneStorage)
- (À compléter lors de l'audit)

**Bénéfices** :
- Meilleure maintenabilité du code
- Refactoring plus sûr (erreur de compilation si route renommée)
- Autocomplétion IDE
- Centralisation de la définition des routes
- Évite les typos dans les noms de routes

**Created** : 2025-11-30

---

## ✅ COMPLETED TASKS

### ~~Warning: Deprecated config option `checkGenericClassInNonGenericObjectType`~~ ✅

**Priority** : ~~Medium~~ **COMPLETED**
**Context** : PHPStan analysis
**Status** : ✅ **RESOLVED on 2025-11-29**

**Issue** :
PHPStan displays a deprecation warning during analysis:
```
⚠️  You're using a deprecated config option checkGenericClassInNonGenericObjectType ⚠️️
```

**Resolution** :
Fixed all generic type annotations in Collection implementations. The issue was that all collection classes were using `@implements Collection<array-key, EntityType>` with two type parameters, but the `Collection` interface only supports one type parameter `T` (it already extends `Iterator<array-key, T>`).

**Files corrected** :
- `src/Admin/Entities/Article/ArticleCollection.php:21`
- `src/Admin/Entities/FamilyLog/FamilyLogCollection.php:21`
- `src/Admin/Entities/Supplier/SupplierCollection.php:21`
- `src/Admin/Entities/Tax/TaxCollection.php:21`
- `src/Admin/Entities/Unit/UnitCollection.php:21`
- `src/Admin/Entities/ZoneStorage/ZoneStorageCollection.php:21`
- `src/Shared/Tests/Entities/Collection/SomethingCollection.php:21`

Changed from: `@implements Collection<array-key, EntityType>`
To: `@implements Collection<EntityType>`

**Verification** : `make stan` returns no errors

**Created** : 2025-11-25
**Resolved** : 2025-11-29

---

### ~~Tests E2E pour l'annulation de formulaires (Cancel)~~ ✅

**Priority** : ~~Medium~~ **COMPLETED**
**Context** : E2E testing coverage
**Status** : ✅ **COMPLETED on 2025-12-03**

**Objectif** :
Implémenter des tests Cancel pour toutes les opérations (Create et Update) de toutes les entités principales de configuration.

**Tests de création (Create) - ✅ COMPLÉTÉS** :
- ✅ Company : `CreateACompanyTest::testCreateACompanyCancelledDuringSeizure`
- ✅ Unit : `CreateFirstUnitTest::testCancelDuringFirstUnitCreation` + `CreateAnotherUnitTest::testCancelDuringAnotherUnitCreation`
- ✅ Tax : `CreateFirstTaxTest::testCancelDuringFirstTaxCreation` + `CreateAnotherTaxTest::testCancelDuringAnotherTaxCreation`
- ✅ FamilyLog : `CreateFirstFamilyLogTest::testCancelDuringFirstFamilyLogCreation` + `CreateAnotherFamilyLogTest::testCancelDuringAnotherFamilyLogCreation`
- ✅ ZoneStorage : `CreateFirstZoneStorageTest::testCancelDuringFirstZoneStorageCreation` + `CreateAnotherZoneStorageTest::testCancelDuringAnotherZoneStorageCreation`
- ✅ Supplier : `CreateFirstSupplierTest::testCancelDuringFirstSupplierCreation` + `CreateAnotherSupplierTest::testCancelDuringAnotherSupplierCreation`
- ✅ Article : `CreateFirstArticleTest::testCancelDuringFirstArticleCreation` + `CreateAnotherArticleTest::testCancelDuringAnotherArticleCreation`

**Tests de modification (Update) - ✅ COMPLÉTÉS** :

**Tests fonctionnels (rapides)** :
- ✅ Unit : test fonctionnel Cancel pour Rename
- ✅ Tax : tests fonctionnels Cancel pour Rename et ChangeRate
- ✅ FamilyLog : tests fonctionnels Cancel pour ChangeLabel et AssignParent
- ✅ ZoneStorage : tests fonctionnels Cancel pour ChangeLabel et ChangeFamilyLog

**Tests E2E (validation UX pour entités complexes)** :
- ✅ Supplier : `RenameSupplierCancelTest`, `ChangeDomiciliationSupplierCancelTest`, `ChangeContactSupplierCancelTest`, `ChangeDeliverySpecificationsSupplierCancelTest`
- ✅ Article : `RenameArticleCancelTest`, `ChangeFinancialInformationArticleCancelTest`, `ChangeStorageInformationArticleCancelTest`, `ReassignSupplierArticleCancelTest`

**Stratégie** :
1. **Tests fonctionnels** pour les entités simples (Unit, Tax, FamilyLog, ZoneStorage, Company) - Plus rapides et suffisants
2. **Tests E2E** pour les entités complexes (Supplier, Article) - Validation UX complète avec Turbo Frame

**Corrections apportées** :
- Ajout de `turboFrame="_top"` sur tous les boutons Cancel des formulaires
- Utilisation de `{{ 'cancel'|trans }}` au lieu de texte en dur
- Ajout de constantes `ROUTE_NAME` dans les 14 controllers Update concernés

**Created** : 2025-11-30
**Resolved** : 2025-12-03

---

### ~~Tests E2E pour la pagination des listes~~ ✅

**Priority** : ~~Medium~~ **COMPLETED**
**Context** : E2E testing coverage
**Status** : ✅ **RESOLVED on 2025-12-01**

**Objectif** :
Créer des tests E2E complets pour valider tous les aspects de la pagination sur les pages de liste des entités principales.

**Scénarios testés - Article (`ArticlesPaginationTest.php`)** :
- ✅ 10 tests complets couvrant navigation, boutons, affichage items, changement items/page, informations pagination

**Scénarios testés - Supplier (`SuppliersPaginationTest.php`)** :
- ✅ 13 tests complets couvrant navigation, boutons, affichage items, changement items/page (10/50/100), informations pagination

**Bénéfices** :
- Garantie que la pagination fonctionne correctement
- Détection précoce des régressions
- Validation du calcul du nombre de pages

**Created** : 2025-12-01
**Resolved** : 2025-12-01
