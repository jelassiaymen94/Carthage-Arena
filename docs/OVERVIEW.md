# Carthage Arena — Vue d'ensemble du projet

**Carthage Arena** est une **plateforme de tournois e-sport Symfony 6.4** où les joueurs peuvent créer des comptes, former des équipes, participer à des tournois et dépenser de la monnaie virtuelle (Carthage Points - CP) dans une boutique.

---

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| **Backend** | PHP 8.1+, Symfony 6.4 (LTS) |
| **Base de données** | MySQL (hébergement AlwaysData) |
| **ORM** | Doctrine 3.6+ |
| **Frontend** | Twig + Tailwind CSS (CDN) + Stimulus/Turbo |
| **Authentification** | Symfony Security avec `LoginFormAuthenticator` personnalisé |
| **Identifiants** | UUID (BINARY 16) sur toutes les entités |
| **Police** | Be Vietnam Pro |
| **Icônes** | Material Symbols Outlined |

---

## Architecture

### Entités principales (15 au total)

```
User (implémente UserInterface)
 ├── Profile (OneToOne - bio, avatar)
 ├── AuthToken (OneToOne - auth session unique)
 ├── License (OneToOne - licence arbitre)
 └── TeamMembership[] (OneToMany - associations équipe)

Team
 ├── captain (ManyToOne → User)
 ├── members: TeamMembership[] (OneToMany)
 └── inviteCode (code aléatoire 8 caractères)

Tournoi
 ├── Game (ManyToOne)
 ├── Team[] (ManyToMany - équipes inscrites)
 ├── MatchEntity[] (OneToMany - matches du bracket)
 ├── winner (ManyToOne → Team)
 └── referee (ManyToOne → User)

MatchEntity
 ├── Tournoi (ManyToOne)
 ├── team1, team2 (ManyToOne → Team)
 ├── winner (ManyToOne → Team)
 └── score (JSON)

Game
 ├── Tournoi[] (OneToMany)
 └── Skin[] (OneToMany - cosmétiques jeu)

Skin (Article boutique)
 ├── Game (ManyToOne)
 ├── price (int - CP)
 └── rarity (enum: COMMON, RARE, EPIC, LEGENDARY)

Reclamation (Ticket support)
 ├── author (ManyToOne → User)
 └── ReclamationResponse[] (OneToMany)
```

---

## Rôles utilisateurs et permissions

| Rôle | Description | Accès |
|------|-------------|-------|
| `ROLE_USER` | Par défaut (ajouté automatiquement) | Pages authentifiées |
| `ROLE_REFEREE` | Arbitre (requiert une licence) | + Arbitrer les tournois |
| `ROLE_ADMIN` | Administrateur | + Panneau `/admin` |

---

## Enums (12 au total)

| Enum | Valeurs |
|------|--------|
| `AccountStatus` | ACTIVE, SUSPENDED, DELETED |
| `TeamRole` | CAPTAIN, CO_CAPTAIN, MEMBER |
| `TeamStatus` | ACTIVE, INACTIVE, DISBANDED |
| `TournamentStatus` | UPCOMING, REGISTRATION_OPEN, IN_PROGRESS, COMPLETED, CANCELLED |
| `TournamentType` | SINGLE_ELIMINATION, DOUBLE_ELIMINATION, ROUND_ROBIN, SWISS |
| `MatchStatus` | SCHEDULED, IN_PROGRESS, COMPLETED, CANCELLED |
| `GameType` | FPS, MOBA, RTS, SPORTS, FIGHTING, BATTLE_ROYALE |
| `GameStatus` | ACTIVE, INACTIVE |
| `SkinRarity` | COMMON, RARE, EPIC, LEGENDARY |
| `ReclamationStatus` | PENDING, IN_PROGRESS, RESOLVED, CLOSED |
| `ReclamationPriority` | LOW, MEDIUM, HIGH, URGENT |
| `ReclamationCategory` | TECHNICAL, ACCOUNT, TOURNAMENT, PAYMENT, OTHER |

