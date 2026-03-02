# 🛡️ Carthage Arena - Guide Technique du Codebase

Ce document récapitule les fonctionnalités ajoutées, les services utilisés et l'emplacement des fichiers clés.

---

## 1. Intégrations Intelligence Artificielle (AI)

### 🤖 Assistant Grok (xAI)
*   **Fonction de chat flottant** en bas à droite pour l'aide aux utilisateurs.
*   **Emplacements :**
    *   `src/Service/GrokAiService.php` : Logique de communication avec l'API xAI (Grok-2).
    *   `src/Controller/AiChatController.php` : Endpoint interne `/ai/chat`.
    *   `assets/controllers/ai_chat_controller.js` : Contrôleur Stimulus pour l'interface de chat (Glassmorphism).
    *   `templates/base.html.twig` : Injection HTML globale du bouton et de la fenêtre.

### 🎙️ Voice-to-Text (Microphone)
*   **Transcription en temps réel** dans le formulaire de réclamation.
*   **Emplacements :**
    *   `assets/controllers/voice_recorder_controller.js` : Utilise l'API native `SpeechRecognition` du navigateur.
    *   `templates/reclamation/new.html.twig` : Intégration du bouton "Open Mic" près du champ message.

### 📊 Analyse de Réclamations (NVIDIA AI)
*   **Génération d'insights** et de résumés pour les administrateurs.
*   **Emplacements :**
    *   `src/Service/ReclamationAiService.php` : Utilise NVIDIA NIM (LLM) pour analyser le texte.
    *   `src/Controller/Admin/ReclamationController.php` : Appelle le service pour afficher les conseils IA dans l'admin.

---

## 2. Tournois et Matchs (API & Logique)

### 🛠️ API REST (API Platform)
*   Exposition des entités en tant que **ressources API** accessibles via `/api`.
*   **Emplacements :**
    *   `src/Entity/Tournoi.php` : Marquée avec `#[ApiResource]`.
    *   `src/Entity/MatchEntity.php` : Marquée avec `#[ApiResource]`.

### ⚡ Générateur de Matchs
*   Logique complexe pour créer l'arbre de tournois.
*   **Emplacements :**
    *   `src/Service/MatchGeneratorService.php` : Service central pour la génération automatique des rencontres.

---

## 3. Configuration et Environnement

### 🔑 Clés API et Variables
*   **File :** `.env`
    *   `NVIDIA_API_KEY` : Pour les insights et le STT (legacy).
    *   `XAI_API_KEY` : Pour le chatbot Grok.
*   **File :** `config/services.yaml`
    *   Configuration des injections de dépendances (Binding des clés API aux services PHP).

---

## 🎨 Design System
*   L'application utilise **Tailwind CSS** pour le design.
*   **Glassmorphism** : Appliqué sur le Chatbot AI (`backdrop-blur-2xl`, `bg-white/10`).
*   **Dark Mode** : Activé par défaut pour toute la plateforme.
