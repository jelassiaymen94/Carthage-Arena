# Auth & Comptes — Documentation Technique

## Vue d'ensemble

Le module **Auth & Comptes** gère l'inscription, la connexion, la déconnexion, les tokens d'authentification persistants, et le contrôle du statut des comptes (actif / suspendu / supprimé). Il repose sur le composant **Symfony Security** avec un authentificateur personnalisé (`LoginFormAuthenticator`) et un système de tokens propre à l'application.

---

## Architecture

```
┌─────────────┐     ┌──────────────────────┐     ┌─────────────┐
│  Formulaire │────▶│ LoginFormAuthenticator│────▶│  AuthService│
│  de login   │     │  (authenticate)       │     │  (token)    │
└─────────────┘     └──────────┬───────────┘     └──────┬──────┘
                               │                        │
                    ┌──────────▼───────────┐     ┌──────▼──────┐
                    │    UserChecker        │     │  AuthToken   │
                    │ (pre-auth: status)   │     │  (entity DB) │
                    └──────────────────────┘     └─────────────┘
```

**Flux d'authentification complet :**

1. L'utilisateur soumet email + mot de passe sur `/connexion`
2. `LoginFormAuthenticator.authenticate()` crée un `Passport` avec `UserBadge`, `PasswordCredentials` et `CsrfTokenBadge`
3. Symfony charge l'utilisateur via `app_user_provider` (recherche par email)
4. **Avant validation du mot de passe**, `UserChecker.checkPreAuth()` vérifie le statut du compte :
   - `SUSPENDED` → exception « Votre compte a été suspendu. »
   - `DELETED` → exception « Ce compte n'existe plus. »
5. Symfony vérifie le mot de passe hashé
6. `onAuthenticationSuccess()` appelle `AuthService.authenticate($user)` qui :
   - Révoque l'ancien token s'il existe (1 seul token par utilisateur)
   - Génère un nouveau token de 64 caractères hex (`bin2hex(random_bytes(32))`)
   - Le persiste en base avec une expiration à +30 jours
7. L'utilisateur est redirigé vers le dashboard

---

## Entités

### User (`src/Entity/User.php`)

| Champ        | Type              | Contraintes                          | Description                          |
|--------------|-------------------|--------------------------------------|--------------------------------------|
| `id`         | `Uuid` (BINARY 16)| PK, auto-généré                      | Identifiant unique                   |
| `email`      | `string(180)`     | Unique, non null                     | Identifiant de connexion             |
| `username`   | `string(50)`      | Unique, non null                     | Nom d'utilisateur                    |
| `nickname`   | `string(50)`      | Nullable                             | Surnom affiché                        |
| `password`   | `string`          | Non null                             | Mot de passe hashé                   |
| `status`     | `AccountStatus`   | Enum, défaut `ACTIVE`                | Statut du compte                     |
| `roles`      | `json`            | Non null                             | Rôles (`ROLE_USER` ajouté auto.)     |
| `createdAt`  | `DateTimeImmutable`| Non null                            | Date de création                     |
| `balance`    | `int`             | Défaut `0`                           | Solde (monnaie du jeu)               |
| `profile`    | `Profile`         | OneToOne, cascade persist/remove     | Profil associé                       |
| `authToken`  | `AuthToken`       | OneToOne, cascade persist/remove, orphanRemoval | Token actif |

**Implémente** : `UserInterface`, `PasswordAuthenticatedUserInterface`

**`getUserIdentifier()`** renvoie l'email — c'est le champ utilisé par Symfony pour charger l'utilisateur.

**`getRoles()`** ajoute toujours `ROLE_USER` au tableau des rôles, garantissant que tout utilisateur a au minimum ce rôle.

---

### AuthToken (`src/Entity/AuthToken.php`)

| Champ       | Type               | Contraintes                            | Description                       |
|-------------|--------------------|-----------------------------------------|-----------------------------------|
| `id`        | `Uuid` (BINARY 16) | PK, auto-généré                        | Identifiant unique                |
| `value`     | `string(64)`       | Unique, non null                       | Valeur du token (64 hex chars)    |
| `expiresAt` | `DateTimeImmutable` | Non null                               | Date d'expiration (+30 jours)     |
| `createdAt` | `DateTimeImmutable` | Non null                               | Date de création                  |
| `user`      | `User`             | OneToOne, `onDelete: CASCADE`, non null | Utilisateur propriétaire          |

