import { Controller } from '@hotwired/stimulus';
import EasyMDE from 'easymde';
import { parse as markedParse } from 'marked';
import 'easymde/dist/easymde.min.css';

/* stimulusFetch: 'lazy' */

export default class extends Controller {
    static targets = ['button', 'error', 'input'];
    static values = {
        csrfToken: String,
        improveUrl: String,
    };

    connect() {
        this.editor = new EasyMDE({
            element: this.inputTarget,
            spellChecker: false,
            autoDownloadFontAwesome: false,
            previewRender: (plainText) => markedParse(plainText, { breaks: true }),
        });
    }

    disconnect() {
        if (this.editor) {
            this.editor.toTextArea();
            this.editor = null;
        }
    }

    async improve() {
        const content = this.editor.value().trim();

        this.#hideError();

        if (content === '') {
            this.#showError('Bitte geben Sie zuerst einen Text ein.');
            return;
        }

        this.buttonTarget.disabled = true;

        try {
            const response = await fetch(this.improveUrlValue, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    _token: this.csrfTokenValue,
                    content,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Der Text konnte nicht ausformuliert werden.');
            }

            this.editor.value(data.content || '');
        } catch (error) {
            this.#showError(error.message || 'Der Text konnte nicht ausformuliert werden.');
        } finally {
            this.buttonTarget.disabled = false;
        }
    }

    #showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.classList.remove('hidden');
    }

    #hideError() {
        this.errorTarget.textContent = '';
        this.errorTarget.classList.add('hidden');
    }
}
