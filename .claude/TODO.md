# TODO List

## Fixtures Architecture

### Uniformiser l'enregistrement des fixtures

**Priority**: Medium
**Context**: DataFixtures consistency
**Status**: 🔴 **TO DO**

**Issue**:
Les fixtures utilisent actuellement deux stratégies différentes pour persister les entités :
1. Certaines utilisent `persist()` directement (ex: `TaxFixtures`, `UnitFixtures`)
2. D'autres utilisent les repositories (ex: `CompanyFixtures`, `ArticleFixtures`)

Ce mix de stratégies cause des problèmes lors de l'utilisation de `loadFixtures()` avec LiipTestFixturesBundle, car les entités persistées avec `persist()` ne sont pas toujours visibles par les repositories qui font des `find()` (problème de contexte d'EntityManager).

**Action recommandée**:
- Analyser toutes les fixtures dans `src/Admin/Adapters/DataFixtures/`
- Choisir une stratégie unique (recommandation : utiliser uniquement `persist()` comme dans les fixtures Doctrine standard)
- Refactoriser les fixtures pour uniformiser l'approche
- S'assurer que `ArticleFixtures` et autres fixtures complexes fonctionnent correctement avec `loadFixtures()`

**Fichiers concernés**:
- `src/Admin/Adapters/DataFixtures/TaxFixtures.php` (utilise `persist()`)
- `src/Admin/Adapters/DataFixtures/UnitFixtures.php` (utilise `persist()`)
- `src/Admin/Adapters/DataFixtures/CompanyFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/ArticleFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/FamilyLogFixtures.php` (utilise repository)
- `src/Admin/Adapters/DataFixtures/SupplierFixtures.php` (probablement repository)
- `src/Admin/Adapters/DataFixtures/ZoneStorageFixtures.php` (probablement repository)

**Bénéfices**:
- Fixtures réutilisables dans les tests E2E
- Code plus cohérent et maintenable
- Évite les bugs liés au contexte d'EntityManager

**Created**: 2025-11-29

## PHPStan Configuration

### ~~Warning: Deprecated config option `checkGenericClassInNonGenericObjectType`~~ ✅

**Priority**: ~~Medium~~ **COMPLETED**
**Context**: PHPStan analysis
**Status**: ✅ **RESOLVED on 2025-11-29**

**Issue**:
PHPStan displays a deprecation warning during analysis:
```
⚠️  You're using a deprecated config option checkGenericClassInNonGenericObjectType ⚠️️
```

**Resolution**:
Fixed all generic type annotations in Collection implementations. The issue was that all collection classes were using `@implements Collection<array-key, EntityType>` with two type parameters, but the `Collection` interface only supports one type parameter `T` (it already extends `Iterator<array-key, T>`).

**Files corrected**:
- `src/Admin/Entities/Article/ArticleCollection.php:21`
- `src/Admin/Entities/FamilyLog/FamilyLogCollection.php:21`
- `src/Admin/Entities/Supplier/SupplierCollection.php:21`
- `src/Admin/Entities/Tax/TaxCollection.php:21`
- `src/Admin/Entities/Unit/UnitCollection.php:21`
- `src/Admin/Entities/ZoneStorage/ZoneStorageCollection.php:21`
- `src/Shared/Tests/Entities/Collection/SomethingCollection.php:21`

Changed from: `@implements Collection<array-key, EntityType>`
To: `@implements Collection<EntityType>`

**Verification**: `make stan` returns no errors

**Created**: 2025-11-25
**Resolved**: 2025-11-29
