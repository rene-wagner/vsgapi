import { Controller } from '@hotwired/stimulus';
import EasyMDE from 'easymde';
import { parse as markedParse } from 'marked';
import 'easymde/dist/easymde.min.css';

/* stimulusFetch: 'lazy' */

export default class extends Controller {
    connect() {
        this.editor = new EasyMDE({
            element: this.element,
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
}