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
│   └── Security/      # Symfony Security (voters, etc.)
├── Contracts/         # Interfaces exposées aux autres BC
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
