import { Controller } from '@hotwired/stimulus';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

/* stimulusFetch: 'lazy' */

export default class extends Controller {
    static targets = ['image', 'cropX', 'cropY', 'cropWidth', 'cropHeight'];

    static values = {
        initialX: { type: Number, default: 0 },
        initialY: { type: Number, default: 0 },
        initialWidth: { type: Number, default: 0 },
        initialHeight: { type: Number, default: 0 },
    };

    connect() {
        const image = this.imageTarget;
        const hasInitialData =
            this.initialXValue > 0 ||
            this.initialYValue > 0 ||
            this.initialWidthValue > 0 ||
            this.initialHeightValue > 0;

        this.cropper = new Cropper(image, {
            viewMode: 1,
            autoCropArea: hasInitialData ? 0 : 1,
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: true,
            ready: () => {
                if (hasInitialData) {
                    this.cropper.setData({
                        x: this.initialXValue,
                        y: this.initialYValue,
                        width: this.initialWidthValue,
                        height: this.initialHeightValue,
                    });
                }
                this.syncFieldsFromCropper();
            },
            crop: () => {
                this.syncFieldsFromCropper();
            },
        });
    }

    disconnect() {
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    }

    syncFieldsFromCropper() {
        if (!this.cropper) {
            return;
        }
        const data = this.cropper.getData(true);
        if (!data) {
            return;
        }
        this.cropXTarget.value = data.x;
        this.cropYTarget.value = data.y;
        this.cropWidthTarget.value = data.width;
        this.cropHeightTarget.value = data.height;
    }

    syncCropperFromFields() {
        if (!this.cropper) {
            return;
        }
        const x = parseInt(this.cropXTarget.value, 10);
        const y = parseInt(this.cropYTarget.value, 10);
        const w = parseInt(this.cropWidthTarget.value, 10);
        const h = parseInt(this.cropHeightTarget.value, 10);

        if (Number.isNaN(x) || Number.isNaN(y) || Number.isNaN(w) || Number.isNaN(h)) {
            return;
        }
        if (w <= 0 || h <= 0) {
            return;
        }

        this.cropper.setData({ x, y, width: w, height: h });
    }

    resetCrop() {
        if (!this.cropper) {
            return;
        }
        this.cropper.reset();
        this.cropXTarget.value = '';
        this.cropYTarget.value = '';
        this.cropWidthTarget.value = '';
        this.cropHeightTarget.value = '';
    }
}