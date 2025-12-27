# Code Review Guidelines

Ce fichier documente les décisions architecturales validées pour éviter les faux-positifs en review.

## Décisions Architecturales Validées

| Pattern | Statut | Explication |
|---------|--------|-------------|
| Adapters → Adapters | OK | ORM/Gateway peuvent s'utiliser entre elles |
| Controller → UseCase direct | OK | Pas d'interface pour les UseCases |
| Passage `&$entity` | OK | Convention de clarté pour mutations |
| UseCase multi-étapes | OK | Fusion intentionnelle (ex: load + start) |

## Patterns Techniques Validés

| Pattern | Statut | Explication |
|---------|--------|-------------|
| Quantity + bcmath | OK | Millièmes INTEGER pour précision |
| FLOAT PackagingLevel | OK | Ratios packaging, pas des stocks |
| Doctrine 3.x séquences | OK | Auto-gérées par Doctrine |
| UNIQUE INDEX sur FK | OK | Contrainte explicite valide |

## Checklist Avant Signalement

- [ ] Ce n'est pas un pattern validé ci-dessus
- [ ] Le test n'existe pas déjà (*Test.php, DataProvider)
- [ ] Vérifié les règles Deptrac du projet
- [ ] Lu le code réel, pas juste le diff
