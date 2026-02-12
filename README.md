# 🏟️ Carthage Arena

Plateforme esports de gestion de tournois développée avec Symfony 6.4.

## 📋 Description

**Carthage Arena** est une application web permettant aux joueurs de créer des comptes, former des équipes, participer à des tournois et dépenser de la monnaie virtuelle (Carthage Points — CP) dans une boutique. Les arbitres officient les matchs et les administrateurs gèrent l'ensemble du système.

## 🛠️ Stack Technique

- **Backend** : PHP 8.1+, Symfony 6.4 (LTS)
- **Base de données** : MySQL
- **ORM** : Doctrine 3.6+
- **Frontend** : Twig + Tailwind CSS (CDN) + Stimulus/Turbo (Hotwired)
- **Authentification** : Symfony Security avec `LoginFormAuthenticator` personnalisé
- **Identifiants** : UUID (BINARY 16) sur toutes les entités

## ⚙️ Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL
- Symfony CLI (optionnel, recommandé)

## 🚀 Installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/votre-utilisateur/carthage-arena.git
   cd carthage-arena
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configurer l'environnement**
   ```bash
   cp .env .env.local
   ```
   Modifier `.env.local` avec vos informations de base de données :
   ```
   DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/carthage_arena"
   ```

4. **Créer la base de données et exécuter les migrations**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Lancer le serveur de développement**
   ```bash
   symfony server:start
   ```
   Ou sans Symfony CLI :
   ```bash
   php -S localhost:8000 -t public/
   ```

## 📁 Structure du Projet

```
src/
├── Controller/       # Contrôleurs (Dashboard, Profile, Team, Tournament, Shop, Admin)
├── Entity/           # Entités Doctrine (User, Profile, Team, Tournament, Match...)
├── Enum/             # Enums PHP 8.1 (AccountStatus, TeamRole, TeamStatus...)
├── Form/             # Types de formulaires Symfony
├── Repository/       # Repositories Doctrine
├── Security/         # Authentification (LoginFormAuthenticator, UserChecker)
├── Service/          # Services métier (AuthService, MatchGeneratorService)
└── Validator/        # Contraintes de validation personnalisées

templates/            # Templates Twig
docs/                 # Documentation technique détaillée
migrations/           # Migrations Doctrine
public/uploads/       # Fichiers uploadés (avatars)
```

## 🎮 Fonctionnalités

### Joueurs
- Création de compte et authentification
- Gestion du profil (bio, avatar)
- Création et gestion d'équipes (max 8 membres)
- Système d'invitation par code
- Participation aux tournois
- Boutique avec monnaie virtuelle (CP)

### Arbitres
- Inscription avec numéro de licence
- Officiation des matchs

### Administrateurs
- Tableau de bord d'administration (`/admin`)
- Gestion des utilisateurs, tournois, boutique et jeux
- Traitement des réclamations

## 🔐 Rôles Utilisateur

| Rôle | Description |
|------|-------------|
| `ROLE_USER` | Rôle par défaut, ajouté automatiquement |
| `ROLE_REFEREE` | Arbitre (requiert un numéro de licence) |
| `ROLE_ADMIN` | Administrateur avec accès au back-office |

## 🧪 Tests

```bash
# Exécuter tous les tests
php bin/phpunit

# Exécuter un fichier de test spécifique
php bin/phpunit tests/Chemin/Vers/TestFile.php
```

## 📝 Commandes Utiles

```bash
# Vider le cache
php bin/console cache:clear

# Générer une migration après modification des entités
php bin/console doctrine:migrations:diff

# Peupler la base avec des données de test
php bin/console app:seed-data
php bin/console app:seed-tournaments

# Promouvoir un utilisateur en admin
php bin/console app:promote-admin
```

## 📚 Documentation

La documentation technique détaillée se trouve dans le dossier `docs/` :

- **AUTH_ET_COMPTES.md** — Authentification, tokens, gestion des sessions
- **PROFILS_ET_EQUIPES.md** — Système de profils et d'équipes, rôles, succession du capitaine

## 🌐 Routes Principales

| Route | Chemin | Description |
|-------|--------|-------------|
| Connexion | `/connexion` | Page de connexion |
| Inscription | `/inscription` | Création de compte |
| Tableau de bord | `/` | Page d'accueil authentifiée |
| Profil | `/profil` | Profil utilisateur |
| Paramètres | `/parametres` | Paramètres du compte |
| Équipe | `/equipe` | Gestion d'équipe |
| Tournois | `/tournois` | Liste des tournois |
| Boutique | `/boutique` | Boutique en ligne |
| Admin | `/admin` | Panneau d'administration |

## 📄 Licence

Projet académique — Tous droits réservés.
