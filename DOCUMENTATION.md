# 🛡️ Carthage Arena - Documentation Technique & Architecture

Bienvenue dans la documentation officielle du projet **Carthage Arena**. Ce document récapitule les fonctionnalités avancées, les intégrations IA et l'architecture technique de la plateforme.

---

## 1. 🤖 Intelligence Artificielle (AI)

### Carthage AI (Grok-2/xAI)
*   **Description** : Un assistant de chat flottant (bas-droite) capable d'aider les utilisateurs sur les tournois, le gaming et le support technique.
*   **Composants** :
    *   `src/Service/GrokAiService.php` : Client API pour xAI avec prompt système restreint au thème gaming.
    *   `src/Controller/AiChatController.php` : Endpoint `/ai/chat` pour traiter les requêtes AJAX.
    *   `assets/controllers/ai_chat_controller.js` : Contrôleur Stimulus gérant l'interface (Glassmorphism) et les messages.
    *   `templates/base.html.twig` : Intégration globale du bouton et de la fenêtre de chat.

### Voice-to-Text (Recommandation vocale)
*   **Description** : Permet aux utilisateurs de dicter leurs réclamations au lieu de les taper.
*   **Technologie** : Utilise l'API native `Web Speech API` pour une transcription temps réel sans latence serveur.
*   **Composants** :
    *   `assets/controllers/voice_recorder_controller.js` : Gère le microphone et l'affichage du texte en direct.
    *   `templates/reclamation/new.html.twig` : Bouton "Open Mic" intégré au formulaire.

### AI Insights (NVIDIA NIM)
*   **Description** : Analyse automatique des réclamations pour aider l'administration (résumés, ton, urgence).
*   **Composants** :
    *   `src/Service/ReclamationAiService.php` : Communique avec les modèles LLM de NVIDIA.

---

## 2. 🏆 Tournois & Matchs

### API Platform & Doctrine
*   **API REST** : Les tournois et matchs sont exposés via des endpoints standards (`/api/tournois`, `/api/matches`).
*   **Auditing** : Utilisation de `StofDoctrineExtensionsBundle` pour le tracking automatique des dates (`createdAt`, `updatedAt`).

### Génération Automatique
*   **Service** : `MatchGeneratorService.php`
*   **Fonctionnement** : Génère l'arbre de tournoi (Elimination simple ou Round Robin) en fonction des équipes inscrites.

### Rappels par Email
*   **Automatisation** : Utilise le **Scheduler de Symfony**.
*   **Fichiers** :
    *   `src/Scheduler/MatchReminderSchedule.php` : Planifie l'envoi tous les matins à 08h00.
    *   `src/Command/SendMatchRemindersCommand.php` : Commande console pour l'envoi manuel ou via cron.
    *   `templates/emails/match_reminder.html.twig` : Template HTML premium pour les emails.

---

## 🔑 Configuration (.env)
*   `XAI_API_KEY` : Clé pour le chatbot Grok.
*   `NVIDIA_API_KEY` : Clé pour les services NVIDIA.
*   `MAILER_DSN` : Configuration du serveur SMTP pour les rappels de matchs.

---

## 🎨 Design & UI
*   **Framework** : Tailwind CSS.
*   **Esthétique** : Dark mode natif, Glassmorphism sur les composants IA, animations via Tailwind-Animate et Stimulus.
