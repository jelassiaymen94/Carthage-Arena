import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["window", "input", "messages", "trigger"];
    static values = { url: String };

    connect() {
        this.history = [];
        this.isOpen = false;
    }

    toggle() {
        this.isOpen = !this.isOpen;
        this.windowTarget.classList.toggle('hidden', !this.isOpen);

        if (this.isOpen) {
            this.inputTarget.focus();
            this.windowTarget.classList.add('animate-in', 'fade-in', 'slide-in-from-bottom-4');
        }
    }

    async send(event) {
        event.preventDefault();
        const message = this.inputTarget.value.trim();
        if (!message) return;

        // UI Update: User message
        this.appendMessage('user', message);
        this.inputTarget.value = '';

        // Add loading state
        this.appendMessage('bot', '...', true);

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message, history: this.history })
            });

            const data = await response.json();

            // Remove loading
            this.removeLoading();

            if (data.reply) {
                this.appendMessage('bot', data.reply);
                this.history.push({ role: 'user', content: message });
                this.history.push({ role: 'assistant', content: data.reply });
            }
        } catch (err) {
            this.removeLoading();
            this.appendMessage('bot', "Désolé, je rencontre un problème technique.");
        }
    }

    appendMessage(role, text, isLoading = false) {
        const div = document.createElement('div');
        div.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'} mb-4 animate-in fade-in slide-in-from-${role === 'user' ? 'right' : 'left'}-2`;

        const inner = document.createElement('div');
        inner.className = `max-w-[80%] rounded-2xl px-4 py-2 ${role === 'user' ? 'bg-primary text-white rounded-tr-none' : 'bg-white/10 text-gray-200 backdrop-blur-md border border-white/10 rounded-tl-none'}`;

        if (isLoading) {
            inner.id = 'ai-loading';
            inner.innerHTML = '<span class="flex gap-1"><span class="w-1.5 h-1.5 bg-white/50 rounded-full animate-bounce"></span><span class="w-1.5 h-1.5 bg-white/50 rounded-full animate-bounce [animation-delay:0.2s]"></span><span class="w-1.5 h-1.5 bg-white/50 rounded-full animate-bounce [animation-delay:0.4s]"></span></span>';
        } else {
            inner.textContent = text;
        }

        div.appendChild(inner);
        this.messagesTarget.appendChild(div);
        this.messagesTarget.scrollTop = this.messagesTarget.scrollHeight;
    }

    removeLoading() {
        const loading = document.getElementById('ai-loading');
        if (loading) loading.parentElement.remove();
    }
}
