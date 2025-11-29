# TODO List

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
