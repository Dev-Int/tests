# Auth BC - Authentication & Authorization

**Purpose**: Guide complet de l'authentification, l'autorisation, et la gestion des comptes User (Auth BC).

---

## Table of Contents

1. [Overview](#1-overview)
2. [Authentification](#2-authentification)
3. [Autorisation](#3-autorisation)
4. [Password Management](#4-password-management)
5. [Inter-BC Communication](#5-inter-bc-communication)
6. [Code Locations](#6-code-locations)
7. [Testing Strategy](#7-testing-strategy)

---

## 1. Overview

### Responsabilités Auth BC

Le Bounded Context Auth gère les préoccupations techniques d'accès système :

- **Authentification** : Vérification identité (email + password)
- **Autorisation** : Contrôle d'accès basé sur les roles
- **Session management** : Gestion des sessions utilisateur
- **Password reset** : Workflow de réinitialisation de mot de passe
- **User CRUD** : Création, lecture, modification, désactivation des Users

### Architecture

```
Auth BC
├── Entities
│   ├── User                        # Aggregate Root (compte d'accès)
│   ├── PasswordResetToken          # Token reset avec expiration
│   └── VO/
│       └── HashedPassword          # Value Object (password hashé)
├── UseCases
│   ├── User/
│   │   ├── CreateUser              # Créer User
│   │   ├── GetUsers                # Lister Users
│   │   ├── UpdateUser              # Modifier User
│   │   └── DisableUser             # Désactiver User
│   └── PasswordReset/
│       ├── CreateResetToken        # Générer token
│       ├── ValidateResetToken      # Valider token
│       └── ResetPassword           # Changer password
├── Contracts (API publique)
│   └── Services/CommandHandler/
│       ├── CreateUser/             # Contract pour Admin BC
│       ├── DisableUser/            # Contract pour Admin BC
│       └── UpdateUser/             # Contract pour Admin BC
└── Adapters
    ├── Controller/
    │   ├── LoginController         # Formulaire login
    │   ├── LogoutController        # Déconnexion
    │   └── PasswordResetController # Reset password
    └── Security/
        ├── CurrentUserProvider     # Fourni User courant
        └── SymfonyPasswordHasher   # Hash passwords
```

### User Entity

**Location** : `src/Auth/Entities/User.php`

```php
final class User
{
    public static function create(
        ResourceUuid $uuid,
        EmailField $email,
        HashedPassword $password,
        array $roles = [],
    ): self;

    // Behavior methods
    public function disable(): void;
    public function isActive(): bool;
    public function hasRole(Role $role): bool;
    public function isAdmin(): bool;
    public function changeEmail(EmailField $newEmail): void;
    public function changePassword(HashedPassword $newPassword): void;
    public function updateRoles(array $roles): void;
}
```

**Caractéristiques :**
- Email : Identifiant de connexion (unique)
- Password : Hashé avec `HashedPassword` VO (Argon2id)
- Roles : Array de `Role` enum (`ROLE_USER`, `ROLE_ADMIN`)
- Soft delete : `disabledAt` nullable (comme Employee)
- Normalisation roles : `ROLE_USER` toujours présent automatiquement

### Role Enum

**Location** : `src/Shared/Entities/Role.php`

```php
enum Role: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
}
```

**Usage :**

```php
// Créer User avec role admin
$user = User::create(
    uuid: ResourceUuid::generate(),
    email: EmailField::fromString('admin@example.com'),
    password: HashedPassword::fromHashed($hashedPassword),
    roles: [Role::ADMIN], // ROLE_USER ajouté automatiquement
);

// Vérifier role
if ($user->isAdmin()) {
    // Accès admin
}

if ($user->hasRole(Role::ADMIN)) {
    // Accès admin (équivalent)
}
```

---

## 2. Authentification

### Login Workflow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. User visite /login                                       │
│    └─> LoginController::showLoginForm()                     │
│        └─> Affiche formulaire (email + password)            │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. User soumet credentials                                  │
│    └─> Symfony Security firewall intercept                  │
│        └─> AuthenticatorManager vérifie email + password    │
│        └─> PasswordHasher::verify(plainPassword, hashed)    │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Success: Session créée                                   │
│    └─> User stocké en session                               │
│    └─> Redirect vers dashboard                              │
└─────────────────────────────────────────────────────────────┘
```

### LoginController

**Location** : `src/Auth/Adapters/Controller/Symfony/Controller/LoginController.php`

```php
#[Route('/login', name: 'auth_login')]
final class LoginController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function showLoginForm(
        AuthenticationUtils $authenticationUtils,
    ): Response {
        // Get login error if exists
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get last username (email) entered
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('', methods: ['POST'], name: '_check')]
    public function check(): never
    {
        // Intercepté par Symfony Security, ce code n'est jamais exécuté
        throw new \LogicException('This should never be reached');
    }
}
```

### Security Configuration

**Location** : `src/Auth/Frameworks/config/security.yaml`

```yaml
security:
    password_hashers:
        Auth\Entities\User:
            algorithm: auto # Argon2id par défaut

    providers:
        app_user_provider:
            entity:
                class: Auth\Adapters\Gateway\ORM\Entity\User
                property: email

    firewalls:
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: auth_login
                check_path: auth_login_check
                default_target_path: admin_dashboard
                enable_csrf: true
            logout:
                path: auth_logout
                target: auth_login
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800 # 1 semaine

    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/, roles: ROLE_USER }
```

### Password Hashing

**HashedPassword Value Object** : `src/Auth/Entities/VO/HashedPassword.php`

```php
final readonly class HashedPassword
{
    private function __construct(
        private string $hashedValue,
    ) {}

    public static function fromHashed(string $hashedValue): self
    {
        return new self($hashedValue);
    }

    public static function fromPlain(string $plainPassword, PasswordHasherInterface $hasher): self
    {
        $hashed = $hasher->hashPassword($plainPassword);
        return new self($hashed);
    }

    public function toString(): string
    {
        return $this->hashedValue;
    }

    public function verify(string $plainPassword, PasswordHasherInterface $hasher): bool
    {
        return $hasher->verify($this->hashedValue, $plainPassword);
    }
}
```

**SymfonyPasswordHasher Adapter** :

```php
#[AsAlias(PasswordHasherInterface::class)]
final readonly class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(
        private UserPasswordHasherInterface $symfonyHasher,
    ) {}

    public function hashPassword(string $plainPassword): string
    {
        // Utilise Argon2id (auto)
        return $this->symfonyHasher->hashPassword(
            new InMemoryUser('temp', $plainPassword),
            $plainPassword
        );
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $this->symfonyHasher->isPasswordValid(
            new InMemoryUser('temp', $hashedPassword),
            $plainPassword
        );
    }
}
```

---

## 3. Autorisation

### Role-Based Access Control (RBAC)

Le système utilise l'attribut `#[RequireRole]` pour contrôler l'accès aux controllers.

### RequireRole Attribute

**Location** : `src/Auth/Adapters/Controller/Symfony/Attribute/RequireRole.php`

```php
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final readonly class RequireRole
{
    public function __construct(
        public string $role, // Ex: 'ROLE_ADMIN'
    ) {}
}
```

**Usage dans Controllers :**

```php
use Auth\Adapters\Controller\Symfony\Attribute\RequireRole;

#[Route('/admin/employees')]
#[RequireRole('ROLE_ADMIN')] // ✅ Protège tout le controller
final class EmployeeController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(): Response
    {
        // Seulement accessible aux ROLE_ADMIN
    }

    #[Route('/create', methods: ['GET', 'POST'])]
    #[RequireRole('ROLE_ADMIN')] // ✅ Redondant mais explicite
    public function create(): Response
    {
        // Seulement accessible aux ROLE_ADMIN
    }
}
```

### AuthenticationListener (Event Subscriber)

**Location** : `src/Auth/Adapters/Controller/Symfony/EventSubscriber/AuthenticationListener.php`

```php
final readonly class AuthenticationListener implements EventSubscriberInterface
{
    public function __construct(
        private CurrentUserProvider $currentUserProvider,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 0],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!\is_array($controller)) {
            return;
        }

        [$controllerObject, $method] = $controller;

        // Vérifier attribut #[RequireRole] sur la méthode
        $reflectionMethod = new \ReflectionMethod($controllerObject, $method);
        $attributes = $reflectionMethod->getAttributes(RequireRole::class);

        if (empty($attributes)) {
            // Vérifier attribut #[RequireRole] sur la classe
            $reflectionClass = new \ReflectionClass($controllerObject);
            $attributes = $reflectionClass->getAttributes(RequireRole::class);
        }

        if (empty($attributes)) {
            return; // Pas de protection role nécessaire
        }

        $requireRole = $attributes[0]->newInstance();
        $requiredRole = Role::from($requireRole->role);

        // Vérifier que l'utilisateur a le role requis
        $currentUser = $this->currentUserProvider->getCurrentUser();

        if (!$currentUser->hasRole($requiredRole)) {
            throw new AccessDeniedException(
                sprintf('Access denied. Required role: %s', $requiredRole->value)
            );
        }
    }
}
```

### CurrentUserProvider

**Location** : `src/Auth/Adapters/Security/CurrentUserProvider.php`

```php
#[AsAlias(CurrentUserProvider::class)]
final readonly class CurrentUserProvider
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
    ) {}

    public function getCurrentUser(): User
    {
        $symfonyUser = $this->security->getUser();

        if (!$symfonyUser instanceof SymfonyUserInterface) {
            throw new \RuntimeException('No user authenticated');
        }

        $email = EmailField::fromString($symfonyUser->getUserIdentifier());

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        return $user;
    }

    public function getCurrentUserOrNull(): ?User
    {
        try {
            return $this->getCurrentUser();
        } catch (\RuntimeException) {
            return null;
        }
    }
}
```

**Usage dans UseCases :**

```php
final readonly class GetCurrentUserProfile
{
    public function __construct(
        private CurrentUserProvider $currentUserProvider,
    ) {}

    public function execute(): User
    {
        return $this->currentUserProvider->getCurrentUser();
    }
}
```

---

## 4. Password Management

### Password Reset Workflow

Voir [ADR-005: Password Reset Workflow](adr/ADR-005-password-reset-workflow.md) pour détails complets.

**Résumé :**

1. **CreateResetToken** : Génère token unique avec expiration (1h)
2. **Email envoyé** : Lien `/auth/password-reset/{token}`
3. **ValidateResetToken** : Vérifie token (exists, not expired, not used)
4. **ResetPassword** : Change password + marque token comme utilisé

### PasswordResetToken Entity

**Location** : `src/Auth/Entities/PasswordReset/PasswordResetToken.php`

```php
final class PasswordResetToken
{
    public static function create(
        ResourceUuid $uuid,
        ResourceUuid $userUuid,
        string $token,
        \DateTimeImmutable $expiresAt,
    ): self;

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

### UseCases Password Reset

#### CreateResetToken

```php
final readonly class CreateResetToken
{
    public function execute(CreateResetTokenRequest $request): CreateResetTokenResponse
    {
        $user = $this->userRepository->getByUuid($request->userUuid());

        // Invalider tokens existants (optionnel)
        $this->repository->invalidateAllForUser($user->uuid());

        // Générer nouveau token
        $token = bin2hex(random_bytes(32)); // 64 chars
        $expiresAt = ClockFactory::clock()->now()->modify('+1 hour');

        $resetToken = PasswordResetToken::create(
            uuid: ResourceUuid::generate(),
            userUuid: $user->uuid(),
            token: $token,
            expiresAt: $expiresAt,
        );

        $this->repository->save($resetToken);

        return new CreateResetTokenResponse($token);
    }
}
```

#### ResetPassword

```php
final readonly class ResetPassword
{
    public function execute(ResetPasswordRequest $request): void
    {
        $resetToken = $this->repository->findByToken($request->token());

        if (!$resetToken || $resetToken->isExpired(ClockFactory::clock()->now())) {
            throw new InvalidOrExpiredToken();
        }

        if ($resetToken->isUsed()) {
            throw new TokenAlreadyUsed();
        }

        $user = $this->userRepository->getByUuid($resetToken->userUuid());

        $hashedPassword = HashedPassword::fromPlain(
            $request->plainPassword(),
            $this->passwordHasher
        );

        $user->changePassword($hashedPassword);

        $resetToken->markAsUsed();

        $this->userRepository->update($user);
        $this->repository->update($resetToken);
    }
}
```

---

## 5. Inter-BC Communication

### Contracts (API publique Auth BC)

Les Contracts permettent aux autres BC (Admin, Inventory) d'appeler Auth BC sans couplage direct.

#### CreateUserCommandHandler

**Location** : `src/Auth/Contracts/Services/CommandHandler/CreateUser/CreateUserCommandHandler.php`

```php
interface CreateUserCommandHandler
{
    /**
     * @throws EmailAlreadyExists
     */
    public function createUser(CreateUserCommand $command): CreatedUserResult;
}
```

**Command :**

```php
final readonly class CreateUserCommand
{
    public function __construct(
        public EmailField $email,
        public string $plainPassword,
        public array $roles,
    ) {}
}
```

**Result :**

```php
final readonly class CreatedUserResult
{
    public function __construct(
        public string $uuid,
        public string $email,
    ) {}
}
```

**Implementation** : `src/Auth/Adapters/Contracts/Services/CommandHandler/CreateUser/CreateUserCommandHandler.php`

```php
#[AsAlias(CreateUserCommandHandler::class)]
final readonly class CreateUserCommandHandler implements CreateUserCommandHandlerInterface
{
    public function __construct(
        private CreateUser $createUser, // UseCase
    ) {}

    public function createUser(CreateUserCommand $command): CreatedUserResult
    {
        $response = $this->createUser->execute(
            new CreateUserRequest(
                email: $command->email,
                plainPassword: $command->plainPassword,
                roles: $command->roles,
            )
        );

        return new CreatedUserResult(
            uuid: $response->user->uuid()->toString(),
            email: $response->user->email()->toString(),
        );
    }
}
```

#### DisableUserCommandHandler

**Location** : `src/Auth/Contracts/Services/CommandHandler/DisableUser/DisableUserCommandHandler.php`

```php
interface DisableUserCommandHandler
{
    /**
     * @throws UserNotFound
     * @throws UserAlreadyDisabled
     */
    public function disableUser(DisableUserCommand $command): void;
}
```

### Usage depuis Admin BC

Voir [admin-employee-management.md#4-communication-inter-bc](admin-employee-management.md#4-communication-inter-bc) pour exemples complets.

**Résumé :**

```
Admin BC (Consumer)               Auth BC (Provider)
─────────────────────           ─────────────────────
CreateEmployee UseCase              CreateUser UseCase
        │                                   ▲
        │ calls                             │
        ▼                                   │
UserCreatorGateway (interface)              │
        ▲                                   │
        │ implements                        │
        │                                   │
UserCreatorAdapter ─────── calls ───────────┘
                        (via Contract)
```

---

## 6. Code Locations

### Entities (Domain)

```
src/Auth/Entities/
├── User.php                         # Aggregate Root (compte accès)
├── PasswordReset/
│   └── PasswordResetToken.php       # Token reset avec expiration
├── VO/
│   └── HashedPassword.php           # Value Object (password hashé)
└── Exception/
    ├── UserAlreadyDisabled.php
    └── UserNotFound.php
```

### UseCases (Business Logic)

```
src/Auth/UseCases/
├── User/
│   ├── CreateUser/
│   │   ├── CreateUser.php
│   │   ├── CreateUserRequest.php
│   │   └── CreateUserResponse.php
│   ├── GetUsers/
│   │   ├── GetUsers.php
│   │   └── GetUsersResponse.php
│   ├── UpdateUser/
│   │   └── UpdateUser.php
│   └── DisableUser/
│       └── DisableUser.php
└── PasswordReset/
    ├── CreateResetToken/
    │   └── CreateResetToken.php
    ├── ValidateResetToken/
    │   └── ValidateResetToken.php
    └── ResetPassword/
        └── ResetPassword.php
```

### Contracts (API Publique)

```
src/Auth/Contracts/
├── Services/CommandHandler/
│   ├── CreateUser/
│   │   ├── CreateUserCommandHandler.php      # Interface
│   │   ├── CreateUserCommand.php             # DTO Command
│   │   └── CreatedUserResult.php             # DTO Result
│   ├── DisableUser/
│   │   ├── DisableUserCommandHandler.php
│   │   └── DisableUserCommand.php
│   └── UpdateUser/
│       ├── UpdateUserCommandHandler.php
│       └── UpdateUserCommand.php
└── Exception/
    ├── EmailAlreadyExists.php
    ├── UserNotFound.php
    └── UserAlreadyDisabled.php
```

### Adapters (Implementations)

```
src/Auth/Adapters/
├── Contracts/Services/CommandHandler/
│   ├── CreateUser/
│   │   └── CreateUserCommandHandler.php     # Implémente Contract
│   ├── DisableUser/
│   │   └── DisableUserCommandHandler.php
│   └── UpdateUser/
│       └── UpdateUserCommandHandler.php
├── Controller/Symfony/Controller/
│   ├── LoginController.php
│   ├── LogoutController.php
│   ├── PasswordResetController.php
│   └── GetUsersController.php
├── Security/
│   ├── CurrentUserProvider.php               # Fourni User courant
│   └── SymfonyPasswordHasher.php             # Hash passwords
└── Gateway/ORM/
    ├── Entity/User.php                       # Doctrine Entity
    └── Repository/DoctrineUserRepository.php
```

### Configuration

```
src/Auth/Frameworks/config/
├── security.yaml                             # Firewall, access_control
├── services.yaml                             # DI container
└── routes.yaml                               # Routes Auth
```

---

## 7. Testing Strategy

### Unit Tests (UseCases)

**Pattern** : Mock repositories et gateways.

**Example** : `CreateUserTest`

```php
public function testExecuteCreatesUser(): void
{
    $request = new CreateUserRequest(
        email: EmailField::fromString('test@example.com'),
        plainPassword: 'SecurePass123',
        roles: [Role::USER],
    );

    $repository = $this->createMock(UserRepository::class);
    $passwordHasher = $this->createMock(PasswordHasherInterface::class);

    $passwordHasher->expects($this->once())
        ->method('hashPassword')
        ->with('SecurePass123')
        ->willReturn('$argon2id$hashed');

    $repository->expects($this->once())
        ->method('save')
        ->with($this->isInstanceOf(User::class));

    $useCase = new CreateUser($repository, $passwordHasher);

    $response = $useCase->execute($request);

    $this->assertInstanceOf(CreateUserResponse::class, $response);
}
```

### Functional Tests (Controllers)

**Example** : `LoginControllerTest`

```php
public function testLoginWithValidCredentialsSucceeds(): void
{
    // Créer User en DB
    $this->createUser('test@example.com', 'password123');

    $crawler = $this->client->request('GET', '/login');

    $form = $crawler->selectButton('Se connecter')->form([
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->client->submit($form);

    $this->assertResponseRedirects('/admin/dashboard');
}

public function testLoginWithInvalidCredentialsFails(): void
{
    $this->createUser('test@example.com', 'password123');

    $crawler = $this->client->request('GET', '/login');

    $form = $crawler->selectButton('Se connecter')->form([
        'email' => 'test@example.com',
        'password' => 'wrong_password',
    ]);

    $this->client->submit($form);

    $this->assertResponseRedirects('/login');
    $this->assertSelectorTextContains('.alert-error', 'Invalid credentials');
}
```

### Integration Tests (Password Reset)

**Example** : `PasswordResetWorkflowTest`

```php
public function testPasswordResetWorkflowSucceeds(): void
{
    $user = $this->createUser('test@example.com', 'old_password');

    // 1. Créer token reset
    $createResetToken = self::getContainer()->get(CreateResetToken::class);
    $response = $createResetToken->execute(
        new CreateResetTokenRequest($user->uuid())
    );

    $token = $response->token;

    // 2. Valider token
    $validateResetToken = self::getContainer()->get(ValidateResetToken::class);
    $validateResetToken->execute(new ValidateResetTokenRequest($token));

    // 3. Reset password
    $resetPassword = self::getContainer()->get(ResetPassword::class);
    $resetPassword->execute(
        new ResetPasswordRequest($token, 'new_password123')
    );

    // 4. Vérifier que l'ancien password ne marche plus
    $this->assertLoginFails('test@example.com', 'old_password');

    // 5. Vérifier que le nouveau password marche
    $this->assertLoginSucceeds('test@example.com', 'new_password123');

    // 6. Vérifier que le token est marqué comme utilisé
    $resetToken = $this->tokenRepository->findByToken($token);
    $this->assertTrue($resetToken->isUsed());
}
```

---

## Résumé

Ce guide documente l'implémentation complète de l'authentification et l'autorisation (Auth BC) :

✅ **Authentification** : Login/logout avec session Symfony
✅ **Autorisation** : RBAC avec `#[RequireRole]` attribute
✅ **Password management** : Hashing Argon2id + reset workflow
✅ **Inter-BC communication** : Contracts pour Admin BC (CreateUser, DisableUser)
✅ **Security** : Token cryptographique, expiration, usage unique
✅ **Tests** : Unit + Functional + Integration

**Pour aller plus loin** :
- Lire [ADR-005: Password Reset Workflow](adr/ADR-005-password-reset-workflow.md)
- Consulter [admin-employee-management.md](admin-employee-management.md) pour usage depuis Admin BC
- Consulter `docs/guides/bounded-contexts.md` pour patterns inter-BC
