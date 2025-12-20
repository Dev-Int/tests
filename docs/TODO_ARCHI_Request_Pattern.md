# TODO Architecture - Pattern Request

**Status**: À analyser
**Priority**: Medium
**Impact**: Breaking change majeur si modification

---

## Contexte

Suite à la review de la PR #174, question soulevée sur le pattern Request utilisé dans le projet.

**Pattern actuel** :
- Request = Interface (UseCase layer)
- Implémentation = Classe readonly (Adapter layer)
- Tests = Mock de l'interface

---

## Question

Faut-il migrer de **Interface Request** vers **DTO readonly Request** ?

---

## Analyse requise

### 1. Patterns utilisés en DDD

**Rechercher** :
- Pattern Interface vs DTO pour les Request objects
- Best practices DDD/Clean Architecture
- Avantages/inconvénients de chaque approche

**Sources** :
- Domain-Driven Design (Eric Evans)
- Clean Architecture (Robert C. Martin)
- Implementing DDD (Vaughn Vernon)
- Retours communauté (blogs, Stack Overflow, forums DDD)

### 2. Comparaison des patterns

#### Pattern actuel : Interface Request

**Avantages** :
- Dependency Inversion Principle (DIP) respecté
- Découplage UseCase ↔ Adapter
- Testable avec mocks
- Adapters multiples possibles (API, CLI, Form, etc.)

**Inconvénients** :
- Nécessite mock dans les tests unitaires
- Boilerplate : interface + implémentation(s)
- Indirection supplémentaire

#### Pattern alternatif : DTO readonly

**Avantages** :
- Simplicité : pas de mock nécessaire
- Construction directe : `new Request($uuid, $name)`
- Immutable par design (readonly)
- Code plus explicite
- Moins de fichiers

**Inconvénients** :
- Couplage direct UseCase → DTO concret
- Violation potentielle DIP
- Moins flexible pour multiples adaptations
- Adapter doit créer le même DTO

### 3. Impact sur le projet

**Si migration vers DTO readonly** :

**Fichiers impactés** :
- ~40+ Requests (interface → classe readonly)
- ~80+ Tests (retirer mocks, construire DTOs)
- ~40+ Adapters (modifier instanciation)
- Templates (.claude/templates/request.php.tpl)
- Skills (.claude/skills/create-use-case/)
- Documentation

**Estimation** : ~3-5 jours de refactoring + tests

**Risques** :
- Breaking change complet
- Régression potentielle
- Nécessite migration incrémentale ou big-bang

---

## Recommandation préliminaire

**À valider après analyse** :

Le pattern **Interface Request** semble plus aligné avec DDD/Clean Architecture car :
1. Respecte Dependency Inversion (UseCase ne dépend pas de l'infrastructure)
2. Permet adaptations multiples (REST API, GraphQL, CLI, Form, gRPC...)
3. Testabilité isolée du UseCase
4. Standard dans littérature DDD

**Cependant**, si le projet n'a qu'un seul type d'Adapter par Request et que la simplicité prime, le pattern **DTO readonly** peut être acceptable.

---

## Actions

- [ ] Rechercher best practices DDD sur Request objects
- [ ] Comparer implémentations de projets DDD/CQRS reconnus
- [ ] Analyser si le projet a/aura besoin d'adapters multiples par UseCase
- [ ] Évaluer coût/bénéfice de la migration
- [ ] Décision finale : conserver Interface ou migrer vers DTO
- [ ] Si migration : créer plan de migration incrémentale

---

## Ressources

- [x] Code actuel : `src/Admin/UseCases/*/Request.php` (interfaces)
- [x] Code actuel : `src/Admin/Adapters/*/*Request.php` (implémentations)
- [ ] Articles DDD sur Request/Command objects
- [ ] Exemples projets open-source DDD

---

**Date création** : 2024-12-20  
**Créé par** : Claude Code Assistant  
**Issue liée** : PR #174 review comment
