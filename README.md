# 🏟️ Carthage Arena – Esports Tournament Management Platform

> Developed at **Esprit School of Engineering – Tunisia**
> PIDEV – 3rd Year Engineering Program | Academic Year 2025–2026

---

## Overview

**Carthage Arena** is a full-stack web application built as part of the **PIDEV** project at **Esprit School of Engineering** (Academic Year 2025–2026).

It enables players to register, form teams, compete in tournaments, and spend virtual currency (Carthage Points – CP) in an in-app shop. Referees officiate matches, and administrators manage the entire platform.

---

## Features

### Players
- Account creation and secure authentication (email + password, UUID-based identity)
- Profile management (bio, avatar upload)
- Team creation and management (up to 8 members, captain succession)
- Invitation system via unique team codes
- Tournament registration and bracket participation
- Virtual currency shop (Carthage Points)

### Referees
- Registration with a license number
- Match officiation and result recording

### Administrators
- Full back-office dashboard (`/admin`)
- User, tournament, shop, and game management
- Reclamation and dispute handling

### Tournaments
- Single-elimination bracket generation
- Automatic bye handling for non-power-of-two team counts
- Geolocation-enabled tournament details (interactive map)
- Real-time match status tracking

---

## Tech Stack

### Frontend
- **Twig** – server-side templating engine
- **Tailwind CSS** (CDN) – utility-first styling
- **Stimulus / Turbo** (Hotwired / Symfony UX) – SPA-like navigation without a JS framework

### Backend
- **PHP 8.1+**
- **Symfony 6.4 LTS** – main framework
- **Doctrine ORM 3.6+** – database abstraction
- **API Platform** – REST API layer for tournament and match endpoints
- **StofDoctrineExtensionsBundle** – Timestampable behavior
- **Symfony Mailer** – email notifications
- **MySQL** – relational database
- **UUID (BINARY 16)** – primary keys on all entities

---

## Architecture

```
src/
├── Controller/         # HTTP controllers (Dashboard, Profile, Team, Tournament, Shop, Admin, Reclamation)
├── Entity/             # Doctrine entities (User, Team, Tournoi, MatchEntity, Reclamation, Skin …)
├── Enum/               # PHP 8.1 enums (AccountStatus, TeamRole, TeamStatus, TournamentStatus …)
├── Form/               # Symfony form types
├── Repository/         # Doctrine repository classes
├── Security/           # LoginFormAuthenticator, UserChecker
├── Service/            # Business logic (MatchGeneratorService, AuthService …)
└── Validator/          # Custom validation constraints

templates/              # Twig templates (69 files)
docs/                   # Technical documentation (AUTH, PROFILES, TEAMS)
migrations/             # Doctrine database migrations
public/uploads/         # User-uploaded files (avatars)
tests/                  # PHPUnit unit & integration tests
```

---

## Contributors

| Name | Role | GitHub |
|------|------|--------|
| [Team Member Name] | Tournament & Match Module | [@github-username](https://github.com/github-username) |
| [Team Member Name] | User & Profile Module | [@github-username](https://github.com/github-username) |
| [Team Member Name] | Shop & Skin Module | [@github-username](https://github.com/github-username) |
| [Team Member Name] | Reclamation Module | [@github-username](https://github.com/github-username) |

---

## Academic Context

This project was developed as part of the **PIDEV – 3rd Year Engineering Program** at **Esprit School of Engineering – Tunisia** (Academic Year **2025–2026**).

| Field | Value |
|-------|-------|
| Institution | **Esprit School of Engineering** |
| Program | PIDEV – Projet Intégré de DEVeloppement |
| Class | 3A |
| Academic Year | 2025–2026 |
| Project Type | Full-stack Web Application |

---

## Getting Started

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL
- Symfony CLI (optional, recommended)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/<your-username>/Esprit-PIDEV-3A-2026-CarthageArena.git
   cd Esprit-PIDEV-3A-2026-CarthageArena
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env .env.local
   ```
   Edit `.env.local` with your database credentials:
   ```
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/carthage_arena"
   ```

4. **Create the database and run migrations**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Seed test data (optional)**
   ```bash
   php bin/console app:seed-data
   php bin/console app:seed-tournaments
   ```

6. **Start the development server**
   ```bash
   symfony server:start
   ```
   Or without Symfony CLI:
   ```bash
   php -S localhost:8000 -t public/
   ```

### Useful Commands

```bash
# Clear the cache
php bin/console cache:clear

# Generate a migration after entity changes
php bin/console doctrine:migrations:diff

# Promote a user to admin
php bin/console app:promote-admin

# Run all tests
php bin/phpunit
```

---

## Acknowledgments

- [Esprit School of Engineering](https://esprit.tn) – academic framework and supervision
- [Symfony](https://symfony.com) – PHP framework
- [API Platform](https://api-platform.com) – REST API layer
- [Hotwired (Stimulus / Turbo)](https://hotwired.dev) – modern frontend reactivity
- [Tailwind CSS](https://tailwindcss.com) – utility-first CSS framework
- [GitHub Education](https://education.github.com) – student developer tools

---

*Carthage Arena – Academic Year 2025–2026 | Esprit School of Engineering – Tunisia*
