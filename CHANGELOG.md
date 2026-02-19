# Changelog - Carthage Arena

Ce document liste les changements récents apportés au projet Carthage Arena.

---

## [Non publié] - 2026-02-13

### 📚 Documentation

#### Ajout de documentation complète du projet (commit 9c1b035)
- **Nouveau fichier**: `docs/OVERVIEW.md` - Vue d'ensemble complète de l'architecture du projet en français
  - Documentation détaillée des entités et de leurs relations
  - Description du système d'authentification (AuthToken, UserChecker)
  - Explication du système de profils et d'équipes
  - Documentation du système de tournois et de matchs
  - Système de boutique (produits, skins, marchandises)
  - Système de réclamations et de support
  - Routes et contrôleurs documentés
  - Structure des dossiers expliquée

- **Mise à jour**: `.gitignore` - Exclusion du dossier `.rovodev/`

---

## [Non publié] - 2026-02-12

### ✨ Fonctionnalités

#### Amélioration du panneau d'administration (commit 14139e7)

**Nouvelles fonctionnalités administrateur**:

1. **Formulaire d'ajout d'utilisateur** (`src/Form/AdminNewUserType.php`)
   - Création d'utilisateurs directement depuis l'interface admin
   - Champs: username, email, rôles, statut, solde (CP), numéro de licence
   - Validation conditionnelle: licence obligatoire pour les arbitres
   - Sélection multiple de rôles (USER, PRO, REFEREE, ADMIN)
   - Attribution de solde initial en Carthage Points

2. **Pages d'administration pour les utilisateurs**:
   - `templates/admin/users/add.html.twig` - Formulaire d'ajout d'utilisateur
   - `templates/admin/users/assign_license.html.twig` - Attribution de licence
   - Amélioration de `templates/admin/users/index.html.twig`

3. **Améliorations des templates admin**:
   - Refinements visuels sur toutes les pages d'administration
   - Amélioration de la cohérence UI/UX
   - Templates mis à jour:
     - `templates/admin/games/index.html.twig`
     - `templates/admin/licenses.html.twig`
     - `templates/admin/reclamation/index.html.twig`
     - `templates/admin/reports/index.html.twig`
     - `templates/admin/settings/index.html.twig`
     - `templates/admin/shop/index.html.twig`
     - `templates/admin/tournaments/index.html.twig`

4. **Améliorations du contrôleur admin**:
   - Extension de `src/Controller/Admin/AdminDashboardController.php`
   - Nouvelles routes et actions pour la gestion des utilisateurs

#### Documentation et nettoyage du projet (commit c2ed671)

**Documentation**:
- **Mise à jour majeure**: `README.md` 
  - Description complète du projet
  - Stack technique détaillée
  - Instructions d'installation pas à pas
  - Structure du projet documentée
  - Liste complète des fonctionnalités
  - Documentation des rôles utilisateur
  - Commandes utiles et routes principales
  - Références vers la documentation technique

**Nettoyage**:
- Suppression des fichiers temporaires et utilitaires:
  - `check_tables.php`
  - `check_user_table.php`
  - `routes.txt`
  - `routes_ascii.txt`
  - `seed_log.txt`
- Mise à jour de `.gitignore` pour une meilleure gestion des fichiers

#### Recherche et filtrage des utilisateurs (commit ab55f5b)

**Gestion des utilisateurs**:
1. **Nouvelle méthode de repository** (`src/Repository/UserRepository.php`):
   - `searchAndFilter()` - Recherche et filtrage avancés
   - Filtres disponibles:
     - Recherche par nom d'utilisateur ou email
     - Filtrage par statut de compte (avec validation enum)
     - Filtrage par rôle
   - Tri par date de création (DESC)

2. **Interface utilisateur**:
   - Formulaire de recherche dans le panneau admin
   - Filtres par statut et rôle
   - Mise à jour de `templates/admin/users/index.html.twig`

**Améliorations UI**:
- **Page des paramètres** (`templates/settings/index.html.twig`):
  - Amélioration de l'expérience d'upload d'avatar
  - Déclenchement via label au lieu de bouton
  - Prévisualisation d'image améliorée
  
- **Validation**:
  - Messages de validation explicites en français
  - Mise à jour de `src/Entity/User.php`

#### Implémentation du tableau de bord admin complet (commit c2bc752)

**Gestion des utilisateurs**:
1. **Formulaire d'édition** (`src/Form/AdminUserType.php`):
   - Modification des informations utilisateur
   - Champs: username, email, rôles, statut, solde
   - Interface avec checkboxes pour sélection de rôles multiples

