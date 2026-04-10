import { Modal } from 'bootstrap';

const MODAL_ID = 'mediaSelectorModal';
const FOLDERS_URL = '/api/media_folders';
const ITEMS_URL = '/api/media_items';

let modalInstance = null;
let activeWrapper = null;

function getModal() {
    if (!modalInstance) {
        const el = document.getElementById(MODAL_ID);
        if (!el) return null;
        modalInstance = Modal.getOrCreateInstance(el);
    }
    return modalInstance;
}

function getModalEl() {
    return document.getElementById(MODAL_ID);
}

async function fetchJson(url) {
    const res = await fetch(url, {
        headers: { Accept: 'application/ld+json' },
        credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

async function fetchRootFolders() {
    return fetchJson(FOLDERS_URL + '?exists[parent]=false');
}

async function fetchSubFolders(folderId) {
    return fetchJson(FOLDERS_URL + '?parent=' + FOLDERS_URL + '/' + folderId);
}

async function fetchRootItems() {
    return fetchJson(ITEMS_URL + '?exists[folder]=false');
}

async function fetchFolderItems(folderId) {
    return fetchJson(ITEMS_URL + '?folder=' + FOLDERS_URL + '/' + folderId);
}

function renderBreadcrumb(path) {
    const ol = getModalEl().querySelector('[data-media-selector-breadcrumb]');
    ol.innerHTML = '';

    const rootLi = document.createElement('li');
    rootLi.className = 'breadcrumb-item';
    if (path.length === 0) {
        rootLi.classList.add('active');
        rootLi.setAttribute('aria-current', 'page');
        rootLi.textContent = 'Mediathek';
    } else {
        const a = document.createElement('a');
        a.href = '#';
        a.textContent = 'Mediathek';
        a.addEventListener('click', function (e) {
            e.preventDefault();
            loadFolder(null, []);
        });
        rootLi.appendChild(a);
    }
    ol.appendChild(rootLi);

    path.forEach(function (crumb, idx) {
        const li = document.createElement('li');
        li.className = 'breadcrumb-item';
        if (idx === path.length - 1) {
            li.classList.add('active');
            li.setAttribute('aria-current', 'page');
            li.textContent = crumb.name;
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.textContent = crumb.name;
            const subPath = path.slice(0, idx + 1);
            a.addEventListener('click', function (e) {
                e.preventDefault();
                loadFolder(crumb.id, subPath);
            });
            li.appendChild(a);
        }
        ol.appendChild(li);
    });
}

function renderFolders(members) {
    const container = getModalEl().querySelector('[data-media-selector-folders]');
    container.innerHTML = '';
    members.forEach(function (folder) {
        const div = document.createElement('div');
        div.className = 'admin-media-selector-folder list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3';
        div.setAttribute('role', 'button');
        div.setAttribute('data-folder-id', folder.id);
        div.innerHTML =
            '<span class="d-inline-flex align-items-center justify-content-center rounded bg-body-secondary flex-shrink-0" style="width:48px;height:48px" aria-hidden="true">' +
            '<i class="fa-solid fa-folder text-warning fa-lg"></i></span>' +
            '<span class="fw-semibold">' + escapeHtml(folder.name) + '</span>';
        container.appendChild(div);
    });
}

function renderItems(members) {
    const container = getModalEl().querySelector('[data-media-selector-items]');
    container.innerHTML = '';
    members.forEach(function (item) {
        const div = document.createElement('div');
        div.className = 'admin-media-selector-item list-group-item d-flex align-items-center gap-3 py-2 px-3';
        div.setAttribute('role', 'button');
        div.setAttribute('data-media-item-id', item.id);
        div.setAttribute('data-media-item-name', item.name || '');

        let thumbHtml;
        if (item.type === 'image' && item.thumbnailPath) {
            thumbHtml = '<img src="' + escapeAttr(item.thumbnailPath) + '" alt="" class="rounded">';
        } else if (item.type === 'image' && item.path) {
            thumbHtml = '<img src="' + escapeAttr(item.path) + '" alt="" class="rounded">';
        } else {
            thumbHtml = '<span class="badge text-bg-secondary d-inline-flex align-items-center justify-content-center admin-media-selector-pdf-badge">' + escapeHtml(item.extension || 'PDF') + '</span>';
        }

        div.innerHTML = thumbHtml +
            '<div class="min-w-0"><div class="fw-semibold text-truncate">' + escapeHtml(item.name || '') + '</div>' +
            '<div class="small text-muted">' + escapeHtml(item.type || '') + (item.extension ? ' · .' + escapeHtml(item.extension) : '') + '</div></div>';

        container.appendChild(div);
    });
}

function showEmpty(visible) {
    const el = getModalEl().querySelector('[data-media-selector-empty]');
    if (el) el.hidden = !visible;
}

function showLoading(visible) {
    const el = getModalEl().querySelector('[data-media-selector-loading]');
    if (el) el.hidden = !visible;
}

let currentBreadcrumb = [];

async function loadFolder(folderId, breadcrumb) {
    currentBreadcrumb = breadcrumb;
    renderBreadcrumb(breadcrumb);
    showLoading(true);
    showEmpty(false);

    const foldersContainer = getModalEl().querySelector('[data-media-selector-folders]');
    const itemsContainer = getModalEl().querySelector('[data-media-selector-items]');
    foldersContainer.innerHTML = '';
    itemsContainer.innerHTML = '';

    try {
        const [foldersData, itemsData] = await Promise.all([
            folderId ? fetchSubFolders(folderId) : fetchRootFolders(),
            folderId ? fetchFolderItems(folderId) : fetchRootItems(),
        ]);

        const folders = foldersData['member'] || [];
        const items = itemsData['member'] || [];

        renderFolders(folders);
        renderItems(items);
        showEmpty(folders.length === 0 && items.length === 0);
    } catch (err) {
        console.error('Media selector: fetch error', err);
        showEmpty(true);
    } finally {
        showLoading(false);
    }

    bindFolderClicks();
    bindItemClicks();
}

function bindFolderClicks() {
    getModalEl().querySelectorAll('[data-folder-id]').forEach(function (el) {
        el.addEventListener('click', function () {
            const id = parseInt(el.dataset.folderId, 10);
            const name = el.querySelector('.fw-semibold')?.textContent || '';
            const newPath = currentBreadcrumb.concat([{ id: id, name: name }]);
            loadFolder(id, newPath);
        });
    });
}

function bindItemClicks() {
    getModalEl().querySelectorAll('[data-media-item-id]').forEach(function (el) {
        el.addEventListener('click', function () {
            selectItem(el);
        });
    });
}

function selectItem(el) {
    if (!activeWrapper) return;

    const id = el.dataset.mediaItemId;
    const name = el.dataset.mediaItemName || '';
    const img = el.querySelector('img');
    const thumbSrc = img ? img.src : '';

    const hiddenInput = activeWrapper.querySelector('input[type="hidden"]');
    if (hiddenInput) hiddenInput.value = id;

    updatePreview(activeWrapper, id, name, thumbSrc);

    const removeBtn = activeWrapper.querySelector('[data-media-selector-remove]');
    if (removeBtn) removeBtn.disabled = false;

    const modal = getModal();
    if (modal) modal.hide();
}

function updatePreview(wrapper, id, name, thumbSrc) {
    const preview = wrapper.querySelector('[data-media-selector-preview]');
    if (!preview) return;

    if (!id) {
        preview.innerHTML = '<span class="text-muted">Kein Bild ausgewählt</span>';
        return;
    }

    let html = '';
    if (thumbSrc) {
        html += '<img src="' + escapeAttr(thumbSrc) + '" alt="">';
    }
    html += '<span class="text-truncate">' + escapeHtml(name) + '</span>';
    preview.innerHTML = html;
}

function clearSelection(wrapper) {
    const hiddenInput = wrapper.querySelector('input[type="hidden"]');
    if (hiddenInput) hiddenInput.value = '';

    updatePreview(wrapper, null, '', '');

    const removeBtn = wrapper.querySelector('[data-media-selector-remove]');
    if (removeBtn) removeBtn.disabled = true;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function escapeAttr(str) {
    return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

document.querySelectorAll('[data-media-selector]').forEach(function (wrapper) {
    const openBtn = wrapper.querySelector('[data-media-selector-open]');
    if (openBtn) {
        openBtn.addEventListener('click', function () {
            activeWrapper = wrapper;
            loadFolder(null, []);
            const modal = getModal();
            if (modal) modal.show();
        });
    }

    const removeBtn = wrapper.querySelector('[data-media-selector-remove]');
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            clearSelection(wrapper);
        });
    }
});
