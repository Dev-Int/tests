# Architecture des Bounded Contexts

Ce document explique comment les bounded contexts (BC) communiquent entre eux dans le projet, en respectant les principes du Domain-Driven Design (DDD) et de la Clean Architecture.

## Structure d'un Bounded Context

Chaque bounded context suit cette structure en couches :

```
src/{BoundedContext}/
├── Contracts/           # Interfaces exposées aux autres BC (optionnel)
├── Entities/            # Entités et Value Objects du domaine
├── UseCases/            # Cas d'utilisation (logique métier)
│   └── Gateway/         # Interfaces des repositories (ports)
├── Adapters/            # Implémentations techniques
│   ├── Controller/      # Contrôleurs Symfony
│   ├── Form/           # Formulaires Symfony
│   └── Gateway/        # Implémentations des repositories
├── Frameworks/          # Configuration framework-specific
└── Tests/              # Tests du module
```

## Règles de Dépendances (Enforced by Deptrac)

### Au sein d'un BC

- **Entities** : Peut dépendre uniquement de `Shared\Entities`
- **UseCases** : Peut dépendre de `Entities` et `Shared\Entities`
- **Adapters** : Peut dépendre de `Entities`, `UseCases`, `Shared\Entities`, `Shared\Adapters`

**Principe fondamental** : Les couches internes (Entities, UseCases) ne doivent JAMAIS dépendre des couches externes (Adapters, Frameworks).

### Entre Bounded Contexts

Un bounded context **NE DOIT PAS** dépendre directement de la couche `Entities` ou `UseCases` d'un autre BC.

**❌ Interdit** :
```php
// Dans Inventory\Adapters
use Admin\Entities\Repository\ZoneStorageRepository;  // ❌
use Admin\Entities\Exception\ArticleNotFound;         // ❌
```

**✅ Autorisé** :
```php
// Dans Inventory\Adapters
use Admin\Contracts\ZoneStorageFinder;    // ✅ Interface
use Admin\Adapters\Gateway\SomeService;   // ✅ Si vraiment nécessaire
```

## Pattern Contracts : Communication entre BC

Pour permettre à un BC d'utiliser les fonctionnalités d'un autre BC sans créer de couplage fort, nous utilisons le pattern **Contracts**.

### Principe

1. Le BC **exposant** (ex: `Admin`) définit des **interfaces** dans `{BC}\Contracts\`
2. Le BC **exposant** implémente ces interfaces dans `{BC}\Adapters\`
3. Le BC **consommateur** (ex: `Inventory`) dépend uniquement des **interfaces** (Contracts)
4. Symfony injecte automatiquement l'implémentation via le container DI

### Exemple : Admin expose des services à Inventory

#### 1. Admin définit ses Contracts

```php
// src/Admin/Contracts/ArticleFinder.php
namespace Admin\Contracts\Services\Provider;

use Admin\Contracts\Services\Provider\Article\Result\Article;
use Admin\Contracts\Services\Provider\Exception\ArticleNotFound;

interface ArticleProvider
{
    /**
     * @throws ArticleNotFound
     */
    public function provide(string $uuid): Article;

    /**
     * @throws ArticleNotFound
     * @return iterable<Article>
     */
    public function provideAll(?array $ids = null): iterable;
}
```

#### 2. Inventory utilise le Contract

```php
// src/Inventory/Adapters/Gateway/ORM/InventoryMapper.php
namespace Inventory\Adapters\Gateway\ORM;

use Admin\Contracts\Services\Provider\Article\ArticleProvider;
use Admin\Contracts\Services\Provider\Exception\ArticleNotFound;

final readonly class InventoryMapper
{
    public function __construct(
        private ArticleProvider $articleProvider,  // ✅ Dépend de l'interface
    ) {}

    public function fromDomain(InventoryDomain $inventory): Inventory
    {
        try {
            $article = $this->articleProvider->find($articleUuid);
        } catch (ArticleNotFound $exception) {
            // Gérer l'exception ou la propager
        }
        // ...
    }
}
```

### Configuration Deptrac

Pour autoriser la dépendance vers les Contracts, mettre à jour le deptrac :

```yaml
# src/Inventory/Frameworks/deptrac.yaml
deptrac:
    ruleset:
        Inventory\Adapters:
            - Inventory\Entities
            - Inventory\UseCases
            - Shared\Entities
            - Shared\Adapters
            - Admin\Contracts      # ✅ Autorise UNIQUEMENT les Contracts
