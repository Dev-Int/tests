# TODO List - Tâches actives

**Dernière mise à jour** : 2025-12-22

---

## 🔴 Priority High

### Communication inter-BC : Contrats et TwigComponents

**Status** : ⬜ À faire
**GitHub Issue** : [#163](https://github.com/Dev-Int/tests/issues/163)

**Objectif** :
Implémenter la communication entre bounded contexts via le pattern Contracts pour les select inter-BC (ex: Admin\ZoneStorage dans Inventory).

**Tâches** :
- [ ] Créer les contrats nécessaires pour la communication inter bounded context
- [ ] Utiliser ces contrats pour créer les TwigComponents dans leur BC respectif (`BC\Twig\Components`)
- [ ] Documenter le pattern provider dans `.claude/BOUNDED_CONTEXTS_QUICK.md`

**Principe clé** :
Les appels inter-BC doivent passer **UNIQUEMENT** par `BC\Contracts`, et **JAMAIS** par `BC\Adapters`.

**Architecture TwigComponents** :
- `BC\Twig\Components` : Components spécifiques au BC (avec dépendances métier)
- `src/Twig/Components` : Components génériques sans connexion aux BC (Icon, etc.)

**Fichiers concernés** :
- `src/Admin/Contracts/` (interfaces)
- `src/Admin/Adapters/Contracts/` (implémentations provider)
- `src/Admin/Twig/Components/` (TwigComponents Admin)
- `src/Inventory/Twig/Components/` (TwigComponents Inventory)

**Action IA** :
→ Utiliser skill `add-bc-contract` (à créer)

---

## 🟡 Priority Medium

### Migrer les services.yaml vers services.php

**Status** : ⬜ À faire
**GitHub Issue** : TBD

**Objectif** :
Migrer tous les fichiers `services.yaml` vers `services.php` pour suivre les futures bonnes pratiques de Symfony et bénéficier de l'autocomplétion IDE, du typage strict et de la vérification statique par PHPStan.

**Avantages** :
- ✅ Autocomplétion et navigation dans l'IDE
- ✅ Typage strict et détection d'erreurs par PHPStan
- ✅ Refactoring automatique (renommage de classes, etc.)
- ✅ Performance légèrement meilleure (pas de parsing YAML)

**Fichiers à migrer** :
- `src/Admin/Frameworks/config/services.yaml` → `services.php`
- `src/Inventory/Frameworks/config/services.yaml` → `services.php`
- `src/Shared/Frameworks/config/services.yaml` → `services.php`
- `config/services.yaml` → `services.php`

**Vérifications** :
- [ ] Tous les services.yaml migrés vers services.php
- [ ] PHPStan : 0 erreur
- [ ] Tous les tests passent (make ta + make e2e)
- [ ] Cache clear et vérification en dev/prod

---

### Upgrader le code vers PHP 8.3 avec Rector

**Status** : ⬜ À faire
**GitHub Issue** : TBD

**Objectif** :
Passer Rector sur tout le codebase pour utiliser les nouvelles fonctionnalités PHP 8.3 :
- Typed constants (`private const string ROUTE_NAME = '...'`)
- Readonly properties
- New in initializers
- Et autres améliorations syntaxiques

**Commande** :
```bash
make rector
```

**Vérifications** :
- [ ] Rector exécuté sur tout le codebase
- [ ] PHPStan : 0 erreur
- [ ] CS-Fixer : code formatté
- [ ] Tous les tests passent (make ta + make e2e)

---

### Implémenter le logging applicatif

**Status** : ⬜ À faire
**GitHub Issue** : TBD

**Objectif** :
Ajouter un système de logging pour faciliter le debug et le monitoring en production.

**Cas d'usage identifiés** :
- `ArticleProvider::forArticle()` : Logger l'UUID quand un article n'est pas trouvé
- Erreurs métier (validation, contraintes)
- Appels inter-BC (Contracts/Providers)

**Architecture proposée** :
- Utiliser `Psr\Log\LoggerInterface` (injecté via Symfony DI)
- Niveaux : `warning` pour entités non trouvées, `error` pour erreurs métier
- Format structuré pour exploitation (ELK, Datadog, etc.)

**Exemple** :
```php
if (!$article instanceof Article) {
    $this->logger->warning('Article not found', ['uuid' => $articleId->toString()]);
    return null;
}
```

**Vérifications** :
- [ ] Logger injecté dans les services critiques
- [ ] Tests unitaires vérifient les appels de log
- [ ] Configuration Monolog adaptée (dev vs prod)

---

## 🟢 Priority Low

### End-to-End Tests Coverage - Améliorations optionnelles

**Status** : 🟢 **BONNE COUVERTURE** (améliorations optionnelles possibles)

**État actuel** : La couverture E2E est très bonne. Les workflows critiques (création, pagination, annulation, configuration) sont tous testés.

**Tests manquants (optionnels)** :

**Article - Tests nominaux Update** (seuls les tests Cancel existent) :
- ❌ `ChangeArticleFinancialInformationTest` - Test nominal de modification prix/taxe réussie
- ❌ `ChangeArticleStorageInformationTest` - Test nominal de modification stockage réussie
- ❌ `ReAssignArticleSupplierTest` - Test nominal de réassignation fournisseur réussie
- ❌ `RenameArticleTest` - Test nominal de renommage réussi

**Supplier - Tests nominaux Update** (seuls les tests Cancel existent) :
- ❌ `RenameSupplierTest` - Test nominal de renommage réussi
- ❌ `ChangeDomiciliationSupplierTest` - Test nominal de modification domiciliation réussie
- ❌ `ChangeContactSupplierTest` - Test nominal de modification contact réussie
- ❌ `ChangeDeliverySpecificationsSupplierTest` - Test nominal de modification specs livraison réussie

**Recommandation** : Ces tests sont peu prioritaires car les workflows sont déjà couverts par les tests fonctionnels.

---

## 📚 Archive

Historique des tâches complétées :
→ Voir `.claude/archive/TODO_2024.md`