---

## Structure des routes

### Routes publiques

| Route | Chemin | Description |
|-------|--------|-------------|
| `app_login` | `/connexion` | Connexion |
| `app_register` | `/inscription` | Inscription (joueur/arbitre) |

### Routes authentifiées (`ROLE_USER`)

| Route | Chemin | Description |
|-------|--------|-------------|
| `app_dashboard` | `/` | Tableau de bord |
| `app_profile` | `/profil` | Profil utilisateur |
| `app_settings` | `/parametres` | Paramètres + avatar |
| `app_team` | `/equipe` | Gestion d'équipe (créer, rejoindre, quitter, dissoudre) |
| `app_tournaments` | `/tournois` | Liste et détails des tournois |
| `app_shop` | `/boutique` | Boutique (skins, merch) |
| `app_reclamation` | `/reclamations` | Tickets de support |

### Routes administrateur (`ROLE_ADMIN`)

| Route | Chemin | Description |
|-------|--------|-------------|
| `admin_dashboard` | `/admin/` | Tableau de bord admin |
| `admin_users` | `/admin/users` | Gestion des utilisateurs |
| `admin_tournaments` | `/admin/tournaments` | Gestion des tournois |
| `admin_games` | `/admin/games` | Gestion des jeux |
| `admin_shop` | `/admin/shop` | Gestion de la boutique |
| `admin_reclamations` | `/admin/reclamations` | Tickets de support |

---

## Patterns de conception clés

### 1. Pattern Entity Association

`TeamMembership` lie User ↔ Team avec les métadonnées `role` et `joinedAt` (pas un simple ManyToMany).

### 2. Profile chargé à la demande

Profile est séparé de User pour les performances - évite de charger bio/avatar à chaque requête authentifiée.

### 3. Application d'une session unique

Un seul `AuthToken` par utilisateur ; une nouvelle connexion invalide la session précédente.

### 4. Soft-delete via Enum

`AccountStatus::DELETED` bloque la connexion mais préserve les données.

### 5. Système de code d'invitation