**Méthode clé** : `isExpired(): bool` — compare `expiresAt` avec la date actuelle.

**Relation** : OneToOne bidirectionnelle avec `User`. Le côté propriétaire est `AuthToken` (il porte la colonne `user_id` en base). La suppression d'un `User` entraîne la suppression de son `AuthToken` via `CASCADE`.

**Pourquoi 64 caractères ?** `bin2hex(random_bytes(32))` produit exactement 64 caractères hexadécimaux. 32 octets = 256 bits d'entropie, ce qui est largement suffisant pour un token non devinable.

---

### AccountStatus (`src/Enum/AccountStatus.php`)

```php
enum AccountStatus: string
{
    case ACTIVE    = 'active';     // Compte opérationnel
    case SUSPENDED = 'suspended';  // Compte temporairement bloqué
    case DELETED   = 'deleted';    // Compte supprimé (soft delete)
}
```

Stocké en `VARCHAR(255)` via l'option `enumType` de Doctrine. Ce n'est pas un soft-delete traditionnel (le row existe toujours), mais le `UserChecker` empêche toute connexion.

---

## Repository

### AuthTokenRepository (`src/Repository/AuthTokenRepository.php`)

| Méthode                                    | Retour       | Description                                        |
|--------------------------------------------|--------------|----------------------------------------------------|
| `findValidTokenByValue(string $value)`     | `?AuthToken` | Cherche un token non expiré par sa valeur           |
| `findTokenByUser(User $user)`              | `?AuthToken` | Cherche le token actif d'un utilisateur             |
| `deleteExpiredTokens()`                    | `int`        | Supprime en masse les tokens expirés, retourne le nombre supprimé |

**`findValidTokenByValue`** utilise un `WHERE value = :value AND expiresAt > :now` — cela garantit qu'un token expiré ne sera jamais considéré valide, même s'il existe encore en base.

**`deleteExpiredTokens`** utilise une requête DQL `DELETE` directe (pas d'hydratation d'entité), ce qui est performant pour un nettoyage en masse.

---

## Service

### AuthService (`src/Service/AuthService.php`)

| Méthode                              | Retour      | Description                                        |
|--------------------------------------|-------------|----------------------------------------------------|
| `authenticate(User $user)`           | `AuthToken` | Révoque l'ancien token, crée un nouveau (30 jours) |
| `revokeToken(AuthToken $token)`      | `void`      | Supprime un token spécifique                        |
| `revokeUserToken(User $user)`        | `void`      | Trouve et supprime le token de l'utilisateur        |
| `cleanupExpiredTokens()`             | `int`       | Délègue à `deleteExpiredTokens()` du repository     |

**Choix de design — un seul token par utilisateur** : `authenticate()` révoque systématiquement l'ancien token avant d'en créer un nouveau. Cela signifie qu'une connexion depuis un nouvel appareil invalide automatiquement la session précédente. C'est un choix de sécurité : **single-session enforcement**.

**Injection de dépendances** : `AuthService` reçoit `EntityManagerInterface` et `AuthTokenRepository` via le constructeur. Symfony les injecte automatiquement grâce à l'autowiring.

---

## Sécurité

### LoginFormAuthenticator (`src/Security/LoginFormAuthenticator.php`)

Étend `AbstractLoginFormAuthenticator` de Symfony.

