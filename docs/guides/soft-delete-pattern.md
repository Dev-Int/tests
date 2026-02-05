# Soft Delete Pattern & Index Partiel PostgreSQL

## Vue d'ensemble

Le **soft delete** est un pattern qui consiste à marquer les enregistrements comme "supprimés" plutôt que de les supprimer physiquement de la base de données. Cela permet de conserver l'historique, faciliter les restaurations, et maintenir l'intégrité référentielle.

## Implémentation du Soft Delete

### 1. Ajouter le champ `disabledAt`

```php
#[ORM\Entity]
#[ORM\Table(name: 'employees')]
class Employee
{
    #[ORM\Column(type: 'datetimetz_immutable', nullable: true)]
    private ?\DateTimeImmutable $disabledAt;

    // ...
}
```

**Pourquoi `disabledAt` et pas `deletedAt` ?**
- Plus précis dans un contexte métier (employé désactivé, pas supprimé)
- Cohérent avec la terminologie métier (disable/enable)
- `NULL` = actif, `NOT NULL` = désactivé avec horodatage

### 2. Méthodes de domaine

```php
namespace Admin\Entities\Employee;

final readonly class Employee
{
    private function __construct(
        private ResourceUuid $uuid,
        // ... autres champs
        private ?\DateTimeImmutable $disabledAt,
    ) {}

    public function isActive(): bool
    {
        return $this->disabledAt === null;
    }

    public function disable(\DateTimeImmutable $at): self
    {
        if ($this->disabledAt !== null) {
            throw new \DomainException('Employee already disabled');
        }

        return new self(
            uuid: $this->uuid,
            // ... autres champs
            disabledAt: $at,
        );
    }

    public function disabledAt(): ?\DateTimeImmutable
    {
        return $this->disabledAt;
    }
}
```

## Optimisation avec Index Partiel

### Problème de performance

Avec un index classique sur `disabled_at`, **toutes les lignes** sont indexées (actives ET désactivées).

Dans la réalité :
- 95%+ des requêtes concernent les employés **actifs** (`WHERE disabled_at IS NULL`)
- Seulement 5% concernent les employés désactivés ou tous les employés

Un index classique sur `disabled_at` inclut donc 100% des lignes pour servir 100% des requêtes, mais 95% des requêtes ne concernent qu'une petite partie des données.

### Solution : Index Partiel

Un **index partiel** n'indexe que les lignes qui correspondent à un prédicat WHERE.

```php
#[ORM\Entity(repositoryClass: DoctrineEmployeeRepository::class)]
#[ORM\Table(name: 'employees')]
// Note: Parentheses required to match PostgreSQL's pg_get_expr() output
// See: https://github.com/doctrine/dbal/issues/3780
#[ORM\Index(
    name: 'idx_employee_active',
    columns: ['uuid'],
    options: ['where' => '(disabled_at IS NULL)']
)]
class Employee
{
    // ...
}
```

**Avantages** :
- ✅ **Index plus petit** : N'indexe que les employés actifs (95% de la volumétrie en moins)
- ✅ **Requêtes plus rapides** : Moins de pages à scanner, cache plus efficace
- ✅ **Moins d'I/O** : Index tenant en mémoire plus facilement
- ✅ **Moins de maintenance** : Moins de pages à mettre à jour lors des INSERT/UPDATE

**Utilisation automatique par PostgreSQL** :

```sql
-- PostgreSQL utilise automatiquement idx_employee_active
SELECT * FROM employees WHERE disabled_at IS NULL;

-- Plan d'exécution
EXPLAIN SELECT * FROM employees WHERE disabled_at IS NULL;
-- → Index Scan using idx_employee_active
```

### Comparaison Index Classique vs Index Partiel

| Critère | Index Classique | Index Partiel |
|---------|----------------|---------------|
| Taille | 100% des lignes | ~5% des lignes (actifs uniquement) |
| Performance lecture | Rapide | **Plus rapide** (index en cache) |
| Performance écriture | Moyenne | **Meilleure** (moins de pages) |
| Cas d'usage | Requêtes variées | Requêtes ciblées dominantes |

**Quand utiliser un index partiel ?**
- ✅ Soft delete avec majorité de requêtes sur actifs
- ✅ Filtres booléens avec distribution asymétrique (ex: `published = true` à 90%)
- ✅ Filtres temporels (ex: `created_at > NOW() - INTERVAL '30 days'`)
- ❌ Requêtes uniformément distribuées sur toutes les valeurs

## Bug Doctrine DBAL #3780 - Workaround Obligatoire

### Le problème

**PostgreSQL normalise les prédicats d'index** avec des parenthèses via `pg_get_expr()` :

```sql
-- Vous créez :
CREATE INDEX idx_employee_active ON employees (uuid) WHERE disabled_at IS NULL;

-- PostgreSQL stocke :
CREATE INDEX idx_employee_active ON employees (uuid) WHERE (disabled_at IS NULL);
--                                                            ^                  ^
--                                                         Parenthèses ajoutées
```

**Doctrine DBAL compare les chaînes brutes** sans normalisation :

```php
// Doctrine attend (depuis votre code)
'disabled_at IS NULL'

// PostgreSQL retourne (via pg_get_expr)
'(disabled_at IS NULL)'

// Doctrine détecte : ❌ DIFFERENCE → Schema not in sync
```

### La solution : Ajouter les parenthèses

**Toujours ajouter explicitement les parenthèses** dans l'attribut `#[ORM\Index]` :

