import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    open(event) {
        event.preventDefault();
        document.dispatchEvent(new CustomEvent('media-selector:open', {
            detail: { wrapper: this.element },
        }));
    }

    remove(event) {
        event.preventDefault();
        document.dispatchEvent(new CustomEvent('media-selector:remove', {
            detail: { wrapper: this.element },
        }));
    }
}