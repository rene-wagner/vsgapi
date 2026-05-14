import { Controller } from '@hotwired/stimulus';
import { Modal } from 'flowbite';
import EasyMDE from 'easymde';
import { parse as markedParse } from 'marked';
import 'easymde/dist/easymde.min.css';

/* stimulusFetch: 'lazy' */

export default class extends Controller {
    static targets = [
        'button',
        'error',
        'input',
        'modal',
        'modalBody',
        'modalError',
        'modalLoading',
        'modelLink',
        'modelName',
        'previewInput',
        'prompt',
        'applyButton',
    ];
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
        this.previewEditor = null;
        this.modalInstance = new Modal(this.modalTarget, {
            onHide: () => {
                this.#hideModalLoading();
                this.#hideModalError();
            },
        });
    }

    disconnect() {
        if (this.editor) {
            this.editor.toTextArea();
            this.editor = null;
        }

        if (this.previewEditor) {
            this.previewEditor.toTextArea();
            this.previewEditor = null;
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
        this.modalInstance.show();
        this.#showModalLoading();
        this.#hideModalError();
        this.#hideModalBody();
        this.applyButtonTarget.disabled = true;

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

            this.#ensurePreviewEditor();
            this.previewEditor.value(data.content || '');
            this.promptTarget.textContent = data.systemPrompt || '';
            this.modelNameTarget.textContent = data.model || '';
            this.modelLinkTarget.href = data.modelUrl || '#';
            this.#hideModalLoading();
            this.#showModalBody();
            this.applyButtonTarget.disabled = false;
            this.previewEditor.codemirror.refresh();
        } catch (error) {
            this.#hideModalLoading();
            this.#showModalError(error.message || 'Der Text konnte nicht ausformuliert werden.');
        } finally {
            this.buttonTarget.disabled = false;
        }
    }

    apply() {
        if (!this.previewEditor) {
            return;
        }

        this.editor.value(this.previewEditor.value());
        this.modalInstance.hide();
    }

    closeModal() {
        this.modalInstance.hide();
    }

    #ensurePreviewEditor() {
        if (this.previewEditor) {
            return;
        }

        this.previewEditor = new EasyMDE({
            element: this.previewInputTarget,
            spellChecker: false,
            autoDownloadFontAwesome: false,
            previewRender: (plainText) => markedParse(plainText, { breaks: true }),
        });
    }

    #showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.classList.remove('hidden');
    }

    #hideError() {
        this.errorTarget.textContent = '';
        this.errorTarget.classList.add('hidden');
    }

    #showModalLoading() {
        this.modalLoadingTarget.classList.remove('hidden');
    }

    #hideModalLoading() {
        this.modalLoadingTarget.classList.add('hidden');
    }

    #showModalBody() {
        this.modalBodyTarget.classList.remove('hidden');
    }

    #hideModalBody() {
        this.modalBodyTarget.classList.add('hidden');
    }

    #showModalError(message) {
        this.modalErrorTarget.textContent = message;
        this.modalErrorTarget.classList.remove('hidden');
    }

    #hideModalError() {
        this.modalErrorTarget.textContent = '';
        this.modalErrorTarget.classList.add('hidden');
    }
}
