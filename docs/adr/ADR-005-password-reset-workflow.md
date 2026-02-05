# ADR-005: Password Reset Workflow

**Status:** Accepted

**Date:** 2026-02-05

## Context

Le système nécessite un workflow de réinitialisation de mot de passe pour deux cas d'usage :
1. **Première connexion** : Nouvel Employee créé avec password temporaire
2. **Mot de passe oublié** : User existant demande un reset

### Contraintes de sécurité

- **Token unique** : Un token ne peut être utilisé qu'une seule fois
- **Expiration** : Token valide pendant une durée limitée (ex: 1 heure)
- **Invalidation** : Tokens précédents invalidés lors d'une nouvelle demande
- **Pas de JWT** : Pas de révocation possible avec JWT sans stateful store

### Contraintes métier

- **Email obligatoire** : Envoi du lien de reset par email
- **User-friendly** : Lien cliquable direct (pas de copier-coller de code)
- **Traçabilité** : Log des tentatives de reset (audit, détection d'abus)

## Decision

**Implémenter un token unique stocké en base de données avec expiration et invalidation après usage.**

### Pattern: Token Entity + Gateway

```php
// Auth BC - Entité PasswordResetToken
final class PasswordResetToken
{
    public static function create(
        ResourceUuid $uuid,
        ResourceUuid $userUuid,
        string $token,
        \DateTimeImmutable $expiresAt,
    ): self {
        return new self(
            uuid: $uuid,
            userUuid: $userUuid,
            token: $token,
            expiresAt: $expiresAt,
            usedAt: null,
            createdAt: ClockFactory::clock()->now(),
        );
    }

    public function markAsUsed(): void
    {
        if ($this->isUsed()) {
            throw new TokenAlreadyUsed($this->uuid);
        }
        $this->usedAt = ClockFactory::clock()->now();
    }

    public function isExpired(\DateTimeImmutable $now): bool
    {
        return $now > $this->expiresAt;
    }

    public function isUsed(): bool
    {
        return $this->usedAt instanceof \DateTimeImmutable;
    }
}
```

### Workflow de création Employee

```
1. CreateEmployee (Admin BC)
   └─> UserCreatorAdapter → CreateUser (Auth BC)
       └─> Password temporaire aléatoire (bin2hex(random_bytes(16)))

2. CreateEmployee (Admin BC)
   └─> PasswordResetGateway → CreateResetToken (Auth BC)
       └─> Génère token unique (bin2hex(random_bytes(32)))
       └─> Stocke PasswordResetToken (expires_at = now + 1h)

3. CreateEmployee (Admin BC)
   └─> NotificationGateway → Send Email
       └─> Template: email/employee_welcome.html.twig
       └─> Contenu: Lien https://app.example.com/auth/password-reset/{token}

4. Employee clique sur lien
   └─> PasswordResetController (Auth BC)
       └─> Valide token (exists, not expired, not used)
       └─> Affiche formulaire nouveau password

5. Employee soumet nouveau password
   └─> PasswordResetController (Auth BC)
       └─> Valide password (règles de complexité)
       └─> UpdateUser avec nouveau password hashé
       └─> PasswordResetToken::markAsUsed()
       └─> Redirect vers login
```

### Communication Inter-BC

```php
// Admin BC → Gateway (interface)
interface PasswordResetGateway {
    public function createResetToken(ResourceUuid $userUuid): string;
}

// Admin BC → Adapter (implémentation)
#[AsAlias(PasswordResetGateway::class)]
final readonly class PasswordResetTokenCreatorAdapter implements PasswordResetGateway
{
    public function createResetToken(ResourceUuid $userUuid): string
    {
        $result = $this->tokenCreator->createResetToken(
            new CreateResetTokenCommand(
                userUuid: $userUuid->toString()
            )
        );
        return $result->token;
    }
}

// Auth BC → Contract (interface publique)
interface CreateResetTokenCommandHandler {
    public function createResetToken(CreateResetTokenCommand $command): CreatedResetTokenResult;
}
```

### Sécurité

**Génération du token :**
```php
// ✅ CORRECT - Cryptographiquement sécurisé
$token = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux

// ❌ INCORRECT - Prédictible
$token = uniqid(); // Ne JAMAIS utiliser
$token = md5(time()); // Ne JAMAIS utiliser
```

**Validation du token :**
```php
public function validateToken(string $token): PasswordResetToken
{
    $resetToken = $this->repository->findByToken($token);

    if (!$resetToken) {
        throw new InvalidToken();
    }

    if ($resetToken->isExpired(ClockFactory::clock()->now())) {
        throw new TokenExpired();
    }

    if ($resetToken->isUsed()) {
        throw new TokenAlreadyUsed();
    }

    return $resetToken;
}
```

## Consequences

### Positive ✅

- **Sécurité** : Token cryptographiquement sécurisé (random_bytes)
- **Révocation** : Tokens stockés en DB, facilement révocables
- **Expiration** : Durée de validité limitée (réduit la fenêtre d'attaque)
- **Idempotence** : Token ne peut être utilisé qu'une seule fois
- **Traçabilité** : Logs complets (created_at, used_at, expires_at)
- **UX** : Lien cliquable direct (pas de copier-coller)

### Negative ⚠️

- **Database dependency** : Nécessite un store persistant (pas stateless)
- **Email dependency** : Si email down, reset impossible
- **Storage growth** : Tokens expirés s'accumulent (nécessite cleanup job)
- **Race condition** : Multiples clics simultanés possibles (résolu par `markAsUsed()`)

### Trade-offs acceptés

- **Storage cost** : Acceptable (tokens < 500 bytes, cleanup périodique)
- **Email latency** : Acceptable (secondes, pas critique)
- **Stateful** : Nécessaire pour révocation (JWT insuffisant)

## Alternatives Considered

### 1. JWT Token (stateless)

**Description** : Token JWT signé avec expiration, pas de stockage DB.

**Rejected because** :
- **Pas de révocation** : Impossible d'invalider un JWT avant expiration
- **Stateless problem** : Pas de traçabilité (qui a utilisé le token?)
- **Sécurité** : Token valide jusqu'à expiration même si User désactivé
- **Pas d'idempotence** : Peut être réutilisé pendant la durée de validité

### 2. SMS/OTP (One-Time Password)

**Description** : Code à 6 chiffres envoyé par SMS.

**Rejected because** :
- **Coût** : Service SMS payant (Twilio, AWS SNS)
- **Complexité** : Nécessite gestion téléphone mobile
- **UX** : Copier-coller code moins user-friendly que lien cliquable
- **Over-engineering** : Email suffisant pour le besoin actuel

### 3. Magic Link (passwordless)

**Description** : Lien de connexion directe sans password.

**Rejected because** :
- **Sécurité** : Moins sécurisé que password (email compromise = accès total)
- **Métier** : Besoin de password pour autres flows (API, mobile)
- **Compliance** : Certains clients exigent password fort

### 4. Email avec code à 6 chiffres

**Description** : Envoyer code court par email, saisir dans formulaire.

**Rejected because** :
- **UX** : Moins user-friendly (copier-coller, risque de typo)
- **Sécurité** : Code court = espace de recherche réduit (brute-force)
- **Complexité** : Nécessite validation côté serveur (rate limiting)

## Implementation

### Code Locations

- **PasswordResetToken Entity** : `src/Auth/Entities/PasswordReset/PasswordResetToken.php`
- **CreateResetToken UseCase** : `src/Auth/UseCases/PasswordReset/CreateResetToken/CreateResetToken.php`
- **PasswordResetController** : `src/Auth/Adapters/Controller/Symfony/Controller/PasswordResetController.php`
- **PasswordResetGateway** : `src/Admin/UseCases/Gateway/PasswordResetGateway.php`
- **PasswordResetTokenCreatorAdapter** : `src/Admin/Adapters/Gateway/Auth/PasswordResetTokenCreatorAdapter.php`

### Database Schema

```sql
CREATE TABLE password_reset_tokens (
    uuid VARCHAR(36) PRIMARY KEY,
    user_uuid VARCHAR(36) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL, -- bin2hex(random_bytes(32))
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_token (token), -- Performance lookup
    INDEX idx_user_uuid (user_uuid), -- Cleanup par user
    INDEX idx_expires_at (expires_at), -- Cleanup tokens expirés
    FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
);
```

### Configuration

**Expiration par défaut :**
```yaml
# config/packages/password_reset.yaml
parameters:
    password_reset.token_lifetime: 3600 # 1 heure (en secondes)
```

**Cleanup Job (recommandé) :**
```php
// Supprimer tokens expirés tous les jours
// bin/console app:cleanup-expired-tokens

public function cleanupExpiredTokens(): void
{
    $this->repository->deleteExpired(
        ClockFactory::clock()->now()
    );
}
```

### Email Template

```twig
{# templates/email/employee_welcome.html.twig #}
<h1>Bienvenue {{ firstName }} !</h1>
<p>Votre compte a été créé. Cliquez sur le lien ci-dessous pour définir votre mot de passe :</p>
<a href="{{ resetUrl }}">Créer mon mot de passe</a>
<p>Ce lien expire dans 1 heure.</p>
```

### Tests

- **Unit Tests** :
  - `CreateResetTokenTest` (génération token)
  - `PasswordResetTokenTest` (isExpired, isUsed, markAsUsed)
- **Integration Tests** :
  - `PasswordResetWorkflowTest` (workflow complet)
  - `CreateEmployeeEmailTest` (email envoyé avec bon token)
- **Functional Tests** :
  - `PasswordResetControllerTest` (validation token, formulaire)

### Règles de validation password

**Complexité minimale (exemple) :**
```php
#[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins 8 caractères')]
#[Assert\NotCompromisedPassword(message: 'Ce mot de passe est trop commun')]
#[Assert\PasswordStrength(minScore: PasswordStrength::STRENGTH_MEDIUM)]
private string $password;
```

## Améliorations futures

### Rate Limiting

Limiter les tentatives de reset pour éviter l'abus :
```php
// Exemple: Max 3 tentatives par heure par user
if ($this->rateLimiter->isExceeded($userEmail)) {
    throw new TooManyResetAttempts();
}
```

### Invalidation des tokens précédents

Lors d'une nouvelle demande, invalider tous les tokens actifs du User :
```php
public function createResetToken(ResourceUuid $userUuid): string
{
    // Invalider tokens existants
    $this->repository->invalidateAllForUser($userUuid);

    // Créer nouveau token
    // ...
}
```

### Notification de changement de password

Envoyer email de confirmation après reset réussi :
```php
$this->notificationGateway->sendEmail(
    new EmailPayload(
        to: $user->email(),
        type: EmailType::PASSWORD_CHANGED,
        subject: 'Votre mot de passe a été modifié',
        context: ['changedAt' => ClockFactory::clock()->now()],
    )
);
```

## References

- **OWASP Password Reset Cheat Sheet** : https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html
- **Symfony PasswordHasher** : https://symfony.com/doc/current/security/passwords.html
- **Guide Inter-BC** : `docs/guides/bounded-contexts.md`
- **PR** : #256 (CRUD Employee + Password Reset)

## Related ADRs

- ADR-003: Employee-User Coupling (création User avec password temporaire)
- ADR-004: Employee Soft Delete (désactivation invalide les tokens)
