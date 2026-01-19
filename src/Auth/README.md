# Auth Bounded Context

Gestion de l'authentification et des utilisateurs.

## Structure

```
Auth/
├── Adapters/          # Implémentations techniques
│   ├── Contracts/     # Implémentation des contrats inter-BC
│   ├── Controller/    # Controllers HTTP
│   ├── DataFixtures/  # Fixtures pour tests
│   ├── Gateway/       # Repositories Doctrine
│   ├── Security/      # Symfony Security (voters, etc.)
│   └── Service/       # Services techniques (AttributeResolver)
├── Contracts/         # Interfaces exposées aux autres BC
│   ├── Attribute/     # Attributs de sécurité exposés
│   │   ├── RequireAuthenticated.php
│   │   └── RequireRole.php
│   ├── CurrentUserProvider.php
│   ├── DTO/           # Data Transfer Objects
│   └── Exception/     # Exceptions publiques
├── Entities/          # Domaine métier
│   ├── User.php
│   ├── Role.php
│   └── VO/            # Value Objects
├── UseCases/          # Logique applicative
└── Tests/             # Tests (Unit, Functional, E2E)
```

## Contrats exposés

Le BC Auth expose `CurrentUserProvider` pour permettre aux autres BC d'accéder à l'utilisateur connecté.

```php
interface CurrentUserProvider
{
    public function getCurrentUser(): ?CurrentUserDTO;
    public function isAuthenticated(): bool;
    public function hasRole(string $role): bool;
}
```

## Attributs de sécurité

Le BC Auth expose des attributs PHP pour protéger les routes des autres BC.

### RequireAuthenticated

Vérifie que l'utilisateur est authentifié, sinon redirige vers le login.

```php
use Auth\Contracts\Attribute\RequireAuthenticated;

#[RequireAuthenticated]  // Défaut: redirige vers 'auth_login'
class MyController { }

#[RequireAuthenticated(redirectRoute: 'custom_login', flashMessage: 'Veuillez vous connecter')]
class CustomController { }
```

### RequireRole

Vérifie qu'un utilisateur a un rôle spécifique (respecte la hiérarchie Symfony).

```php
use Auth\Contracts\Attribute\RequireRole;

#[RequireRole(role: 'ROLE_INVENTORY_MANAGER')]
class InventoryAdminController { }
```

> **Note** : `RequireRole` vérifie d'abord l'authentification. Si non authentifié, redirige vers le login. Si authentifié mais sans le rôle, lance `AccessDeniedException` (403).

---

## Notes techniques

### Optimisation future : memoization de `getCurrentUser()`

> **Priorité** : Basse (pas de problème fonctionnel actuel)

Dans `AuthCurrentUserProvider`, les méthodes `hasRole()` et `isAuthenticated()` appellent chacune `getCurrentUser()`, qui relit le token depuis `TokenStorageInterface` à chaque appel.

**Impact actuel** : Négligeable (lecture mémoire simple).

**Amélioration possible** : Si ces méthodes sont appelées en boucle (ex: vérification de permissions sur une liste), envisager une memoization :

```php
private ?CurrentUserDTO $memoizedUser = null;
private bool $userFetched = false;

public function getCurrentUser(): ?CurrentUserDTO
{
    if (!$this->userFetched) {
        $this->memoizedUser = $this->fetchCurrentUser();
        $this->userFetched = true;
    }
    return $this->memoizedUser;
}
```

**À surveiller** : Lors du profiling futur, vérifier si `getCurrentUser()` apparaît dans les hotspots.

### Optimisation future : cache des attributs Reflection

> **Priorité** : Basse (acceptable pour trafic modéré)

Le service `AttributeResolver` utilise `ReflectionClass` + `getAttributes()` à chaque requête pour lire les attributs de sécurité.

**Impact actuel** : Négligeable (PHP met en cache les metadata de Reflection en interne).

**Amélioration possible** : Si le trafic augmente significativement, décorer `AttributeResolver` avec un cache APCu :

```php
final class CachedAttributeResolver
{
    public function __construct(
        private AttributeResolver $inner,
    ) {}

    public function resolve(array|callable $controller, string $attributeClass): ?object
    {
        $cacheKey = $this->buildCacheKey($controller, $attributeClass);
        if (apcu_exists($cacheKey)) {
            return apcu_fetch($cacheKey);
        }

        $attribute = $this->inner->resolve($controller, $attributeClass);
        apcu_store($cacheKey, $attribute);
        return $attribute;
    }
}
```

**À surveiller** : Lors du profiling futur, vérifier si `AttributeResolver::resolve()` apparaît dans les hotspots.