```

## Pattern Provider : Exposition de données pour les TwigComponents

### Principe

Pour permettre à un TwigComponent d'un BC (ex: `Inventory`) d'afficher des données d'un autre BC (ex: `Admin\ZoneStorage`) dans un select, nous utilisons un **Provider** qui expose les données via un Contract.

### Architecture des TwigComponents par BC

Les TwigComponents sont organisés par bounded context :
- **`BC\Twig\Components`** : Components spécifiques au BC avec dépendances métier
- **`src/Twig/Components`** : Components génériques sans connexion aux BC (Icon, etc.)

### Exemple : Select ZoneStorage dans Inventory

#### 1. Admin définit le Contract du Provider

```php
// src/Admin/Contracts/ZoneStorageProvider.php
namespace Admin\Contracts;

interface ZoneStorageProvider
{
    /**
     * @return array<string, string> [uuid => label]
     */
    public function getAllForChoice(): array;
}
```

#### 2. Admin implémente le Provider

```php
// src/Admin/Adapters/Contracts/AdminZoneStorageProvider.php
namespace Admin\Adapters\Contracts;

use Admin\Contracts\ZoneStorageProvider;
use Admin\UseCases\Gateway\Finder\ZoneStorageFinder;

final readonly class AdminZoneStorageProvider implements ZoneStorageProvider
{
    public function __construct(
        private ZoneStorageFinder $finder
    ) {}

    public function getAllForChoice(): array
    {
        $zoneStorages = $this->finder->findAll();

        $choices = [];
        foreach ($zoneStorages as $zoneStorage) {
            $choices[$zoneStorage->getUuid()->toString()] = $zoneStorage->getLabel();
        }

        return $choices;
    }
}
```

#### 3. Inventory crée son TwigComponent qui utilise le Provider

```php
// src/Inventory/Twig/Components/Form/ZoneStorageChoice.php
namespace Inventory\Twig\Components\Form;

