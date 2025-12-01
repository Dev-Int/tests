# TODO List

## Fixtures Architecture

### Uniformiser l'enregistrement des fixtures

**Priority** : Medium
**Context** : DataFixtures consistency
**Status** : 🔴 **TO DO**

**Issue** :
Les fixtures utilisent actuellement deux stratégies différentes pour persister les entités :
1. Certaines utilisent `persist()` directement (ex: `TaxFixtures`, `UnitFixtures`)
2. D'autres utilisent les repositories (ex: `CompanyFixtures`, `ArticleFixtures`)

Ce mix de stratégies cause des problèmes lors de l'utilisation de `loadFixtures()` avec LiipTestFixturesBundle, car les entités persistées avec `persist()` ne sont pas toujours visibles par les repositories qui font des `find()` (problème de contexte d'EntityManager).

**Action recommandée** :
- Analyser toutes les fixtures dans `src/Admin/Adapters/DataFixtures/`
- Choisir une stratégie unique (recommandation : utiliser uniquement `persist()` comme dans les fixtures Doctrine standard)
- Refactoriser les fixtures pour uniformiser l'approche
- S'assurer que `ArticleFixtures` et autres fixtures complexes fonctionnent correctement avec `loadFixtures()`

**Fichiers concernés** :
- `src/Admin/Adapters/DataFixtures/TaxFixtures.php` (utilise `persist()`)
- `src/Admin/Adapters/DataFixtures/UnitFixtures.php` (utilise `persist()`)
- `src/Admin/Adapters/DataFixtures/CompanyFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/ArticleFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/FamilyLogFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/SupplierFixtures.php` (probablement repository)
- `src/Admin/Adapters/DataFixtures/ZoneStorageFixtures.php` (probablement repository)

**Bénéfices** :
- Fixtures réutilisables dans les tests E2E
- Code plus cohérent et maintenable
- Évite les bugs liés au contexte d'EntityManager

**Created** : 2025-11-29

## PHPStan Configuration

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

## Routes Refactoring

### Refactorer les noms de routes en dur en constantes de controller