2. **Template d'édition** (`templates/admin/users/edit.html.twig`):
   - Page dédiée à la modification des utilisateurs
   - Intégration du formulaire AdminUserType

3. **Contrôleur enrichi**:
   - Nouvelles actions dans `AdminDashboardController`
   - Routes pour édition et gestion des utilisateurs

4. **Amélioration de l'index utilisateurs**:
   - Interface de liste améliorée
   - Actions d'édition et de suppression

---

## 🏗️ Architecture et Patterns

### Modèle de Domaine Principal

```
User (UserInterface)
 ├── OneToOne → Profile (bio, avatar - lazy loaded)
 ├── OneToOne → AuthToken (authentification à session unique)
 └── OneToMany → TeamMembership[] (entité d'association avec rôle)

Team
 ├── ManyToOne → captain (User)
 └── OneToMany → TeamMembership[]

Tournament (Tournoi) → MatchEntity[] (système de bracket)

Shop: Product, Skin, Merch entities

Reclamation system
```

### Patterns Clés

1. **Entité d'association**: `TeamMembership` lie User↔Team avec métadonnées (rôle, joinedAt)
2. **Profile lazy-loaded**: Séparé de User pour éviter le chargement bio/avatar à chaque requête
3. **Authentification à session unique**: Un AuthToken par user; nouvelle connexion invalide la précédente
4. **Soft-delete via enum**: `AccountStatus::DELETED` bloque la connexion mais préserve les données

### Rôles Utilisateur
- `ROLE_USER` — Ajouté automatiquement à tous les utilisateurs
- `ROLE_PRO` — Joueur professionnel
- `ROLE_REFEREE` — Arbitre (requiert `licenseId`)
- `ROLE_ADMIN` — Accès au panneau `/admin`

---

## 📊 Statistiques des Changements

**Période**: 2026-02-12 à 2026-02-13 (derniers 5 commits)

### Fichiers modifiés
- **28 fichiers** changés au total
- **+1,377 insertions** / **-238 suppressions**

### Principaux ajouts
- 2 nouveaux fichiers de formulaire (AdminNewUserType, AdminUserType)
- 4 nouveaux templates admin (add, edit, assign_license)
- 1 fichier de documentation majeur (OVERVIEW.md)
- Enrichissement du README (+150 lignes)

### Domaines impactés
- **Backend**: Contrôleurs, formulaires, repositories
- **Frontend**: Templates Twig pour l'administration
- **Documentation**: README, OVERVIEW, .gitignore
- **Nettoyage**: Suppression de 5 fichiers temporaires

---

## 🔄 Migrations de Base de Données

Aucune nouvelle migration dans cette période. Les changements concernent principalement:
- La logique applicative
- Les formulaires et l'interface utilisateur
- La documentation

---

## 🎯 Prochaines Étapes Recommandées

Basé sur les changements récents, voici les améliorations suggérées:

1. **Tests unitaires**:
   - Tests pour `UserRepository::searchAndFilter()`
   - Tests des formulaires AdminNewUserType et AdminUserType
   - Tests des routes admin

2. **Validation**:
   - Tests de validation conditionnelle pour les licences d'arbitres
   - Validation des rôles multiples

3. **Documentation**:
   - Guide administrateur pour l'utilisation du panneau admin
   - Documentation API pour les futurs développeurs

4. **Améliorations UI/UX**:
   - Pagination pour la liste des utilisateurs
   - Export CSV des utilisateurs
   - Logs d'audit pour les actions admin

---

## 👥 Contributeurs

- **JELASSI Aymen** - Développement principal
- **Cursor** - Assistant IA (co-auteur)
- **Warp** - Assistant IA (co-auteur)

---

## 📝 Notes Techniques

### Conventions de Codage Respectées

- **Langue**: UI/routes/messages en français, code en anglais
- **Routes**: Chemins français (`/connexion`, `/inscription`, `/equipe`, `/boutique`, `/parametres`)
- **IDs**: UUID (BINARY 16) sur toutes les entités
- **Enums**: PHP 8.1 backed enums stockés en VARCHAR
- **Formulaires**: Attribut `novalidate`, validation serveur uniquement
- **Templates**: Classes utilitaires Tailwind directement dans Twig
- **Flash messages**: Types `success`, `error`, `warning`
- **Font**: Be Vietnam Pro
- **Icons**: Material Symbols Outlined

### Stack Technique

- **PHP**: 8.1+
- **Symfony**: 6.4 (LTS)
- **MySQL**: Base de données
- **Doctrine ORM**: 3.6+
- **Frontend**: Twig + Tailwind CSS (CDN) + Stimulus/Turbo

---

*Dernière mise à jour: 2026-02-18*
