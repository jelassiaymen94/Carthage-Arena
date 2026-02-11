# Profils & Équipes — Documentation Technique

## Vue d'ensemble

Le module **Profils & Équipes** gère les profils utilisateurs (bio, avatar, paramètres) et le système complet de gestion d'équipes (création, invitation, rôles, dissolution). Il s'articule autour de trois entités principales : `Profile`, `Team`, et `TeamMembership`, avec un pattern de membership qui découple l'utilisateur de l'équipe.

---

## Architecture

```
┌──────────┐ OneToOne  ┌──────────┐
│   User   │◀─────────▶│  Profile  │
│          │           │ bio       │
│          │           │ avatarUrl │
└────┬─────┘           └──────────┘
     │
     │ OneToMany
     ▼
┌──────────────────┐ ManyToOne  ┌──────────┐
│  TeamMembership  │───────────▶│   Team   │
│  role (enum)     │           │ name      │
│  joinedAt        │           │ tag       │
└──────────────────┘           │ inviteCode│
                               │ captain   │
                               │ status    │
                               └──────────┘
```

**Pourquoi un `TeamMembership` intermédiaire plutôt qu'un ManyToMany direct ?**
La table pivot `TeamMembership` porte des données propres : le `role` du joueur dans l'équipe et la date d'adhésion `joinedAt`. Un ManyToMany Doctrine ne permet pas d'ajouter des colonnes à la table de jointure. C'est un pattern classique appelé **association entity**.

---

## Entités

### Profile (`src/Entity/Profile.php`)

| Champ       | Type               | Contraintes              | Description                        |
|-------------|--------------------|--------------------------|------------------------------------|
| `id`        | `Uuid` (BINARY 16) | PK, auto-généré         | Identifiant unique                 |
| `bio`       | `string(500)`      | Nullable                 | Biographie de l'utilisateur        |
| `avatarUrl` | `string(255)`      | Nullable                 | Nom du fichier avatar              |
| `createdAt` | `DateTimeImmutable` | Non null                 | Date de création                   |
| `updatedAt` | `DateTime`         | Nullable                 | Dernière modification              |
| `user`      | `User`             | OneToOne, non null       | Utilisateur propriétaire           |

**Pourquoi séparer `Profile` de `User` ?**
Le `User` est chargé à chaque requête authentifiée (via le security provider). Si le profil (bio, avatar, etc.) était directement dans `User`, ces données seraient systématiquement chargées même quand elles ne sont pas nécessaires. Avec un `OneToOne(lazy)`, le profil n'est chargé que lorsqu'on y accède — optimisation des performances.

**Création automatique** : Le profil est créé au moment de l'inscription (`SecurityController.register()`) et peut être modifié via les paramètres.

---

### Team (`src/Entity/Team.php`)

| Champ        | Type               | Contraintes                     | Description                          |
|--------------|--------------------|---------------------------------|--------------------------------------|
| `id`         | `Uuid` (BINARY 16) | PK, auto-généré                | Identifiant unique                   |
| `name`       | `string(100)`      | Unique, non null                | Nom de l'équipe                      |
| `tag`        | `string(5)`        | Unique, non null                | Abréviation (3-5 chars, uppercase)   |
| `description`| `string(500)`      | Nullable                        | Description de l'équipe              |
| `status`     | `TeamStatus`       | Enum, défaut `ACTIVE`           | Statut de l'équipe                   |
| `createdAt`  | `DateTimeImmutable` | Non null                        | Date de création                     |
| `inviteCode` | `string(10)`       | Unique, non null                | Code d'invitation (8 chars)          |
| `captain`    | `User`             | ManyToOne, non null             | Capitaine de l'équipe                |
| `members`    | `Collection<TeamMembership>` | OneToMany, cascade persist/remove | Liste des membres |

**`setTag()`** convertit automatiquement le tag en majuscules — c'est une règle métier encodée dans le setter.

**`inviteCode`** est un code aléatoire de 8 caractères majuscules, généré côté contrôleur. Il sert de "mot de passe" pour rejoindre l'équipe.

**`captain`** est une référence directe au `User` capitaine. C'est un raccourci pour éviter de parcourir les memberships à chaque fois qu'on veut savoir qui est le capitaine.

