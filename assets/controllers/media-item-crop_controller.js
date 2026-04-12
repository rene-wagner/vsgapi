import { Controller } from '@hotwired/stimulus';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.min.css';

/* stimulusFetch: 'lazy' */

export default class extends Controller {
    static targets = ['image', 'form', 'reset', 'selectionWidth', 'selectionHeight'];

    connect() {
        if (!(this.imageTarget instanceof HTMLImageElement)) {
            throw new Error('media-item-crop: image target missing or not an image');
        }
        if (!(this.formTarget instanceof HTMLFormElement)) {
            throw new Error('media-item-crop: form target missing or not a form');
        }

        this.cropper = new Cropper(this.imageTarget, {
            viewMode: 1,
            responsive: true,
            autoCropArea: 1,
            rotatable: false,
            scalable: false,
            zoomable: true,
            checkOrientation: true,
            ready: () => {
                this.#updateSelectionDimensions();
            },
        });

        this._onCrop = this.#updateSelectionDimensions.bind(this);
        this._onSubmit = this.#populateCropFields.bind(this);
        this._onReset = this.#resetCrop.bind(this);

        this.imageTarget.addEventListener('crop', this._onCrop);
        this.formTarget.addEventListener('submit', this._onSubmit);

        if (this.hasResetTarget && this.resetTarget instanceof HTMLButtonElement) {
            this.resetTarget.addEventListener('click', this._onReset);
        }
    }

    disconnect() {
        if (this.hasResetTarget && this.resetTarget instanceof HTMLButtonElement) {
            this.resetTarget.removeEventListener('click', this._onReset);
        }
        this.formTarget.removeEventListener('submit', this._onSubmit);
        this.imageTarget.removeEventListener('crop', this._onCrop);

        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    }

    #updateSelectionDimensions() {
        if (!(this.hasSelectionWidthTarget && this.hasSelectionHeightTarget)) {
            return;
        }
        if (!(this.selectionWidthTarget instanceof HTMLInputElement) || !(this.selectionHeightTarget instanceof HTMLInputElement)) {
            return;
        }
        const d = this.cropper.getData(true);
        if (d.width > 0 && d.height > 0) {
            this.selectionWidthTarget.value = String(d.width);
            this.selectionHeightTarget.value = String(d.height);
        } else {
            this.selectionWidthTarget.value = '—';
            this.selectionHeightTarget.value = '—';
        }
    }

    #resetCrop() {
        this.cropper.reset();
        this.#updateSelectionDimensions();
    }

    #populateCropFields() {
        const imageData = this.cropper.getImageData();
        const nw = imageData.naturalWidth;
        const nh = imageData.naturalHeight;
        if (!nw || !nh) {
            this.#clearCropFields();
            return;
        }
        const d = this.cropper.getData(true);
        if (d.width <= 0 || d.height <= 0) {
            this.#clearCropFields();
            return;
        }
        const fx = document.getElementById('media_item_edit_crop_x');
        const fy = document.getElementById('media_item_edit_crop_y');
        const fw = document.getElementById('media_item_edit_crop_w');
        const fh = document.getElementById('media_item_edit_crop_h');
        const fnw = document.getElementById('media_item_edit_crop_natural_w');
        const fnh = document.getElementById('media_item_edit_crop_natural_h');
        if (fx) {
            fx.value = String(d.x);
        }
        if (fy) {
            fy.value = String(d.y);
        }
        if (fw) {
            fw.value = String(d.width);
        }
        if (fh) {
            fh.value = String(d.height);
        }
        if (fnw) {
            fnw.value = String(Math.round(nw));
        }
        if (fnh) {
            fnh.value = String(Math.round(nh));
        }
    }

    #clearCropFields() {
        const ids = [
            'media_item_edit_crop_x',
            'media_item_edit_crop_y',
            'media_item_edit_crop_w',
            'media_item_edit_crop_h',
            'media_item_edit_crop_natural_w',
            'media_item_edit_crop_natural_h',
        ];
        for (const id of ids) {
            const el = document.getElementById(id);
            if (el instanceof HTMLInputElement) {
                el.value = '';
            }
        }
    }
}