# Testing Strategy

## Overview

Ce projet utilise `LiipTestFixturesBundle` pour gérer l'isolation des tests, aussi bien pour les tests E2E (End-to-End) que pour les tests fonctionnels.

## Pourquoi pas de transactions Doctrine ?

Les transactions Doctrine (DAMA) ne peuvent pas être utilisées car :

### Pour les tests E2E (Panther)
1. Les tests E2E lancent un serveur web séparé
2. Le serveur web et le processus de test doivent partager la même base de données
3. Les transactions isolent les données, empêchant le serveur web de les voir

### Pour les tests fonctionnels (WebTestCase)
1. Les requêtes HTTP via `$this->client->request()` utilisent leur propre connexion DB
2. Cette connexion ne voit pas les données non committées de la transaction du test
3. Cela cause des erreurs avec l'EntityValueResolver de Symfony :
   ```
   "Entity" object not found by "Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver"
   ```
4. **Les LiveComponents ne fonctionnent pas** avec les transactions

**Solution adoptée** : `LiipTestFixturesBundle` pour purger et recréer la DB avant chaque test, garantissant que toutes les données sont visibles pour les requêtes HTTP.

## Classes de base disponibles

### `BasePantherTestCase` - Pour les tests E2E

**Usage**: Tests End-to-End avec Panther (tests navigateur réels)

**Caractéristiques**:
- Hérite de `Symfony\Component\Panther\PantherTestCase`
- Utilise `LiipTestFixturesBundle` pour reset manuel de la DB
- La base de données est purgée **avant** chaque test via `$this->databaseTool->loadFixtures([])`
- Utilise `flushAndClearEntityManager()` pour que le serveur web voie les données

**Exemple**:
```php
use App\Shared\Tests\BasePantherTestCase;

final class MyE2ETest extends BasePantherTestCase
{
    public function testSomething(): void
    {
        $client = self::createPantherClient();

        // Créer des données de test
        $company = (new CompanyDataBuilder())->create('Test')->build();
        $repository->save($company);

        // IMPORTANT: Flusher pour que Panther voie les données
        $this->flushAndClearEntityManager();

        // Tester avec Panther
        $client->request('GET', '/');
        // ...
    }
}
```

### `BaseFunctionalTestCase` - Pour les tests fonctionnels

**Usage**: Tests fonctionnels (contrôleurs, services avec base de données, LiveComponents)

**Caractéristiques**:
- Hérite de `Symfony\Bundle\FrameworkBundle\Test\WebTestCase`
- Utilise `LiipTestFixturesBundle` pour reset complet de la DB
- La base de données est purgée **avant** chaque test via `$this->databaseTool->loadFixtures([])`
- Les données sont réellement committées, donc visibles pour les requêtes HTTP internes
- **Compatible avec les LiveComponents** et toutes les fonctionnalités nécessitant des requêtes HTTP

**Pourquoi pas de transactions ?**
Les transactions Doctrine ne fonctionnent pas pour les tests fonctionnels qui font des requêtes HTTP car :
1. La requête HTTP utilise sa propre connexion à la base de données
2. Cette connexion ne voit pas les données non committées de la transaction du test
3. Cela cause des erreurs "entity not found" avec l'EntityValueResolver de Symfony

**Performance**:
- 🟡 **Modérée** : ~500-600ms par test (purge + migrations)
- Plus lent que les transactions, mais **nécessaire** pour la compatibilité HTTP
- Sur 112 tests : ~1min 03s (acceptable pour garantir la fiabilité)

**Exemple basique**:
```php
use App\Shared\Tests\BaseFunctionalTestCase;

final class MyFunctionalTest extends BaseFunctionalTestCase
{
    public function testController(): void
    {
        // Créer des données de test avec les repositories
        $company = (new CompanyDataBuilder())->create('Test')->build();
        $repository = static::getContainer()->get(DoctrineCompanyRepository::class);
        $repository->save($company);

        // Les données sont automatiquement committées et visibles
        // Pas besoin de flush manuel

        // Tester le contrôleur
        $this->client->request('GET', '/admin/company/' . $company->uuid());
        self::assertResponseIsSuccessful();
    }
}
```

**Exemple avec LiveComponents**:
```php
use App\Shared\Tests\BaseFunctionalTestCase;

final class LiveComponentTest extends BaseFunctionalTestCase
{
    public function testLiveComponent(): void
    {
        // Créer les données
        $article = (new ArticleDataBuilder())->create('Test')->build();
        $repository = static::getContainer()->get(DoctrineArticleRepository::class);
        $repository->save($article);

        // Le LiveComponent peut maintenant accéder à l'article
        $crawler = $this->client->request('GET', '/admin/articles/' . $article->uuid());
        self::assertResponseIsSuccessful();

        // Soumettre le formulaire LiveComponent
        $form = $crawler->selectButton('Enregistrer')->form();
        $this->client->submit($form);
        self::assertResponseRedirects();
    }
}
```

## Recommandations

### Pour les nouveaux tests

1. **Tests E2E** (navigateur réel) → Utiliser `BasePantherTestCase`
2. **Tests fonctionnels** (HTTP, pas de navigateur) → Utiliser `BaseFunctionalTestCase`
3. **Tests unitaires** (pas de DB) → Utiliser `PHPUnit\Framework\TestCase`

### Isolation des tests

**Tests Fonctionnels** (`BaseFunctionalTestCase`):
- ✅ La DB est purgée avant chaque test via `loadFixtures([])`
- ✅ Les données sont réellement committées (visibles pour HTTP)
- ✅ **Compatible avec LiveComponents et requêtes HTTP internes**
- 🟡 Temps moyen : ~500-600ms par test

**Tests E2E** (`BasePantherTestCase`):
- ✅ La DB est purgée avant chaque test via `loadFixtures([])`
- ✅ Les données sont committées pour que le serveur web les voie
- ✅ Utilise `flushAndClearEntityManager()` pour synchroniser
- 🟡 Temps moyen : ~500-800ms par test (serveur web + navigateur)

### Performance

**Tests Fonctionnels** : 🟡 **Modérée mais fiable**
- `loadFixtures([])` : ~500-600ms par test
- 112 tests fonctionnels : ~1min 03s
- **Nécessaire** pour la compatibilité avec les requêtes HTTP et LiveComponents
- Les transactions Doctrine ne fonctionnent PAS avec les requêtes HTTP internes

**Tests E2E** : 🟡 **Similaire aux fonctionnels**
- `loadFixtures([])` + serveur web : ~500-800ms par test
- Incompressible car le serveur web + navigateur réel

**Pourquoi pas de transactions pour les tests fonctionnels ?**
- ❌ Les transactions isolent les données du test
- ❌ Les requêtes HTTP utilisent une connexion séparée
- ❌ Cause des erreurs "entity not found" avec EntityValueResolver
- ✅ `loadFixtures([])` commit les données → visibles partout

## Fichiers modifiés

- `/src/Shared/Tests/BasePantherTestCase.php` - Classe de base pour tests E2E
- `/src/Shared/Tests/BaseFunctionalTestCase.php` - Classe de base pour tests fonctionnels
- `/phpunit.xml` - Configuration PHPUnit (DAMA désactivé)

## Références

- [Symfony Panther](https://github.com/symfony/panther)
- [LiipTestFixturesBundle](https://github.com/liip/LiipTestFixturesBundle)
- [DAMA DoctrineTestBundle](https://github.com/dmaicher/doctrine-test-bundle)