---

### TeamMembership (`src/Entity/TeamMembership.php`)

| Champ      | Type               | Contraintes                         | Description                       |
|------------|--------------------|------------------------------------|-----------------------------------|
| `id`       | `Uuid` (BINARY 16) | PK, auto-généré                   | Identifiant unique                |
| `team`     | `Team`             | ManyToOne, non null                | Équipe                            |
| `player`   | `User`             | ManyToOne, non null                | Joueur                            |
| `role`     | `TeamRole`         | Enum, non null                     | Rôle dans l'équipe                |
| `joinedAt` | `DateTimeImmutable` | Non null                           | Date d'adhésion                   |

**Contrainte d'unicité** : `UniqueConstraint(fields: ['team', 'player'])` — un joueur ne peut apparaître qu'une seule fois dans une équipe donnée.

---

## Enums

### TeamRole (`src/Enum/TeamRole.php`)

```php
enum TeamRole: string
{
    case CAPTAIN    = 'captain';      // Chef d'équipe — droits complets
    case CO_CAPTAIN = 'co_captain';   // Adjoint — droits étendus (futur)
    case MEMBER     = 'member';       // Membre standard
}
```

### TeamStatus (`src/Enum/TeamStatus.php`)

```php
enum TeamStatus: string
{
    case ACTIVE    = 'active';      // Équipe opérationnelle
    case INACTIVE  = 'inactive';    // Temporairement inactive
    case DISBANDED = 'disbanded';   // Équipe dissoute
}
```

---

## Repositories

### ProfileRepository (`src/Repository/ProfileRepository.php`)

Hérite de `ServiceEntityRepository<Profile>`. Pas de méthodes personnalisées — utilise les méthodes standard de Doctrine (`find`, `findOneBy`, etc.).

### TeamRepository (`src/Repository/TeamRepository.php`)

| Méthode                                  | Retour  | Description                              |
|------------------------------------------|---------|------------------------------------------|
| `findOneByInviteCode(string $code)`      | `?Team` | Recherche une équipe par code d'invitation |

### TeamMembershipRepository (`src/Repository/TeamMembershipRepository.php`)

Hérite de `ServiceEntityRepository<TeamMembership>`. Pas de méthodes personnalisées.

---

## Contrôleurs

### ProfileController (`src/Controller/ProfileController.php`)

| Route         | Chemin    | Méthode | Action                          |
|---------------|-----------|---------|---------------------------------|
| `app_profile` | `/profil` | GET     | Affiche le profil de l'utilisateur |

**Données transmises au template :**
- Infos utilisateur : nom, email, rôle, solde, avatar, rang, niveau, date d'inscription, pays, bio
- Statistiques : matchs joués, victoires, défaites, winrate, tournois gagnés, gains totaux
- Matchs récents (tableau, actuellement vide — préparé pour la future fonctionnalité)
- Achievements (tableau, actuellement vide)

> **Note** : Les statistiques sont actuellement des **données mock** (valeurs en dur). Elles seront remplacées par des calculs réels quand le module Tournois sera implémenté.

---

### TeamController (`src/Controller/TeamController.php`)

| Route                       | Chemin                  | Méthode   | Action                                  |
|-----------------------------|-------------------------|-----------|-----------------------------------------|
| `app_team`                  | `/equipe`               | GET       | Affiche l'équipe ou l'état "sans équipe" |
| `app_team_create`           | `/equipe/creer`         | GET, POST | Créer une nouvelle équipe                |
| `app_team_join`             | `/equipe/rejoindre`     | GET, POST | Rejoindre une équipe avec un code        |
| `app_team_leave`            | `/equipe/quitter`       | POST      | Quitter son équipe                       |
| `app_team_disband`          | `/equipe/dissoudre`     | POST      | Dissoudre l'équipe (capitaine)           |
| `app_team_regenerate_invite`| `/equipe/nouveau-code`  | POST      | Regénérer le code d'invitation           |
| `app_team_kick`             | `/equipe/expulser/{id}` | POST      | Expulser un membre                       |

---

#### Créer une équipe (`app_team_create`)

