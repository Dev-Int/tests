# Exemple de Test Fonctionnel avec BaseFunctionalTestCase

## Exemple simple sans fixtures

```php
<?php

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Company;

use Admin\Tests\DataBuilder\CompanyDataBuilder;
use App\Shared\Tests\BaseFunctionalTestCase;

final class GetCompanyControllerTest extends BaseFunctionalTestCase
{
    public function testGetCompanyReturnsSuccessfully(): void
    {
        // Le client est déjà créé dans setUp() et disponible via $this->client

        // Créer une company directement
        $company = (new CompanyDataBuilder())->create('Test Company')->build();

        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($company);
        $em->flush();

        // Act
        $this->client->request('GET', '/admin/company');

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Company');
    }

    public function testGetCompanyWithoutData(): void
    {
        // Le client est déjà disponible via $this->client
        // Pas de données créées - la DB est vide grâce au rollback du test précédent

        // Act
        $this->client->request('GET', '/admin/company');

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.empty-state', 'No companies found');
    }
}
```

## Exemple avec fixtures partagées

```php
<?php

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Article;

use Admin\Adapters\DataFixtures\CompanyFixtures;
use Admin\Adapters\DataFixtures\TaxFixtures;
use Admin\Adapters\DataFixtures\UnitFixtures;
use App\Shared\Tests\BaseFunctionalTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

final class CreateArticleControllerTest extends BaseFunctionalTestCase
{
    private AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        parent::setUp();

        // Récupérer le databaseTool pour charger les fixtures
        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $databaseToolCollection->get();

        // Charger les fixtures nécessaires pour tous les tests de cette classe
        $this->databaseTool->loadFixtures([
            CompanyFixtures::class,
            UnitFixtures::class,
            TaxFixtures::class,
        ]);
    }

    public function testCreateArticleForm(): void
    {
        // Les fixtures sont déjà chargées
        // Le client est disponible via $this->client

        $this->client->request('GET', '/admin/article/create');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="createArticle"]');
    }

    public function testCreateArticleSuccess(): void
    {
        // Les fixtures sont re-chargées (grâce au rollback + reload dans setUp)

        $this->client->request('POST', '/admin/article/create', [
            'createArticle' => [
                'name' => 'New Article',
                'price' => '10.50',
                // ...
            ],
        ]);

        self::assertResponseRedirects('/admin/articles');
    }
}
```

## Exemple avec fixtures spécifiques par test

```php
<?php

namespace Admin\Tests\Adapters\Controller\Symfony\Controller\Supplier;

use Admin\Adapters\DataFixtures\SupplierFixtures;
use Admin\Tests\DataBuilder\FamilyLogDataBuilder;
use App\Shared\Tests\BaseFunctionalTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

final class GetSuppliersControllerTest extends BaseFunctionalTestCase
{
    public function testGetSuppliersWithMultipleSuppliers(): void
    {
        // Charger des fixtures spécifiques pour CE test
        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $databaseTool = $databaseToolCollection->get();
        $databaseTool->loadFixtures([SupplierFixtures::class]);

        // Le client est déjà disponible via $this->client
        $this->client->request('GET', '/admin/suppliers');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.supplier-list');
    }

    public function testGetSuppliersFiltered(): void
    {
        // Créer des données personnalisées pour CE test
        $familyLog = (new FamilyLogDataBuilder())->create('Frozen')->build();

        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($familyLog);
        $em->flush();

        // Le client est déjà disponible via $this->client
        $this->client->request('GET', '/admin/suppliers?family=' . $familyLog->uuid());

        self::assertResponseIsSuccessful();
    }
}
```

## Points clés

### ✅ Ce qui fonctionne bien

1. **Transactions automatiques** : Chaque test est isolé
2. **Performance** : Rollback très rapide (~1-5ms)
3. **Flexibilité** : Fixtures par test OU par classe selon les besoins
4. **CI-friendly** : Pas de configuration spéciale nécessaire
5. **Client automatique** : `$this->client` est créé dans `setUp()` et prêt à utiliser

### ⚠️ Points d'attention

1. **Client déjà créé** : N'appelez PAS `static::createClient()` dans les tests, utilisez `$this->client`
2. **Nested transactions** : Si votre code utilise déjà des transactions, Doctrine gère les savepoints automatiquement
3. **Flush obligatoire** : N'oubliez pas `$em->flush()` après `persist()`
4. **Fixtures reload** : Les fixtures dans `setUp()` sont rechargées à chaque test (rollback + reload)

### 🚀 Performance attendue

- Test simple : ~10-20ms
- Test avec fixtures : ~50-100ms (loadFixtures + test)
- Test sans fixtures : ~5-10ms (juste rollback + test)

## Migration depuis WebTestCase

Pour migrer un test existant :

```diff
- use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
+ use App\Shared\Tests\BaseFunctionalTestCase;

- final class MyTest extends WebTestCase
+ final class MyTest extends BaseFunctionalTestCase
{
    public function testSomething(): void
    {
-       $client = static::createClient();
+       // Le client est déjà créé : $this->client

-       $client->request('GET', '/some-url');
+       $this->client->request('GET', '/some-url');

        // Le reste reste identique
    }
}
```

**Changements requis** :
1. Hériter de `BaseFunctionalTestCase` au lieu de `WebTestCase`
2. Remplacer `$client = static::createClient()` par `$this->client`
3. C'est tout ! La transaction et le rollback sont automatiques.