```php
// ❌ INCORRECT - Doctrine détectera une différence
#[ORM\Index(
    name: 'idx_employee_active',
    columns: ['uuid'],
    options: ['where' => 'disabled_at IS NULL']
)]

// ✅ CORRECT - Match exact avec PostgreSQL
// Note: Parentheses required to match PostgreSQL's pg_get_expr() output
// See: https://github.com/doctrine/dbal/issues/3780
#[ORM\Index(
    name: 'idx_employee_active',
    columns: ['uuid'],
    options: ['where' => '(disabled_at IS NULL)']
)]
```

### Référence du bug

- **Issue GitHub** : https://github.com/doctrine/dbal/issues/3780
- **Status** : Open (au 2026-02-05)
- **Workaround** : Ajouter manuellement les parenthèses

### Vérification

```bash
# Vérifier que le schéma est synchronisé
docker compose exec php bin/console doctrine:schema:validate

# Résultat attendu :
# [OK] The database schema is in sync with the mapping files.
```

```sql
-- Vérifier la définition de l'index dans PostgreSQL
SELECT indexdef FROM pg_indexes WHERE indexname = 'idx_employee_active';

-- Résultat attendu :
-- CREATE INDEX idx_employee_active ON public.employees
-- USING btree (uuid) WHERE (disabled_at IS NULL)
```

## Exemples d'implémentation

### Exemple 1 : Repository avec soft delete

```php
namespace Admin\Adapters\Gateway\ORM\Repository;

use Admin\Entities\Employee\Employee;
use Admin\Entities\Employee\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineEmployeeRepository implements EmployeeRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(Employee $employee): void
    {
        $employeeOrm = $this->entityManager
            ->getRepository(\Admin\Adapters\Gateway\ORM\Entity\Employee::class)
            ->findOneBy(['uuid' => $employee->uuid()->toString()]);

        if ($employeeOrm === null) {
            $employeeOrm = \Admin\Adapters\Gateway\ORM\Entity\Employee::fromDomain($employee);
            $this->entityManager->persist($employeeOrm);
        } else {
            $employeeOrm->updateFromDomain($employee);
        }

        $this->entityManager->flush();
    }

    public function delete(Employee $employee): void
    {
        // Soft delete : on met à jour disabledAt, pas de remove()
        $disabledEmployee = $employee->disable(new \DateTimeImmutable());
        $this->save($disabledEmployee);
    }
}
```

### Exemple 2 : Finder avec filtre actifs uniquement

```php
namespace Admin\Adapters\Gateway\ORM\Finder;

use Admin\Entities\Employee\Employee;
use Admin\Entities\Employee\EmployeeFinder;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineEmployeeFinder implements EmployeeFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Retourne uniquement les employés actifs (disabled_at IS NULL)
     * Utilise automatiquement l'index partiel idx_employee_active
     */
    public function findAll(): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $employeesOrm = $qb
            ->select('e')
            ->from(\Admin\Adapters\Gateway\ORM\Entity\Employee::class, 'e')
            ->where('e.disabledAt IS NULL')  // ← Index partiel utilisé
            ->getQuery()
            ->getResult();

        return array_map(
            static fn ($employeeOrm) => $employeeOrm->toDomain(),
            $employeesOrm
        );
    }

    /**
     * Retourne TOUS les employés (actifs + désactivés)
     * N'utilise PAS l'index partiel
     */
    public function findAllIncludingDisabled(): array
    {
        $employeesOrm = $this->entityManager
            ->getRepository(\Admin\Adapters\Gateway\ORM\Entity\Employee::class)
            ->findAll();

        return array_map(
            static fn ($employeeOrm) => $employeeOrm->toDomain(),
            $employeesOrm
        );
    }
}
```

### Exemple 3 : Migration Doctrine

```php
final class Version20260125170115 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE employees (
            uuid UUID NOT NULL,
            -- ... autres colonnes
            disabled_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
            PRIMARY KEY(uuid)
        )');

        // Index partiel avec parenthèses (workaround bug DBAL #3780)
        $this->addSql('CREATE INDEX idx_employee_active
            ON employees (uuid)
            WHERE (disabled_at IS NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE employees');
    }
}
```

## Checklist d'implémentation

Lors de l'implémentation du soft delete avec index partiel :

- [ ] Ajouter `disabledAt` nullable dans l'entité ORM
- [ ] Ajouter l'index partiel avec **parenthèses** dans le prédicat WHERE
- [ ] Ajouter le commentaire explicatif du bug DBAL #3780
- [ ] Implémenter `disable()` dans l'entité de domaine
- [ ] Modifier `delete()` du Repository pour faire un soft delete
- [ ] Ajouter `WHERE disabled_at IS NULL` dans les Finders par défaut
- [ ] Vérifier avec `doctrine:schema:validate` que le schéma est synchronisé
- [ ] Vérifier avec `EXPLAIN` que PostgreSQL utilise bien l'index partiel
- [ ] Tester les cas : création, lecture actifs, lecture tous, désactivation

## Ressources

- [PostgreSQL Documentation - Partial Indexes](https://www.postgresql.org/docs/current/indexes-partial.html)
- [Doctrine DBAL Issue #3780](https://github.com/doctrine/dbal/issues/3780)
- [Use The Index, Luke! - Partial Indexes](https://use-the-index-luke.com/sql/where-clause/obfuscation/smart-logic)

## Quand ne PAS utiliser le soft delete ?

Le soft delete n'est pas toujours approprié :

- ❌ **RGPD / Données personnelles** : Droit à l'effacement → vraie suppression requise
- ❌ **Volumes énormes** : Croissance infinie de la table (préférer archivage)
- ❌ **Contraintes d'unicité** : Complique les contraintes (ex: email unique actif)
- ❌ **Performance critique** : Ajoute un filtre WHERE sur chaque requête

**Alternatives** :
- Archivage dans une table séparée (`employees_archived`)
- Event sourcing (historique complet des événements)
- Snapshots périodiques avant suppression physique
- Logs d'audit externes (base séparée, immuable)
