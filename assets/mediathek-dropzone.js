const maxFiles = 20;

/**
 * @param {FileList} existing
 * @param {DataTransferItemList|FileList} incoming
 * @returns {File[]}
 */
function mergeFiles(existing, incoming) {
    const out = [];
    for (let i = 0; i < existing.length; i += 1) {
        out.push(existing[i]);
    }
    for (let i = 0; i < incoming.length; i += 1) {
        out.push(incoming[i]);
    }
    return out;
}

/**
 * @param {HTMLInputElement} input
 * @param {File[]} files
 */
function assignFiles(input, files) {
    const capped = files.slice(0, maxFiles);
    const dt = new DataTransfer();
    for (const f of capped) {
        dt.items.add(f);
    }
    input.files = dt.files;
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

function initMediathekDropzone(root) {
    const input = root.querySelector('input.admin-mediathek-file-input');
    const target = root.querySelector('[data-mediathek-dropzone-target]');
    const listEl = root.querySelector('[data-mediathek-selected-list]');
    if (!(input instanceof HTMLInputElement) || !(target instanceof HTMLElement)) {
        return;
    }

    function updateList() {
        if (!(listEl instanceof HTMLElement)) {
            return;
        }
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

    target.addEventListener('click', () => {
        input.click();
    });

    input.addEventListener('change', updateList);

    let dragCounter = 0;
    target.addEventListener('dragenter', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dragCounter += 1;
        target.classList.add('is-dragover');
    });
    target.addEventListener('dragleave', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dragCounter -= 1;
        if (dragCounter <= 0) {
            dragCounter = 0;
            target.classList.remove('is-dragover');
        }
    });
    target.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
    });
    target.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dragCounter = 0;
        target.classList.remove('is-dragover');
        const dropped = e.dataTransfer?.files;
        if (!dropped || dropped.length === 0) {
            return;
        }
        const merged = mergeFiles(input.files, dropped);
        assignFiles(input, merged);
        updateList();
    });
}

document.querySelectorAll('[data-mediathek-dropzone]').forEach((root) => {
    if (root instanceof HTMLElement) {
        initMediathekDropzone(root);
    }
});