**Validation :**
- L'utilisateur ne doit pas déjà avoir une équipe
- Nom : 3-50 caractères, doit être unique en base
- Tag : 3-5 caractères, converti en majuscules, doit être unique
- Description : optionnelle
- Protection CSRF

**Logique métier :**
1. Génère un code d'invitation aléatoire de 8 caractères (`strtoupper(substr(bin2hex(random_bytes(4)), 0, 8))`)
2. Crée l'entité `Team` avec les champs du formulaire
3. Crée un `TeamMembership` avec le rôle `CAPTAIN` pour le créateur
4. Définit l'utilisateur comme `captain` de la team
5. Persiste les deux entités et flush

**Le créateur est automatiquement capitaine** — c'est une règle métier fondamentale.

---

#### Rejoindre une équipe (`app_team_join`)

**Validation :**
- L'utilisateur ne doit pas déjà avoir une équipe
- Le code d'invitation doit correspondre à une équipe existante
- L'équipe doit être `ACTIVE`
- L'équipe doit avoir moins de 8 membres
- Protection CSRF

**Règle des 8 membres maximum** : C'est une limite arbitraire adaptée aux formats de tournois esports classiques (5v5 + remplaçants).

---

#### Quitter l'équipe (`app_team_leave`)

**Cas complexe — transfert de capitanat :**

```
Le capitaine quitte ?
    ├── OUI et il reste des membres
    │       → Le prochain membre devient capitaine
    │       → Son membership.role passe à CAPTAIN
    │       → team.captain est mis à jour
    │
    ├── OUI et c'est le dernier membre
    │       → L'équipe passe en statut DISBANDED
    │       → L'équipe est effectivement dissoute
    │
    └── NON (membre simple)
            → Son membership est simplement supprimé
```

Ce mécanisme de succession automatique évite qu'une équipe se retrouve sans capitaine.

---

#### Dissoudre l'équipe (`app_team_disband`)

- Réservé au **capitaine**
- Met le statut de l'équipe à `DISBANDED`
- Supprime **tous** les memberships (chaque membre est désinscrit)
- Opération irréversible

---

#### Regénérer le code d'invitation (`app_team_regenerate_invite`)

- Réservé au **capitaine**
- Génère un nouveau code aléatoire de 8 caractères
- L'ancien code devient immédiatement invalide
- Utile si le code a fuité

---

#### Expulser un membre (`app_team_kick`)

**Validation :**
- L'utilisateur doit être capitaine
- Le membership ciblé doit appartenir à la même équipe
- On ne peut pas s'expulser soi-même
- Protection CSRF

---

### SettingsController (`src/Controller/SettingsController.php`)

| Route          | Chemin         | Méthode   | Action                              |
|----------------|----------------|-----------|-------------------------------------|
| `app_settings` | `/parametres`  | GET, POST | Gestion des paramètres du profil    |

**Champs modifiables :**

| Champ      | Validation                              | Entité affectée |
|------------|-----------------------------------------|-----------------|
| `username` | Min 3 caractères, unique                | `User`          |
| `email`    | Format email valide, unique             | `User`          |
| `bio`      | Max 500 caractères                      | `Profile`       |
| `avatar`   | JPEG/PNG/WebP, max 2 Mo                | `Profile`       |

**Upload d'avatar :**
1. Vérifie le type MIME (`image/jpeg`, `image/png`, `image/webp`)
2. Vérifie la taille (< 2 Mo)
3. Génère un nom unique : `uniqid() . '.' . extension`
4. Supprime l'ancien avatar s'il existe
5. Déplace le fichier vers `/public/uploads/avatars/`
6. Met à jour `profile.avatarUrl`

**Création automatique de profil** : Si l'utilisateur modifie sa bio et n'a pas encore de profil, le contrôleur en crée un à la volée et l'associe au `User`.

---

## Templates

### Profil (`templates/profile/index.html.twig`)

