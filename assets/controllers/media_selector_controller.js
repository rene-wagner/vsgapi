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

            this.#updatePreview(wrapper, null, '', '');
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
        const name = el.dataset.mediaItemName || '';
        const img = el.querySelector('img');
        const thumbSrc = img ? img.src : '';

        const hiddenInput = this.activeWrapper.querySelector('input[type="hidden"]');
        if (hiddenInput) hiddenInput.value = id;

        this.#updatePreview(this.activeWrapper, id, name, thumbSrc);

        const removeBtn = this.activeWrapper.querySelector('[data-media-selector-remove]');
        if (removeBtn) removeBtn.disabled = false;

        this.modalInstance.hide();
    }

    #updatePreview(wrapper, id, name, thumbSrc) {
        const preview = wrapper.querySelector('[data-media-selector-preview]');
        if (!preview) return;

        if (!id) {
            preview.innerHTML = '<span class="text-sm text-gray-500">Kein Bild ausgewählt</span>';
            return;
        }

        let html = '';
        if (thumbSrc) {
            html += '<img src="' + this.#escapeAttr(thumbSrc) + '" alt="">';
        }
        html += '<span class="truncate text-sm text-gray-900">' + this.#escapeHtml(name) + '</span>';
        preview.innerHTML = html;
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