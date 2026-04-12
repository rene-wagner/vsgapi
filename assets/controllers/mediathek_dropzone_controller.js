import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'dropzone', 'list'];
    static values = { maxFiles: { type: Number, default: 20 } };

    connect() {
        this.dragCounter = 0;
        this.updateList();
    }

    openPicker() {
        this.inputTarget.click();
    }

    updateList() {
        const listEl = this.listTarget;
        const input = this.inputTarget;
        const n = input.files.length;
        if (n === 0) {
            listEl.hidden = true;
            listEl.textContent = '';
            return;
        }
        const names = Array.from(input.files, (f) => f.name).join(', ');
        listEl.hidden = false;
        listEl.textContent =
            n === 1 ? `1 Datei ausgewählt: ${names}` : `${n} Dateien ausgewählt: ${names}`;
    }

    dragEnter(e) {
        e.preventDefault();
        e.stopPropagation();
        this.dragCounter += 1;
        this.dropzoneTarget.classList.add('is-dragover');
    }

    dragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        this.dragCounter -= 1;
        if (this.dragCounter <= 0) {
            this.dragCounter = 0;
            this.dropzoneTarget.classList.remove('is-dragover');
        }
    }

    dragOver(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    drop(e) {
        e.preventDefault();
        e.stopPropagation();
        this.dragCounter = 0;
        this.dropzoneTarget.classList.remove('is-dragover');
        const dropped = e.dataTransfer?.files;
        if (!dropped || dropped.length === 0) {
            return;
        }
        const merged = this.#mergeFiles(this.inputTarget.files, dropped);
        this.#assignFiles(this.inputTarget, merged);
        this.updateList();
    }

    #mergeFiles(existing, incoming) {
        const out = [];
        for (let i = 0; i < existing.length; i += 1) {
            out.push(existing[i]);
        }
        for (let i = 0; i < incoming.length; i += 1) {
            out.push(incoming[i]);
        }
        return out;
    }

    #assignFiles(input, files) {
        const capped = files.slice(0, this.maxFilesValue);
        const dt = new DataTransfer();
        for (const f of capped) {
            dt.items.add(f);
        }
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}