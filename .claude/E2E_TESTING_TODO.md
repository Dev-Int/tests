# Tests E2E - TODO et Analyse

## État actuel

### Tests E2E existants ✅
- **Company**
  - `CreateACompanyTest` - Création d'une entreprise
  - `UpdateACompanyTest` - Modification d'une entreprise

## Analyse des modules et recommandations

### 1. Article ⭐⭐⭐ PRIORITÉ HAUTE

**Pourquoi ?**
- Module le plus complexe de l'application
- Multiples relations (supplier, tax, zoneStorage, familyLog, packaging)
- Plusieurs workflows différents
- Utilise des LiveComponents pour les interactions

**Workflows à tester :**

#### Création d'article
- [ ] `CreateArticleTest` - Workflow complet de création
  - Sélection du fournisseur
  - Sélection de la taxe
  - Sélection des zones de stockage (multiple)
  - Sélection de la famille logistique
  - Configuration du packaging (conditionnement, colisage, palettisation)
  - Validation et enregistrement

#### Modifications d'article
- [ ] `ChangeArticleFinancialInformationTest` - Modification prix/taxe
  - Accès à la page de modification
  - Changement du prix unitaire
  - Changement de la taxe
  - Validation

- [ ] `ChangeArticleStorageInformationTest` - Modification stockage
  - Accès à la page de modification
  - Changement des zones de stockage
  - Changement de la famille logistique
  - Validation

- [ ] `ReAssignArticleSupplierTest` - Réassignation fournisseur
  - Accès à la page de réassignation
  - Sélection d'un nouveau fournisseur
  - Validation

- [ ] `RenameArticleTest` - Renommage
  - Accès à la page de renommage
  - Modification du nom
  - Validation

#### Navigation et listing
- [ ] `GetArticlesTest` - Liste des articles
  - Affichage de la liste
  - Pagination (si implémentée)
  - Recherche/filtres (si implémentés)

**Particularités à tester :**
- Les LiveComponents pour les sélections multiples
- Les validations côté client/serveur
- Les relations cascade entre entités
- L'affichage du packaging complexe (3 niveaux)

---

### 2. Workflow de configuration ⭐⭐ PRIORITÉ MOYENNE

**Pourquoi ?**
- Point d'entrée pour initialiser l'application
- Parcours guidé critique pour l'onboarding
- Workflow séquentiel avec plusieurs étapes

**Workflows à tester :**

- [ ] `ApplicationConfigurationWorkflowTest` - Parcours complet
  - Accès à la page de configuration (`/admin/configure`)
  - Navigation séquentielle à travers les étapes :
    1. Création des unités
    2. Création des taxes
    3. (Autres étapes selon votre workflow)
  - Validation du déblocage progressif des étapes
  - Vérification de la cohérence des données créées

**Particularités à tester :**
- Le verrouillage/déverrouillage des étapes
- Les redirections entre étapes
- L'état de complétion du workflow

---

### 3. Supplier ⭐ PRIORITÉ BASSE

**Pourquoi ?**
- Similaire à Company en complexité
- Déjà bien couvert par les tests fonctionnels
- Moins critique que Article

**Workflows à tester (optionnel) :**

- [ ] `CreateSupplierTest` - Création d'un fournisseur
  - Formulaire de création
  - Sélection de la famille logistique
  - Validation

- [ ] `UpdateSupplierTest` - Modifications groupées
  - `ChangeContactSupplierTest` - Modification du contact
  - `ChangeDomiciliationSupplierTest` - Modification de la domiciliation
  - `ChangeDeliverySpecificationsSupplierTest` - Modification des specs de livraison
  - `RenameSupplierTest` - Renommage

- [ ] `GetSuppliersTest` - Liste des fournisseurs
  - Affichage de la liste
  - Pagination/recherche

---

### 4. Tax, Unit, FamilyLog, ZoneStorage ⭐ PRIORITÉ TRÈS BASSE

**Pourquoi ?**
- Entités de référence simples (CRUD basique)
- Pas de relations complexes
- Les tests fonctionnels existants sont suffisants

