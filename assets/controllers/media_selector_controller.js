/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['breadcrumb', 'folders', 'items', 'empty', 'loading'];
    static values = {
        foldersUrl: { type: String, default: '/api/media_folders' },
        itemsUrl: { type: String, default: '/api/media_items' },
    };

    #onOpenBound;
    #onRemoveBound;
    #onHiddenBound;

    connect() {
        this.modalInstance = Modal.getOrCreateInstance(this.element);
        this.activeWrapper = null;
        this.currentBreadcrumb = [];

        this.#onOpenBound = this.#onOpen.bind(this);
        this.#onRemoveBound = this.#onRemove.bind(this);
        this.#onHiddenBound = () => { this.activeWrapper = null; };

        document.addEventListener('media-selector:open', this.#onOpenBound);
        document.addEventListener('media-selector:remove', this.#onRemoveBound);
        this.element.addEventListener('hidden.bs.modal', this.#onHiddenBound);
    }

    disconnect() {
        document.removeEventListener('media-selector:open', this.#onOpenBound);
        document.removeEventListener('media-selector:remove', this.#onRemoveBound);
        this.element.removeEventListener('hidden.bs.modal', this.#onHiddenBound);
        this.modalInstance?.dispose();
    }

    #onOpen(event) {
        this.activeWrapper = event.detail.wrapper;
        this.#loadFolder(null, []);
        this.modalInstance.show();
    }

    #onRemove(event) {
        const wrapper = event.detail.wrapper;
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        if (hiddenInput) hiddenInput.value = '';

        this.#updatePreview(wrapper, null, '', '');

        const removeBtn = wrapper.querySelector('[data-media-selector-remove]');
        if (removeBtn) removeBtn.disabled = true;
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
        rootLi.className = 'breadcrumb-item';
        if (path.length === 0) {
            rootLi.classList.add('active');
            rootLi.setAttribute('aria-current', 'page');
            rootLi.textContent = 'Mediathek';
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.textContent = 'Mediathek';
            a.addEventListener('click', (e) => {
                e.preventDefault();
                this.#loadFolder(null, []);
            });
            rootLi.appendChild(a);
        }
        ol.appendChild(rootLi);

        path.forEach((crumb, idx) => {
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
            div.className = 'admin-media-selector-folder list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3';
            div.setAttribute('role', 'button');
            div.setAttribute('data-folder-id', folder.id);
            div.innerHTML =
                '<span class="d-inline-flex align-items-center justify-content-center rounded bg-body-secondary flex-shrink-0" style="width:48px;height:48px" aria-hidden="true">' +
                '<i class="fa-solid fa-folder text-warning fa-lg"></i></span>' +
                '<span class="fw-semibold">' + this.#escapeHtml(folder.name) + '</span>';
            container.appendChild(div);
        });
    }

    #renderItems(members) {
        const container = this.itemsTarget;
        container.innerHTML = '';
        members.forEach((item) => {
            const div = document.createElement('div');
            div.className = 'admin-media-selector-item list-group-item d-flex align-items-center gap-3 py-2 px-3';
            div.setAttribute('role', 'button');
            div.setAttribute('data-media-item-id', item.id);
            div.setAttribute('data-media-item-name', item.name || '');

            let thumbHtml;
            if (item.type === 'image' && item.thumbnail_url) {
                thumbHtml = '<img src="' + this.#escapeAttr(item.thumbnail_url) + '" alt="" class="rounded">';
            } else if (item.type === 'image' && item.original_url) {
                thumbHtml = '<img src="' + this.#escapeAttr(item.original_url) + '" alt="" class="rounded">';
            } else {
                thumbHtml = '<span class="badge text-bg-secondary d-inline-flex align-items-center justify-content-center admin-media-selector-pdf-badge">' + this.#escapeHtml(item.extension || 'PDF') + '</span>';
            }

            div.innerHTML = thumbHtml +
                '<div class="min-w-0"><div class="fw-semibold text-truncate">' + this.#escapeHtml(item.name || '') + '</div>' +
                '<div class="small text-muted">' + this.#escapeHtml(item.type || '') + (item.extension ? ' · .' + this.#escapeHtml(item.extension) : '') + '</div></div>';

            container.appendChild(div);
        });
    }

    #bindFolderClicks() {
        this.foldersTarget.querySelectorAll('[data-folder-id]').forEach((el) => {
            el.addEventListener('click', () => {
                const id = parseInt(el.dataset.folderId, 10);
                const name = el.querySelector('.fw-semibold')?.textContent || '';
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
            preview.innerHTML = '<span class="text-muted">Kein Bild ausgewählt</span>';
            return;
        }

        let html = '';
        if (thumbSrc) {
            html += '<img src="' + this.#escapeAttr(thumbSrc) + '" alt="">';
        }
        html += '<span class="text-truncate">' + this.#escapeHtml(name) + '</span>';
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