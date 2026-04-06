import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.min.css';

const root = document.querySelector('[data-media-item-crop]');
if (!root) {
    throw new Error('media-item-crop: root missing');
}

const img = root.querySelector('[data-media-item-crop-image]');
const form = root.querySelector('[data-media-item-crop-form]');
if (!(img instanceof HTMLImageElement) || !(form instanceof HTMLFormElement)) {
    throw new Error('media-item-crop: image or form missing');
}

const cropFieldIds = [
    'media_item_edit_crop_x',
    'media_item_edit_crop_y',
    'media_item_edit_crop_w',
    'media_item_edit_crop_h',
    'media_item_edit_crop_natural_w',
    'media_item_edit_crop_natural_h',
];

const fieldById = (id) => {
    const el = document.getElementById(id);
    return el instanceof HTMLInputElement ? el : null;
};

const clearCropFields = () => {
    for (const id of cropFieldIds) {
        const el = fieldById(id);
        if (el) {
            el.value = '';
        }
    }
};

const widthDisplay = root.querySelector('[data-media-item-crop-selection-width]');
const heightDisplay = root.querySelector('[data-media-item-crop-selection-height]');

const updateSelectionDimensions = () => {
    if (!(widthDisplay instanceof HTMLInputElement) || !(heightDisplay instanceof HTMLInputElement)) {
        return;
    }
    const d = cropper.getData(true);
    if (d.width > 0 && d.height > 0) {
        widthDisplay.value = String(d.width);
        heightDisplay.value = String(d.height);
    } else {
        widthDisplay.value = '—';
        heightDisplay.value = '—';
    }
};

const cropper = new Cropper(img, {
    viewMode: 1,
    responsive: true,
    autoCropArea: 1,
    rotatable: false,
    scalable: false,
    zoomable: true,
    checkOrientation: true,
    ready() {
        updateSelectionDimensions();
    },
});

img.addEventListener('crop', () => {
    updateSelectionDimensions();
});

const resetBtn = root.querySelector('[data-media-item-crop-reset]');
if (resetBtn instanceof HTMLButtonElement) {
    resetBtn.addEventListener('click', () => {
        cropper.reset();
        updateSelectionDimensions();
    });
}

form.addEventListener('submit', () => {
    const imageData = cropper.getImageData();
    const nw = imageData.naturalWidth;
    const nh = imageData.naturalHeight;
    if (!nw || !nh) {
        clearCropFields();
        return;
    }
    const d = cropper.getData(true);
    if (d.width <= 0 || d.height <= 0) {
        clearCropFields();
        return;
    }
    const fx = fieldById('media_item_edit_crop_x');
    const fy = fieldById('media_item_edit_crop_y');
    const fw = fieldById('media_item_edit_crop_w');
    const fh = fieldById('media_item_edit_crop_h');
    const fnw = fieldById('media_item_edit_crop_natural_w');
    const fnh = fieldById('media_item_edit_crop_natural_h');
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
});