**Priority** : Medium
**Context** : Code maintainability and refactoring
**Status** : 🔴 **TO DO**

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
- (À compléter lors de l'audit)

**Bénéfices** :
- Meilleure maintenabilité du code
- Refactoring plus sûr (erreur de compilation si route renommée)
- Autocomplétion IDE
- Centralisation de la définition des routes
- Évite les typos dans les noms de routes

**Created** : 2025-11-30

## End-to-End Tests Coverage

### Tests E2E pour l'annulation de formulaires (Cancel)

**Priority** : Medium
**Context** : E2E testing coverage
**Status** : 🟡 **IN PROGRESS**

**Issue** :
Les tests E2E pour les cas d'annulation (bouton Cancel) lors de la saisie de formulaires ne sont pas complets pour toutes les opérations sur toutes les entités.

**Objectif** :
Implémenter des tests Cancel pour toutes les opérations (Create et Update) de toutes les entités principales de configuration.

**Tests de création (Create) - ✅ COMPLÉTÉS** :
- ✅ Company: `CreateACompanyTest::testCreateACompanyCancelledDuringSeizure`
- ✅ Unit: `CreateFirstUnitTest::testCancelDuringFirstUnitCreation` + `CreateAnotherUnitTest::testCancelDuringAnotherUnitCreation`
- ✅ Tax: `CreateFirstTaxTest::testCancelDuringFirstTaxCreation` + `CreateAnotherTaxTest::testCancelDuringAnotherTaxCreation`
- ✅ FamilyLog: `CreateFirstFamilyLogTest::testCancelDuringFirstFamilyLogCreation` + `CreateAnotherFamilyLogTest::testCancelDuringAnotherFamilyLogCreation`
- ✅ ZoneStorage: `CreateFirstZoneStorageTest::testCancelDuringFirstZoneStorageCreation` + `CreateAnotherZoneStorageTest::testCancelDuringAnotherZoneStorageCreation`
- ✅ Supplier: `CreateFirstSupplierTest::testCancelDuringFirstSupplierCreation` + `CreateAnotherSupplierTest::testCancelDuringAnotherSupplierCreation`
- ✅ Article: `CreateFirstArticleTest::testCancelDuringFirstArticleCreation` + `CreateAnotherArticleTest::testCancelDuringAnotherArticleCreation`

**Tests de modification (Update) - 🔴 À FAIRE** :

**Tests fonctionnels (rapides)** :
- ✅ Company : `RenameCompanyTest::testCancelDuringRenameCompany`
- 🔴 Unit : test fonctionnel Cancel pour Rename
- 🔴 Tax : tests fonctionnels Cancel pour Rename et ChangeRate
- 🔴 FamilyLog : tests fonctionnels Cancel pour Rename et ChangeParent
- 🔴 ZoneStorage : tests fonctionnels Cancel pour Rename et ChangeFamilyLog

**Tests E2E (validation UX pour entités complexes)** :
- 🔴 Supplier : tests E2E Cancel pour Rename, ChangeDomiciliation, ChangeContact, ChangeDeliverySpecifications
- 🔴 Article : tests E2E Cancel pour Rename, ChangeFinancialInformation, ChangeStorageInformation, ReassignSupplier

**Stratégie** :
1. **Tests fonctionnels** pour les entités simples (Unit, Tax, FamilyLog, ZoneStorage, Company) :
   - Plus rapides et suffisants pour tester la logique de redirection
   - Vérifier qu'aucune donnée n'est persistée
   - Confirmer que l'annulation redirige vers la bonne URL
2. **Tests E2E** pour les entités complexes (Supplier, Article) :
   - Valider l'expérience utilisateur complète avec Turbo Frame
   - Détecter les problèmes de navigation (`turboFrame="_top"` manquant)
   - S'assurer que les boutons Cancel ont bien `turboFrame="_top"` dans les templates

**Corrections déjà apportées** :
- Ajout de `turboFrame="_top"` sur tous les boutons Cancel des formulaires Article pour garantir une navigation correcte hors du contexte Turbo Frame
- Fichiers corrigés : Article `CreateForm`, `ChangeStorageInformationForm`, `ReassignSupplierForm`, `RenameForm`, `ChangeFinancialInformationForm`

**Bénéfices** :
- Couverture complète des scénarios d'annulation pour toutes les opérations
- Détection précoce des bugs de navigation Turbo Frame
- Garantie que les utilisateurs peuvent annuler une saisie en cours sans effet de bord

**Created** : 2025-11-30
**Updated** : 2025-12-01

### Tests E2E pour la pagination des listes

**Priority** : Medium
**Context** : E2E testing coverage
**Status** : 🔴 **TO DO**

**Issue** :
Les tests E2E pour la pagination des listes d'articles et de fournisseurs n'existent pas encore. La pagination est implémentée avec 25 items par page par défaut, et il est important de valider que la navigation entre les pages fonctionne correctement.

**Objectif** :
Créer des tests E2E complets pour valider tous les aspects de la pagination sur les pages de liste des entités principales.

**Scénarios à tester - Article (`ArticlesPaginationTest.php`)** :

**Navigation entre pages** :
- 🔴 `testNavigateToSecondPage` : Naviguer vers la page 2
- 🔴 `testNavigateToThirdPage` : Naviguer vers la page 3
- 🔴 `testNavigateToLastPage` : Naviguer vers la dernière page
- 🔴 `testNavigateBackToFirstPage` : Retourner à la page 1 depuis une autre page

**Boutons de navigation** :
- 🔴 `testNextButtonNavigation` : Utiliser le bouton "Suivant" pour naviguer
- 🔴 `testPreviousButtonNavigation` : Utiliser le bouton "Précédent" pour naviguer
- 🔴 `testNextButtonDisabledOnLastPage` : Vérifier que "Suivant" est désactivé sur la dernière page
- 🔴 `testPreviousButtonDisabledOnFirstPage` : Vérifier que "Précédent" est désactivé sur la première page

**Affichage des items** :
- 🔴 `testCorrectNumberOfItemsPerPage` : Vérifier qu'il y a bien 25 items par page (ou moins sur la dernière page)
- 🔴 `testCorrectItemsDisplayedOnEachPage` : Vérifier que les bons articles sont affichés sur chaque page
- 🔴 `testLastPageWithPartialItems` : Vérifier l'affichage correct de la dernière page avec moins de 25 items

**Changement du nombre d'items par page** (si implémenté) :
- 🔴 `testChangeItemsPerPageTo10` : Changer le nombre d'items par page à 10
- 🔴 `testChangeItemsPerPageTo50` : Changer le nombre d'items par page à 50
- 🔴 `testChangeItemsPerPageTo100` : Changer le nombre d'items par page à 100

**Informations de pagination** :
- 🔴 `testPaginationInfoDisplay` : Vérifier l'affichage des informations "X-Y sur Z items"
- 🔴 `testTotalPagesCalculation` : Vérifier que le nombre total de pages est correct

**Scénarios à tester - Supplier (`SuppliersPaginationTest.php`)** :

**Navigation entre pages** :
- 🔴 `testNavigateToSecondPage` : Naviguer vers la page 2
- 🔴 `testNavigateToThirdPage` : Naviguer vers la page 3
- 🔴 `testNavigateToLastPage` : Naviguer vers la dernière page
- 🔴 `testNavigateBackToFirstPage` : Retourner à la page 1 depuis une autre page

**Boutons de navigation** :
- 🔴 `testNextButtonNavigation` : Utiliser le bouton "Suivant" pour naviguer
- 🔴 `testPreviousButtonNavigation` : Utiliser le bouton "Précédent" pour naviguer
- 🔴 `testNextButtonDisabledOnLastPage` : Vérifier que "Suivant" est désactivé sur la dernière page
- 🔴 `testPreviousButtonDisabledOnFirstPage` : Vérifier que "Précédent" est désactivé sur la première page

**Affichage des items** :
- 🔴 `testCorrectNumberOfItemsPerPage` : Vérifier qu'il y a bien 25 items par page (ou moins sur la dernière page)
- 🔴 `testCorrectItemsDisplayedOnEachPage` : Vérifier que les bons fournisseurs sont affichés sur chaque page
- 🔴 `testLastPageWithPartialItems` : Vérifier l'affichage correct de la dernière page avec moins de 25 items

**Changement du nombre d'items par page** (si implémenté) :
- 🔴 `testChangeItemsPerPageTo10` : Changer le nombre d'items par page à 10
- 🔴 `testChangeItemsPerPageTo50` : Changer le nombre d'items par page à 50
- 🔴 `testChangeItemsPerPageTo100` : Changer le nombre d'items par page à 100

**Informations de pagination** :
- 🔴 `testPaginationInfoDisplay` : Vérifier l'affichage des informations "X-Y sur Z items"
- 🔴 `testTotalPagesCalculation` : Vérifier que le nombre total de pages est correct

**Prérequis pour les tests** :
1. Créer suffisamment d'articles/fournisseurs pour avoir au moins 3-4 pages (75-100 items)
2. Utiliser `createMinimalConfiguration()` comme base
3. Ajouter les articles/fournisseurs via des boucles dans le test
4. Donner des noms identifiables aux items pour vérifier qu'ils apparaissent sur les bonnes pages

**Action recommandée** :
1. Examiner le template de pagination pour identifier les sélecteurs CSS/ARIA
2. Créer `ArticlesPaginationTest.php` dans `src/Admin/Tests/EndToEnd/Article/`
3. Créer `SuppliersPaginationTest.php` dans `src/Admin/Tests/EndToEnd/Supplier/`
4. Implémenter les tests un par un en suivant la liste ci-dessus
5. Mettre à jour cette TODO au fur et à mesure de l'avancement

**Bénéfices** :
- Garantie que la pagination fonctionne correctement pour les utilisateurs
- Détection précoce des régressions sur la navigation
- Validation du calcul du nombre de pages
- Assurance que tous les items sont accessibles via la pagination

**Created** : 2025-12-01
