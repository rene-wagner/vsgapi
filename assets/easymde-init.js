import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';

document.querySelectorAll('form textarea').forEach((element) => {
    if (!(element instanceof HTMLTextAreaElement)) {
        return;
    }
    if (element.disabled || element.readOnly) {
        return;
    }
    if (element.getAttribute('data-easymde') === '0') {
        return;
    }
    if (element.closest('.EasyMDEContainer')) {
        return;
    }

    new EasyMDE({
        element,
        spellChecker: false,
        autoDownloadFontAwesome: false,
    });
});