Codes simples de 8 caractères pour rejoindre une équipe (pas de système d'invitation complexe).

---

## Contrôleurs (13 au total)

| Contrôleur | Responsabilité |
|------------|----------------|
| `SecurityController` | Connexion, inscription, déconnexion |
| `DashboardController` | Tableau de bord principal |
| `ProfileController` | Profil utilisateur |
| `SettingsController` | Paramètres + avatar |
| `TeamController` | CRUD équipe, système d'invitation |
| `TournamentController` | Liste/détails tournois |
| `ShopController` | Articles boutique |
| `ReclamationController` | Tickets de support |
| `LicenseController` | Gestion des licences |
| `Admin\AdminDashboardController` | Panneau admin |
| `Admin\ReclamationController` | Gestion support admin |

---

## Services

| Service | Responsabilité |
|---------|----------------|
| `AuthService` | Création/révocation des tokens d'authentification |
| `MatchGeneratorService` | Génération des brackets de tournoi |

---

## Formulaires

| Formulaire | Usage |
|------------|-------|
| `RegistrationType` | Inscription (choix joueur/arbitre + licence) |
| `ProfileUpdateType` | Mise à jour profil avec avatar |
| `TeamCreateType` | Création d'équipe |
| `TeamType` | Modification d'équipe |
| `TournoiType` | Création/modification tournoi |
| `GameType` | Gestion des jeux |
| `SkinType` | Gestion des skins |
| `MerchType` | Gestion du merch |
| `ReclamationType` | Création de ticket support |
| `AdminUserType` | Modification utilisateur (admin) |
| `AdminNewUserType` | Création utilisateur (admin) |

---

## Commandes personnalisées

```bash
# Démarrer le serveur de développement
symfony server:start

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Générer une migration après modification des entités
php bin/console doctrine:migrations:diff

# Vider le cache
php bin/console cache:clear

# Peupler la base de données
php bin/console app:seed-data
php bin/console app:seed-tournaments

# Promouvoir un utilisateur admin
php bin/console app:promote-admin
```

---

## Conventions

- **Langue** : Français pour l'UI/routes/messages flash, Anglais pour le code (noms de classes, variables, méthodes)
- **Routes** : Chemins en français (`/connexion`, `/inscription`, `/equipe`, `/boutique`, `/parametres`)
- **Identifiants** : UUID sur toutes les entités (jamais d'auto-incrément)
- **Enums** : PHP 8.1 backed enums stockés comme VARCHAR
- **Formulaires** : Attribut `novalidate`, validation côté serveur uniquement via Symfony Forms + Assert
- **Templates** : Classes utilitaires Tailwind directement dans Twig
- **Messages flash** : Types `success`, `error`, `warning`
- **Police** : Be Vietnam Pro
- **Icônes** : Material Symbols Outlined

---

## Structure des dossiers

```
src/
├── Controller/
│   ├── SecurityController.php
│   ├── DashboardController.php
│   ├── ProfileController.php
│   ├── SettingsController.php
│   ├── TeamController.php
│   ├── TournamentController.php
│   ├── ShopController.php
│   ├── ReclamationController.php
│   ├── LicenseController.php
│   └── Admin/
│       ├── AdminDashboardController.php
│       └── ReclamationController.php
├── Entity/
│   ├── User.php
│   ├── Profile.php
│   ├── Team.php
│   ├── TeamMembership.php
│   ├── AuthToken.php
│   ├── License.php
│   ├── Tournoi.php
│   ├── MatchEntity.php
│   ├── Game.php
│   ├── Skin.php
│   ├── Product.php
│   ├── Merch.php
│   ├── Reclamation.php
│   └── ReclamationResponse.php
├── Enum/
│   ├── AccountStatus.php
│   ├── TeamRole.php
│   ├── TeamStatus.php
│   ├── TournamentStatus.php
│   ├── TournamentType.php
│   ├── MatchStatus.php
│   ├── GameType.php
│   ├── GameStatus.php
│   ├── SkinRarity.php
│   ├── ReclamationStatus.php
│   ├── ReclamationPriority.php
│   └── ReclamationCategory.php
├── Form/
│   ├── RegistrationType.php
│   ├── ProfileUpdateType.php
│   ├── TeamCreateType.php
│   ├── TeamType.php
│   ├── TournoiType.php
│   ├── GameType.php
│   ├── SkinType.php
│   ├── MerchType.php
│   ├── ReclamationType.php
│   ├── AdminUserType.php
│   └── AdminNewUserType.php
├── Repository/
├── Security/
│   ├── LoginFormAuthenticator.php
│   └── UserChecker.php
├── Service/
│   ├── AuthService.php
│   └── MatchGeneratorService.php
├── Validator/
│   └── Constraints/
│       ├── ValidLicense.php
│       └── ValidLicenseValidator.php
├── EventSubscriber/
│   └── LogoutSubscriber.php
└── Command/
    ├── SeedDataCommand.php
    ├── SeedTournamentsCommand.php
    └── PromoteAdminCommand.php

templates/
├── base.html.twig
├── base_admin.html.twig
├── components/
│   ├── _sidebar.html.twig
│   └── _header_user_pill.html.twig
├── security/
├── dashboard/
├── profile/
├── settings/
├── team/
├── tournament/
├── shop/
├── reclamation/
├── tournoi/
└── admin/

docs/
├── AUTH_ET_COMPTES.md
├── PROFILS_ET_EQUIPES.md
└── OVERVIEW.md

migrations/
public/uploads/avatars/
```

---

## Migrations base de données

15 migrations suivent l'évolution complète de la base de données, de la configuration initiale à l'état actuel.

---

Ce projet Symfony est bien structuré et suit les bonnes pratiques avec une séparation appropriée des responsabilités, des identifiants UUID, et un modèle de domaine complet pour la gestion de tournois e-sport.
