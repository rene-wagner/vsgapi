/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';
import { Modal } from 'flowbite';

export default class extends Controller {
    static targets = ['breadcrumb', 'folders', 'items', 'empty', 'loading'];
    static values = {
        foldersUrl: { type: String, default: '/api/media_folders' },
        itemsUrl: { type: String, default: '/api/media_items' },
    };

    #onDocumentClickBound;
    connect() {
        this.modalInstance = new Modal(this.element, {
            onHide: () => {
                this.activeWrapper = null;
            },
        });
        this.activeWrapper = null;
        this.currentBreadcrumb = [];

        this.#onDocumentClickBound = this.#onDocumentClick.bind(this);
        document.addEventListener('click', this.#onDocumentClickBound);
    }

    disconnect() {
        document.removeEventListener('click', this.#onDocumentClickBound);
    }

    #onDocumentClick(event) {
        const openButton = event.target.closest('[data-media-selector-open]');
        if (openButton) {
            event.preventDefault();
            const wrapper = openButton.closest('[data-media-selector]');
            if (!wrapper) {
                return;
            }

            this.activeWrapper = wrapper;
            this.#loadFolder(null, []);
            this.modalInstance.show();

            return;
        }

        const removeButton = event.target.closest('[data-media-selector-remove]');
        if (removeButton) {
            event.preventDefault();
            const wrapper = removeButton.closest('[data-media-selector]');
            if (!wrapper) {
                return;
            }

            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            if (hiddenInput) hiddenInput.value = '';

            this.#updatePreview(wrapper, null);
            removeButton.disabled = true;
        }
    }

    async #fetchJson(url) {
        const res = await fetch(url, {
            headers: { Accept: 'application/ld+json' },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    async #loadFolder(folderId, breadcrumb) {
        this.currentBreadcrumb = breadcrumb;
        this.#renderBreadcrumb(breadcrumb);
        this.#showLoading(true);
        this.#showEmpty(false);

        this.foldersTarget.innerHTML = '';
        this.itemsTarget.innerHTML = '';

        try {
            const [foldersData, itemsData] = await Promise.all([
                folderId
                    ? this.#fetchJson(this.foldersUrlValue + '?parent=' + this.foldersUrlValue + '/' + folderId)
                    : this.#fetchJson(this.foldersUrlValue + '?exists[parent]=false'),
                folderId
                    ? this.#fetchJson(this.itemsUrlValue + '?folder=' + this.foldersUrlValue + '/' + folderId)
                    : this.#fetchJson(this.itemsUrlValue + '?exists[folder]=false'),
            ]);

            const folders = foldersData['member'] || [];
            const items = itemsData['member'] || [];

            this.#renderFolders(folders);
            this.#renderItems(items);
            this.#showEmpty(folders.length === 0 && items.length === 0);
        } catch (err) {
            console.error('Media selector: fetch error', err);
            this.#showEmpty(true);
        } finally {
            this.#showLoading(false);
        }

        this.#bindFolderClicks();
        this.#bindItemClicks();
    }

    #renderBreadcrumb(path) {
        const ol = this.breadcrumbTarget;
        ol.innerHTML = '';

        const rootLi = document.createElement('li');
        if (path.length === 0) {
            rootLi.className = 'font-medium text-gray-900';
            rootLi.setAttribute('aria-current', 'page');
            rootLi.textContent = 'Mediathek';
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.className = 'font-medium text-blue-700 hover:underline';
            a.textContent = 'Mediathek';
            a.addEventListener('click', (e) => {
                e.preventDefault();
                this.#loadFolder(null, []);
            });
            rootLi.appendChild(a);
        }
        ol.appendChild(rootLi);

        path.forEach((crumb, idx) => {
            const separator = document.createElement('li');
            separator.className = 'text-gray-400';
            separator.setAttribute('aria-hidden', 'true');
            separator.textContent = '/';
            ol.appendChild(separator);

            const li = document.createElement('li');
            if (idx === path.length - 1) {
                li.className = 'font-medium text-gray-900';
                li.setAttribute('aria-current', 'page');
                li.textContent = crumb.name;
            } else {
                const a = document.createElement('a');
                a.href = '#';
                a.className = 'font-medium text-blue-700 hover:underline';
                a.textContent = crumb.name;
                const subPath = path.slice(0, idx + 1);
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.#loadFolder(crumb.id, subPath);
                });
                li.appendChild(a);
            }
            ol.appendChild(li);
        });
    }

    #renderFolders(members) {
        const container = this.foldersTarget;
        container.innerHTML = '';
        members.forEach((folder) => {
            const div = document.createElement('div');
            div.className = 'admin-media-selector-folder flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50';
            div.setAttribute('role', 'button');
            div.setAttribute('data-folder-id', folder.id);
            div.innerHTML =
                '<span class="inline-flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100" aria-hidden="true">' +
                '<i class="fa-solid fa-folder fa-lg text-yellow-500"></i></span>' +
                '<span class="font-semibold text-gray-900">' + this.#escapeHtml(folder.name) + '</span>';
            container.appendChild(div);
        });
    }

    #renderItems(members) {
        const container = this.itemsTarget;
        container.innerHTML = '';
        members.forEach((item) => {
            const div = document.createElement('div');
            div.className = 'admin-media-selector-item flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50';
            div.setAttribute('role', 'button');
            div.setAttribute('data-media-item-id', item.id);
            div.setAttribute('data-media-item-name', item.name || '');
            div.setAttribute('data-media-item-type', item.type || '');
            div.setAttribute('data-media-item-extension', item.extension || '');
            div.setAttribute('data-media-item-thumbnail-url', item.thumbnail_url || '');
            div.setAttribute('data-media-item-original-url', item.original_url || '');
            div.setAttribute('data-media-item-size-human', item.size_human || '');

            let thumbHtml;
            if (item.type === 'image' && item.thumbnail_url) {
                thumbHtml = '<img src="' + this.#escapeAttr(item.thumbnail_url) + '" alt="" class="rounded-md">';
            } else if (item.type === 'image' && item.original_url) {
                thumbHtml = '<img src="' + this.#escapeAttr(item.original_url) + '" alt="" class="rounded-md">';
            } else {
                thumbHtml = '<span class="inline-flex items-center justify-center rounded-md bg-gray-200 px-2 text-xs font-semibold uppercase tracking-wide text-gray-700 admin-media-selector-pdf-badge">' + this.#escapeHtml(item.extension || 'PDF') + '</span>';
            }

            div.innerHTML = thumbHtml +
                '<div class="min-w-0"><div class="truncate font-semibold text-gray-900">' + this.#escapeHtml(item.name || '') + '</div>' +
                '<div class="text-sm text-gray-500">' + this.#escapeHtml(item.type || '') + (item.extension ? ' · .' + this.#escapeHtml(item.extension) : '') + '</div></div>';

            container.appendChild(div);
        });
    }

    #bindFolderClicks() {
        this.foldersTarget.querySelectorAll('[data-folder-id]').forEach((el) => {
            el.addEventListener('click', () => {
                const id = parseInt(el.dataset.folderId, 10);
                const name = el.querySelector('.font-semibold')?.textContent || '';
                const newPath = this.currentBreadcrumb.concat([{ id: id, name: name }]);
                this.#loadFolder(id, newPath);
            });
        });
    }

    #bindItemClicks() {
        this.itemsTarget.querySelectorAll('[data-media-item-id]').forEach((el) => {
            el.addEventListener('click', () => {
                this.#selectItem(el);
            });
        });
    }

    #selectItem(el) {
        if (!this.activeWrapper) return;

        const id = el.dataset.mediaItemId;
        const item = {
            id: id,
            name: el.dataset.mediaItemName || '',
            type: el.dataset.mediaItemType || '',
            extension: el.dataset.mediaItemExtension || '',
            thumbnailUrl: el.dataset.mediaItemThumbnailUrl || '',
            originalUrl: el.dataset.mediaItemOriginalUrl || '',
            sizeHuman: el.dataset.mediaItemSizeHuman || '',
        };

        const hiddenInput = this.activeWrapper.querySelector('input[type="hidden"]');
        if (hiddenInput) hiddenInput.value = id;

        this.#updatePreview(this.activeWrapper, item);

        const removeBtn = this.activeWrapper.querySelector('[data-media-selector-remove]');
        if (removeBtn) removeBtn.disabled = false;

        this.modalInstance.hide();
    }

    #updatePreview(wrapper, item) {
        const preview = wrapper.querySelector('[data-media-selector-preview]');
        if (!preview) return;

        const mode = preview.dataset.mediaSelectorPreviewMode || 'inline';

        if (!item || !item.id) {
            preview.innerHTML = mode === 'card'
                ? '<div class="flex min-h-[220px] flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gradient-to-br from-gray-50 to-white px-6 py-8 text-center"><span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400"><i class="fa-solid fa-image text-2xl" aria-hidden="true"></i></span><p class="mt-4 text-sm font-medium text-gray-700">Kein Medium ausgewählt</p><p class="mt-1 text-sm text-gray-500">Wählen Sie eine Datei aus der Mediathek aus.</p></div>'
                : '<span class="text-sm text-gray-500">Kein Bild ausgewählt</span>';
            return;
        }

        preview.innerHTML = mode === 'card'
            ? this.#renderCardPreview(item)
            : this.#renderInlinePreview(item);
    }

    #renderInlinePreview(item) {
        let html = '';
        const imageUrl = item.thumbnailUrl || item.originalUrl;

        if (imageUrl) {
            html += '<img src="' + this.#escapeAttr(imageUrl) + '" alt="">';
        }

        html += '<span class="truncate text-sm text-gray-900">' + this.#escapeHtml(item.name) + '</span>';

        return html;
    }

    #renderCardPreview(item) {
        const isImage = item.type === 'image';
        const previewUrl = item.thumbnailUrl || item.originalUrl;
        let mediaHtml = '';

        if (isImage && previewUrl) {
            mediaHtml = '<img src="' + this.#escapeAttr(previewUrl) + '" alt="' + this.#escapeAttr(item.name) + '" class="h-[180px] w-[180px] rounded-2xl border border-gray-200 object-contain bg-gradient-to-br from-gray-50 to-white p-3 shadow-sm">';
        } else if (item.type === 'pdf') {
            mediaHtml = '<div class="flex h-[180px] w-[180px] items-center justify-center rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white shadow-sm"><i class="fa-solid fa-file-pdf text-6xl text-red-600" aria-hidden="true"></i></div>';
        } else {
            mediaHtml = '<div class="flex h-[180px] w-[180px] items-center justify-center rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white shadow-sm"><i class="fa-solid fa-file text-6xl text-gray-500" aria-hidden="true"></i></div>';
        }

        const typeLabel = isImage ? 'Bild' : 'PDF';
        const editUrl = '/admin/mediathek/items/' + encodeURIComponent(item.id) + '/edit';

        return '<div class="grid gap-6 lg:grid-cols-[minmax(0,220px)_minmax(0,1fr)] lg:items-center">' +
            '<div class="flex justify-center lg:justify-start">' + mediaHtml + '</div>' +
            '<div class="min-w-0">' +
                '<h4 class="truncate text-base font-semibold text-gray-900">' + this.#escapeHtml(item.name) + '</h4>' +
                '<div class="mt-3 flex flex-wrap gap-2">' +
                    '<span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">' + this.#escapeHtml(typeLabel) + '</span>' +
                    '<span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">' + this.#escapeHtml(item.sizeHuman || '—') + '</span>' +
                '</div>' +
                '<p class="mt-4 text-sm text-gray-500">Sie können die aktuelle Datei austauschen oder in der Mediathek bearbeiten.</p>' +
                '<div class="mt-5 flex flex-wrap gap-3">' +
                    '<a href="' + this.#escapeAttr(editUrl) + '" class="inline-flex items-center rounded-lg border border-blue-700 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-300" aria-label="Bearbeiten" title="Bearbeiten"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span class="ml-2">Bearbeiten</span></a>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    #showEmpty(visible) {
        this.emptyTarget.hidden = !visible;
    }

    #showLoading(visible) {
        this.loadingTarget.hidden = !visible;
    }

    #escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    #escapeAttr(str) {
        return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
}