**`authenticate(Request)`** :
- Lit `email` et `password` depuis le corps de la requête POST
- Stocke le dernier email en session (pour le pré-remplir en cas d'erreur)
- Retourne un `Passport` avec :
  - `UserBadge($email)` — Symfony charge l'utilisateur par email
  - `PasswordCredentials($password)` — Symfony vérifie le hash
  - `CsrfTokenBadge('authenticate', $csrfToken)` — protection CSRF

**`onAuthenticationSuccess()`** :
- Si un `targetPath` existe en session (l'utilisateur essayait d'accéder à une page protégée), il est redirigé vers cette page
- Sinon, appelle `AuthService.authenticate($user)` pour créer le token
- Redirige vers `app_dashboard`

---

### UserChecker (`src/Security/UserChecker.php`)

Implémente `UserCheckerInterface`. Enregistré dans `security.yaml` sous `user_checker`.

**`checkPreAuth(UserInterface $user)`** — appelé **avant** la vérification du mot de passe :
- Si `SUSPENDED` : lève `CustomUserMessageAccountStatusException('Votre compte a été suspendu.')`
- Si `DELETED` : lève `CustomUserMessageAccountStatusException('Ce compte n\'existe plus.')`

**Pourquoi `checkPreAuth` et pas `checkPostAuth` ?**
Le `checkPreAuth` s'exécute avant même de vérifier le mot de passe. Cela évite de gaspiller des ressources CPU sur le hashing si le compte est de toute façon bloqué. Le `checkPostAuth` est laissé vide pour une future utilisation (par exemple, vérifier qu'un email est confirmé).

**Pourquoi un `UserChecker` plutôt qu'un check dans `onAuthenticationSuccess` ?**
L'ancien code vérifiait le statut dans `onAuthenticationSuccess`, mais à ce stade la session est déjà créée — l'utilisateur est techniquement connecté. Le `UserChecker` intervient **avant** la création de la session, ce qui bloque réellement la connexion.

---

### LogoutSubscriber (`src/EventSubscriber/LogoutSubscriber.php`)

Écoute le `LogoutEvent` de Symfony.

Quand l'utilisateur se déconnecte :
1. Symfony intercepte la requête vers `/deconnexion`
2. Le `LogoutEvent` est dispatché
3. Le subscriber récupère l'utilisateur depuis le token de sécurité
4. Appelle `AuthService.revokeUserToken($user)` — supprime le token de la base
5. Symfony détruit la session et redirige vers `app_login`

---

## Contrôleur

### SecurityController (`src/Controller/SecurityController.php`)