**Sections :**
- **En-tête** : Avatar (ou placeholder), nom, rôle, rang, solde, pays, date d'inscription, bio
- **Bouton** : « Modifier le Profil » → lien vers `/parametres`
- **Statistiques** : Grille de cards avec matchs, victoires, défaites, winrate, tournois
- **Matchs récents** : Liste (vide pour l'instant)
- **Achievements** : Liste (vide pour l'instant)

### Équipe (`templates/team/index.html.twig`)

**Sections :**
- **En-tête** : Nom, tag, niveau, description, statistiques
- **Roster** : Liste des membres avec avatars, rôles, indicateurs de statut
- **Actions capitaine** :
  - Boutons d'expulsion à côté de chaque membre
  - Code d'invitation avec bouton de copie
  - Formulaire de regénération du code
  - Section de gestion (modifier, invitations, dissoudre)
- **Action membre** : Formulaire « Quitter l'équipe »
- **Tournois** : Inscriptions en cours (section préparée)

### Sans équipe (`templates/team/no_team.html.twig`)

- Message d'état vide
- Deux boutons : « Créer une Équipe » et « Rejoindre une Équipe »

### Créer une équipe (`templates/team/create.html.twig`)

- Formulaire : nom (3-50), tag (3-5, majuscules), description (optionnel)
- Encadré informatif : capitaine auto, 8 membres max, inscription tournois par le capitaine

### Rejoindre une équipe (`templates/team/join.html.twig`)

- Champ unique : code d'invitation (stylisé en majuscules, espacement large)
- Bouton retour vers la page d'équipe

### Paramètres (`templates/settings/index.html.twig`)

**Sections :**
- **Profil** : Avatar upload avec prévisualisation JS, username, email, langue, timezone, pays, bio
- **Sécurité** : Changement de mot de passe (placeholder — contact support)
- **Notifications** : Toggles email, push, boutique, rappels tournois
- **Confidentialité** : Toggles profil public, afficher statistiques
- **Zone de danger** : Bouton suppression de compte (rouge)

> **Note** : Les sections Notifications, Confidentialité et Zone de danger sont des **placeholder UI** — les toggles ne sont pas encore connectés au backend.

---

## Règles métier résumées

### Profil
1. Un profil est créé automatiquement à l'inscription
2. L'avatar est stocké dans `/public/uploads/avatars/`
3. Formats acceptés : JPEG, PNG, WebP — max 2 Mo
4. La bio est limitée à 500 caractères
5. Le profil est en relation `OneToOne` avec `User`

### Équipes
1. Un utilisateur ne peut appartenir qu'à **une seule équipe** à la fois
2. Capacité maximale : **8 membres** par équipe
3. Le tag est toujours en **majuscules** (3-5 caractères)
4. Le nom et le tag doivent être **uniques**
5. Le code d'invitation est un code aléatoire de **8 caractères majuscules**

### Rôles et permissions

| Action                    | CAPTAIN | CO_CAPTAIN | MEMBER |
|---------------------------|---------|------------|--------|
| Voir l'équipe             | ✅      | ✅          | ✅     |
| Quitter l'équipe          | ✅*     | ✅          | ✅     |
| Expulser un membre        | ✅      | ❌          | ❌     |
| Dissoudre l'équipe        | ✅      | ❌          | ❌     |
| Regénérer le code         | ✅      | ❌          | ❌     |
| Inscrire à un tournoi     | ✅      | ❌          | ❌     |

\* Si le capitaine quitte, le capitanat est transféré automatiquement au prochain membre.

### Cycle de vie d'une équipe

```
Création ──▶ ACTIVE ──▶ DISBANDED
                │
                └──▶ INACTIVE (usage futur)
```

- `ACTIVE` : l'équipe fonctionne normalement, les membres la voient
- `INACTIVE` : prévue pour une désactivation temporaire (pas encore utilisée dans le code)
- `DISBANDED` : l'équipe est dissoute, tous les memberships sont supprimés, l'équipe n'apparaît plus

---

## Diagramme de séquence — Création d'équipe

```
Utilisateur       Navigateur         TeamController          BDD
    │                 │                    │                   │
    │  Formulaire     │                    │                   │
    │────────────────▶│  POST /equipe/creer│                   │
    │                 │───────────────────▶│                   │
    │                 │                    │ vérif pas d'équipe │
    │                 │                    │ vérif nom unique   │
    │                 │                    │ vérif tag unique   │
    │                 │                    │                   │
    │                 │                    │ CREATE Team        │
    │                 │                    │──────────────────▶│
    │                 │                    │ CREATE Membership  │
    │                 │                    │  (role: CAPTAIN)   │
    │                 │                    │──────────────────▶│
    │                 │                    │                   │
    │                 │  302 → /equipe     │                   │
    │                 │◀──────────────────│                   │
    │  page équipe    │                    │                   │
    │◀────────────────│                    │                   │
```

---

## Diagramme de séquence — Rejoindre une équipe

```
Utilisateur       Navigateur         TeamController          BDD
    │                 │                    │                   │
    │  Code invite    │                    │                   │
    │────────────────▶│ POST /equipe/rejoindre                │
    │                 │───────────────────▶│                   │
    │                 │                    │ vérif pas d'équipe │
    │                 │                    │ findByInviteCode   │
    │                 │                    │──────────────────▶│
    │                 │                    │◀──────────────────│
    │                 │                    │ vérif team ACTIVE  │
    │                 │                    │ vérif < 8 membres  │
    │                 │                    │                   │
    │                 │                    │ CREATE Membership  │
    │                 │                    │  (role: MEMBER)    │
    │                 │                    │──────────────────▶│
    │                 │                    │                   │
    │                 │  302 → /equipe     │                   │
    │                 │◀──────────────────│                   │
    │  page équipe    │                    │                   │
    │◀────────────────│                    │                   │
```

---

## Diagramme de séquence — Capitaine quitte l'équipe

```
Capitaine         Navigateur         TeamController          BDD
    │                 │                    │                   │
    │  Quitter        │                    │                   │
    │────────────────▶│ POST /equipe/quitter                  │
    │                 │───────────────────▶│                   │
    │                 │                    │                   │
    │                 │                    │ membership.role    │
    │                 │                    │  == CAPTAIN ?      │
    │                 │                    │                   │
    │                 │                    │ count(members) > 1?│
    │                 │                    │   OUI:             │
    │                 │                    │   next.role=CAPTAIN│
    │                 │                    │   team.captain=next│
    │                 │                    │──────────────────▶│
    │                 │                    │                   │
    │                 │                    │   NON:             │
    │                 │                    │   team.status =    │
    │                 │                    │     DISBANDED      │
    │                 │                    │──────────────────▶│
    │                 │                    │                   │
    │                 │                    │ DELETE membership  │
    │                 │                    │──────────────────▶│
    │                 │                    │                   │
    │                 │  302 → /equipe     │                   │
    │                 │◀──────────────────│                   │
```

---

## Points clés pour le pitch

1. **Pattern Association Entity** : `TeamMembership` porte le rôle et la date d'adhésion — impossible avec un simple ManyToMany. C'est un design pattern reconnu en modélisation de données.

2. **Single-team constraint** : Un joueur ne peut être que dans une seule équipe. Vérifié côté applicatif (le contrôleur vérifie `teamMemberships` avant de créer/rejoindre). Renforcé côté base par la contrainte d'unicité `(team, player)`.

3. **Succession automatique du capitaine** : Quand le capitaine quitte, le système ne laisse pas l'équipe orpheline — il y a toujours un capitaine ou l'équipe est dissoute.

4. **Lazy-loading du profil** : Comme `Profile` est en `OneToOne` séparé de `User`, il n'est chargé en base que quand on y accède. Sur les pages qui n'affichent pas le profil, zéro requête supplémentaire.

5. **Validation multi-couche** : Les contraintes d'unicité (nom d'équipe, tag, email, username) sont vérifiées à la fois côté applicatif (messages d'erreur user-friendly) et côté base (contraintes `UNIQUE` en dernier filet de sécurité).

6. **Code d'invitation comme mécanisme d'accès** : Pas de système d'invitation complexe avec notifications — un simple code partageable. Simple, efficace, et suffisant pour le MVP.

7. **Upload sécurisé** : Validation du type MIME, limitation de taille, nom de fichier unique (pas de collision), suppression de l'ancien fichier — les bases de la sécurité upload sont couvertes.