use Admin\Contracts\ZoneStorageProvider;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Inventory:Form:ZoneStorageChoice')]
final readonly class ZoneStorageChoice
{
    public function __construct(
        private ZoneStorageProvider $provider,  // ✅ Dépend du Contract
    ) {}

    public function getChoices(): array
    {
        return $this->provider->getAllForChoice();
    }
}
```

```twig
{# src/Inventory/Frameworks/templates/components/Form/ZoneStorageChoice.html.twig #}
<select name="zoneStorageUuid" class="form-control">
    <option value="">{{ 'select_zone_storage'|trans }}</option>
    {% for uuid, label in this.choices %}
        <option value="{{ uuid }}">{{ label }}</option>
    {% endfor %}
</select>
```

#### 4. Utilisation dans un formulaire Inventory

```twig
{# Dans un template de formulaire Inventory #}
<div class="form-group">
    <label>{{ 'zone_storage'|trans }}</label>
    <twig:Inventory:Form:ZoneStorageChoice />
</div>
```

### Avantages du pattern Provider

- ✅ **Séparation des responsabilités** : Chaque BC gère ses propres TwigComponents
- ✅ **Couplage faible** : Communication uniquement via Contracts
- ✅ **Réutilisabilité** : Le Provider peut être utilisé par plusieurs components
- ✅ **Testabilité** : Facile de mocker le Provider dans les tests
- ✅ **Architecture claire** : Les TwigComponents restent dans leur BC respectif

### Règles importantes

1. **Organisation des fichiers** :
   - Provider Contract dans `BC\Contracts\`
   - Provider implémentation dans `BC\Adapters\Contracts\`
   - TwigComponent dans `BC\Twig\Components\`
   - Template dans `BC\Frameworks\templates\components\`

2. **Nommage** :
   - Contract : `{Entity}Provider` (ex: `ZoneStorageProvider`)
   - Implémentation : `Admin{Entity}Provider` (ex: `AdminZoneStorageProvider`)
   - Component : `{Entity}Choice` (ex: `ZoneStorageChoice`)

3. **Deptrac** :
   ```yaml
   # src/Inventory/Frameworks/deptrac.yaml
   Inventory\Twig:
       - Admin\Contracts  # ✅ Autorise les Providers
   ```

### Cas d'usage typiques

**Select simple** :
```php
interface EntityProvider {
    public function getAllForChoice(): array;
}
```

**Select avec filtrage** :
```php
interface EntityProvider {
    public function getActiveForChoice(): array;
    public function getByTypeForChoice(string $type): array;
}
```

**Select avec données enrichies** :
```php
interface EntityProvider {
    /** @return array<EntityChoiceData> */
    public function getAllForChoice(): array;
}

final readonly class EntityChoiceData {
    public function __construct(
        public string $uuid,
        public string $label,
        public ?string $description = null,
    ) {}
}
```

## Gestion des Exceptions entre BC

Deux approches possibles :

### Approche 1 : Exceptions dans les Contracts (Recommandée)

Les exceptions font partie du contrat entre BC.

```php
// src/Admin/Contracts/Exception/ArticleNotFoundException.php
namespace Admin\Contracts\Exception;

final class ArticleNotFoundException extends \DomainException {}
```

**Avantages** :
- ✅ Exception fait partie de l'API publique du BC
- ✅ Le BC consommateur peut catcher spécifiquement
- ✅ Couplage explicite et contrôlé

### Approche 2 : Exceptions spécifiques au contexte

Chaque BC définit ses propres exceptions et mappe les exceptions externes.

```php
// src/Inventory/Entities/Exception/ArticleNotAvailable.php
namespace Inventory\Entities\Exception;

final class ArticleNotAvailable extends \DomainException {}

// Dans Inventory\Adapters
try {
    $article = $this->articleFinder->find($uuid);
} catch (Admin\Contracts\Exception\ArticleNotFoundException $e) {
    throw ArticleNotAvailable::fromUuid($uuid);  // Mapping
}
```

**Avantages** :
- ✅ Vocabulaire ubiquitaire propre à chaque BC
- ✅ Isolation totale des BC
- ❌ Nécessite du mapping

## Anti-Patterns à Éviter

### ❌ Dépendance directe vers Admin\Entities

```php
// Inventory\Adapters\Controller
use Admin\Entities\Repository\ZoneStorageRepository;  // ❌ INTERDIT
use Admin\Entities\Exception\ArticleNotFound;         // ❌ INTERDIT
```

**Pourquoi ?** Viole la séparation des BC et crée un couplage fort.

### ❌ Shared Kernel trop large

```php
// Shared\Entities\Article.php  // ❌ MAUVAISE IDÉE
```

**Pourquoi ?** Article est un concept spécifique au BC Admin. Mettre trop de choses dans Shared crée un "Big Ball of Mud".

**Règle** : Shared ne doit contenir QUE des concepts vraiment transverses (ResourceUuid, NameField, EmailField, etc.).

### ❌ Appel direct de Use Cases entre BC

```php
// Dans Inventory\Adapters
use Admin\UseCases\CreateArticle\CreateArticle;  // ❌ INTERDIT
```

**Pourquoi ?** Les Use Cases sont la logique interne d'un BC. Utiliser les Contracts à la place.

## Bonnes Pratiques

### ✅ Principe du moindre privilège

N'expose dans les Contracts **QUE** ce qui est nécessaire aux autres BC.

```php
// ✅ Bon : Interface minimale
interface ArticleFinder {
    public function find(string $uuid): Article;
}

// ❌ Mauvais : Expose trop de méthodes
interface ArticleRepository extends CrudRepository {
    // Toutes les méthodes CRUD exposées...
}
```

### ✅ DTOs pour les données complexes

Si tu dois retourner des données sans exposer les entités ORM :

```php
// src/Admin/Contracts/DTO/ArticleData.php
final readonly class ArticleData {
    public function __construct(
        public string $uuid,
        public string $name,
        public int $price,
    ) {}
}

interface ArticleFinder {
    public function findAsData(string $uuid): ArticleData;
}
```

### ✅ Nommage explicite

- Préfixe les implémentations : `AdminArticleFinder`, `AdminZoneStorageFinder`
- Suffixe les interfaces : `ArticleFinder`, `ZoneStorageFinder` (ou `ArticleFinderInterface`)

## Checklist lors de l'ajout d'une communication inter-BC

- [ ] Créer l'interface dans `{BC}\Contracts\`
- [ ] Créer l'implémentation dans `{BC}\Adapters\Gateway\`
- [ ] Configurer l'autowiring dans `services.yaml`
- [ ] Mettre à jour le `deptrac.yaml` pour autoriser les Contracts
- [ ] Documenter le contrat (PHPDoc)
- [ ] Ajouter des tests pour l'implémentation
- [ ] Vérifier que `make qa` passe (deptrac inclus)

## Ressources

- [DDD - Bounded Context](https://martinfowler.com/bliki/BoundedContext.html)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Dependency Inversion Principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle)
