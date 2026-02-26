import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["input", "button", "status"];

    connect() {
        this.isRecording = false;
        this.recognition = null;
        this.setupRecognition();
    }

    setupRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            this.buttonTarget.style.display = 'none';
            console.warn("Speech Recognition not supported in this browser.");
            return;
        }

        this.recognition = new SpeechRecognition();
        this.recognition.continuous = true;
        this.recognition.interimResults = true;
        this.recognition.lang = 'fr-FR';

        this.recognition.onresult = (event) => {
            let finalTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                }
            }

            if (finalTranscript) {
                const currentText = this.inputTarget.value;
                const separator = currentText.length > 0 ? (currentText.endsWith(' ') ? '' : ' ') : '';
                this.inputTarget.value = currentText + separator + finalTranscript;
                this.inputTarget.dispatchEvent(new Event('input'));
            }
        };

        this.recognition.onerror = (event) => {
            console.error("Speech Recognition Error:", event.error);
            this.stop();
            if (event.error === 'not-allowed') {
                alert("Accès au microphone refusé. Veuillez l'autoriser dans les réglages de votre navigateur.");
            }
        };

        this.recognition.onend = () => {
            if (this.isRecording) {
                try {
                    this.recognition.start();
                } catch (e) {
                    this.isRecording = false;
                    this.updateUI(false);
                }
            }
        };
    }

    toggle() {
        if (this.isRecording) {
            this.stop();
        } else {
            this.start();
        }
    }

    start() {
        if (!this.recognition) return;

        try {
            this.recognition.start();
            this.isRecording = true;
            this.updateUI(true);
        } catch (err) {
            console.error("Recognition start error:", err);
            this.isRecording = false;
        }
    }

    stop() {
        if (this.recognition && this.isRecording) {
            this.isRecording = false;
            this.recognition.stop();
            this.updateUI(false);
        }
    }

    updateUI(recording) {
        const micIcon = this.buttonTarget.querySelector('.material-symbols-outlined');
        if (recording) {
            this.buttonTarget.classList.add('bg-red-500', 'animate-pulse', 'border-red-500/50');
            this.buttonTarget.classList.remove('bg-white/5', 'text-gray-400');
            this.buttonTarget.classList.add('text-white');
            if (micIcon) micIcon.textContent = 'record_voice_over';
            this.statusTarget.textContent = "Je vous écoute...";
        } else {
            this.buttonTarget.classList.remove('bg-red-500', 'animate-pulse', 'border-red-500/50', 'text-white');
            this.buttonTarget.classList.add('bg-white/5', 'text-gray-400');
            if (micIcon) micIcon.textContent = 'mic';
            this.statusTarget.textContent = "";
        }
    }
}
