# TODO List

## PHPStan Configuration

### Warning: Deprecated config option `checkGenericClassInNonGenericObjectType`

**Priority**: Medium
**Context**: PHPStan analysis

**Issue**:
PHPStan displays a deprecation warning during analysis:
```
⚠️  You're using a deprecated config option checkGenericClassInNonGenericObjectType ⚠️️
```

**Recommended action**:
1. Remove the deprecated `checkGenericClassInNonGenericObjectType` option from `phpstan.neon`
2. Add missing generic typehints throughout the codebase
3. Alternatively, add the `missingType.generics` error identifier to `ignoreErrors` if you want to continue ignoring missing typehints from generics:

```yaml
parameters:
    ignoreErrors:
        -
            identifier: missingType.generics
```

**Reference**: PHPStan documentation on generic types

**Created**: 2025-11-25