**Workflows (probablement pas nécessaires en E2E) :**

#### Tax (Taxes)
- ❓ `CreateTaxTest`
- ❓ `RenameTaxTest`
- ❓ `RevaluateTaxTest`
- ❓ `GetTaxesTest`

#### Unit (Unités)
- ❓ `CreateUnitTest`
- ❓ `ChangeUnitLabelTest`
- ❓ `GetUnitsTest`

#### FamilyLog (Familles logistiques)
- ❓ `CreateFamilyLogTest`
- ❓ `ChangeLabelFamilyLogTest`
- ❓ `AssignParentFamilyLogTest` (hiérarchie)
- ❓ `GetFamilyLogsTest`

#### ZoneStorage (Zones de stockage)
- ❓ `CreateZoneStorageTest`
- ❓ `ChangeZoneStorageLabelTest`
- ❓ `ChangeZoneStorageFamilyLogTest`
- ❓ `GetZoneStoragesTest`

**Note :** Ces tests seraient redondants avec les tests fonctionnels existants, sauf si :
- Vous avez des interactions JavaScript complexes
- Des workflows multi-étapes
- Des validations client critiques

---

## Recommandations finales

### Tests E2E à implémenter en priorité

1. **Phase 1 - Critical (Article)** 🔴
   - CreateArticleTest
   - ChangeArticleFinancialInformationTest
   - ChangeArticleStorageInformationTest
   - GetArticlesTest

2. **Phase 2 - Important (Workflow + Article)** 🟡
   - ApplicationConfigurationWorkflowTest
   - ReAssignArticleSupplierTest
   - RenameArticleTest

3. **Phase 3 - Nice to have (Supplier)** 🟢
   - CreateSupplierTest
   - UpdateSupplierTest (groupe de modifications)

4. **Phase 4 - Optionnel** ⚪
   - Tests des entités de référence (Tax, Unit, etc.)
   - Seulement si vous identifiez des bugs spécifiques au navigateur

### Pourquoi cette priorisation ?

**Article en priorité :**
- C'est l'entité centrale de votre domaine (gestion restaurant/stock)
- Multiples relations complexes à tester
- LiveComponents à valider en conditions réelles
- Le plus grand risque de régression

**Configuration en second :**
- Workflow critique pour l'onboarding
- Une seule fois par installation, mais critique

**Supplier en troisième :**
- Important mais similaire à Company (déjà testé)
- Peut attendre si le temps manque

**Entités de référence en dernier :**
- CRUD simple déjà couvert par tests fonctionnels
- ROI faible pour des tests E2E

### Métrique de couverture suggérée

- **Minimum viable** : Phase 1 (Article) + Company existant = cœur métier couvert
- **Recommandé** : Phase 1 + Phase 2 = 95% des workflows critiques
- **Idéal** : Phase 1 + Phase 2 + Phase 3 = couverture complète des workflows complexes

---

## Notes techniques

### Patterns à suivre

Basez-vous sur vos tests Company existants pour :
- Structure des tests E2E
- Utilisation de `BasePantherTestCase`
- Utilisation de `DataBuilder` pour créer les données
- Appel à `flushAndClearEntityManager()` après création de données

### Performance

Avec l'approche actuelle :
- Temps moyen par test E2E : ~500-800ms
- Estimation pour Phase 1 (4 tests) : ~3-5 secondes
- Estimation pour Phase 1+2 (7 tests) : ~5-8 secondes
- Total avec tous les tests : acceptable (<2 minutes)

### Commandes utiles

```bash
# Lancer tous les tests E2E
make e2e

# Lancer les tests E2E d'un module spécifique
docker compose exec php php -d memory_limit=512M bin/phpunit --group=e2eTest src/Admin/Tests/EndToEnd/Article/

# Lancer un test E2E spécifique
docker compose exec php php -d memory_limit=512M bin/phpunit src/Admin/Tests/EndToEnd/Article/CreateArticleTest.php
```

---

**Dernière mise à jour** : 2025-11-26
**Statut** : Company complété, Article en attente