| Route          | Chemin         | Méthode HTTP | Action                                     |
|----------------|----------------|--------------|---------------------------------------------|
| `app_login`    | `/connexion`   | GET, POST    | Affiche et traite le formulaire de connexion |
| `app_register` | `/inscription` | GET, POST    | Affiche et traite le formulaire d'inscription|
| `app_logout`   | `/deconnexion` | GET          | Intercepté par Symfony (ne s'exécute jamais) |

**Inscription (`app_register`)** :
1. Valide que tous les champs sont remplis
2. Vérifie que les mots de passe correspondent
3. Vérifie l'unicité de l'email et du username en base
4. Crée un `User` avec `status = ACTIVE` et le mot de passe hashé
5. Crée un `Profile` vide associé à l'utilisateur
6. Redirige vers la page de connexion

**Login (`app_login`)** :
- Si l'utilisateur est déjà connecté → redirige vers le dashboard
- Sinon → affiche le formulaire avec les erreurs éventuelles

---

## Configuration de sécurité (`config/packages/security.yaml`)

```yaml
security:
    password_hashers:
        PasswordAuthenticatedUserInterface: 'auto'   # bcrypt ou argon2 selon le serveur
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email                       # Connexion par email
    firewalls:
        main:
            lazy: true                                # Charge l'utilisateur à la demande
            provider: app_user_provider
            user_checker: App\Security\UserChecker    # Vérifie le statut du compte
            custom_authenticator: App\Security\LoginFormAuthenticator
            logout:
                path: app_logout
                target: app_login                     # Redirige vers /connexion après logout
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/profil, roles: ROLE_USER }
        - { path: ^/parametres, roles: ROLE_USER }
        - { path: ^/equipe, roles: ROLE_USER }
```

**`lazy: true`** : L'utilisateur n'est chargé depuis la base que quand on accède réellement à `$this->getUser()`. Sur les pages publiques, aucune requête SQL n'est faite.

**`access_control`** : Seule la première règle qui matche est appliquée. Les routes `/profil`, `/parametres`, `/equipe` exigent `ROLE_USER`. La page admin exige `ROLE_ADMIN`.

---

## Base de données

### Table `auth_token`

```sql
CREATE TABLE auth_token (
    id          BINARY(16)   NOT NULL,
    user_id     BINARY(16)   NOT NULL,
    value       VARCHAR(64)  NOT NULL,
    expires_at  DATETIME     NOT NULL,
    created_at  DATETIME     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX (value),
    UNIQUE INDEX (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
```

La contrainte `UNIQUE` sur `user_id` garantit au niveau de la base qu'il n'y a qu'un seul token par utilisateur — même si le code applicatif a un bug, la base empêche les doublons.

Migration : `migrations/Version20260211133608.php`

---

## Diagramme de séquence — Login

```
Utilisateur          Navigateur          LoginFormAuth       UserChecker        AuthService         BDD
    │                    │                    │                  │                  │                │
    │  email + mdp       │                    │                  │                  │                │
    │───────────────────▶│  POST /connexion   │                  │                  │                │
    │                    │───────────────────▶│                  │                  │                │
    │                    │                    │  checkPreAuth()  │                  │                │
    │                    │                    │─────────────────▶│                  │                │
    │                    │                    │                  │ status OK?       │                │
    │                    │                    │◀─────────────────│                  │                │
    │                    │                    │  vérif password   │                  │                │
    │                    │                    │                  │                  │                │
    │                    │                    │  authenticate()   │                  │                │
    │                    │                    │─────────────────────────────────────▶│                │
    │                    │                    │                  │                  │ DELETE old     │
    │                    │                    │                  │                  │───────────────▶│
    │                    │                    │                  │                  │ INSERT new     │
    │                    │                    │                  │                  │───────────────▶│
    │                    │                    │◀─────────────────────────────────────│                │
    │                    │  302 → /dashboard  │                  │                  │                │
    │                    │◀──────────────────│                  │                  │                │
    │  page dashboard    │                    │                  │                  │                │
    │◀───────────────────│                    │                  │                  │                │
```

---

## Diagramme de séquence — Logout

```
Utilisateur        Navigateur        Symfony Security     LogoutSubscriber     AuthService        BDD
    │                  │                    │                    │                  │               │
    │  clic logout     │                    │                    │                  │               │
    │─────────────────▶│  GET /deconnexion  │                    │                  │               │
    │                  │───────────────────▶│                    │                  │               │
    │                  │                    │  LogoutEvent       │                  │               │
    │                  │                    │───────────────────▶│                  │               │
    │                  │                    │                    │ revokeUserToken() │               │
    │                  │                    │                    │─────────────────▶│               │
    │                  │                    │                    │                  │ DELETE token  │
    │                  │                    │                    │                  │──────────────▶│
    │                  │                    │  destroy session   │                  │               │
    │                  │  302 → /connexion  │                    │                  │               │
    │                  │◀──────────────────│                    │                  │               │
    │  page login      │                    │                    │                  │               │
    │◀─────────────────│                    │                    │                  │               │
```

---

## Points clés pour le pitch

1. **Séparation des responsabilités** : Le `UserChecker` gère le statut du compte, le `LoginFormAuthenticator` gère l'authentification, le `AuthService` gère les tokens. Chaque classe a une seule responsabilité.

2. **Single-session enforcement** : Un seul token par utilisateur. Si quelqu'un vole des credentials, la victime qui se reconnecte invalide automatiquement le token du voleur.

3. **Protection CSRF** : Chaque formulaire de login inclut un `CsrfTokenBadge`, empêchant les attaques cross-site request forgery.

4. **Nettoyage des tokens** : `AuthService.cleanupExpiredTokens()` peut être appelé via une commande Symfony planifiée (cron) pour purger les tokens expirés sans surcharger la table.

5. **Soft-delete des comptes** : Le statut `DELETED` ne supprime pas le row — il bloque simplement la connexion. Cela permet de conserver l'historique des matches et stats.

6. **Entropie du token** : 256 bits (32 octets) de données aléatoires cryptographiquement sûres, soit un espace de 2^256 possibilités — impossible à bruteforcer.
