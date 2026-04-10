import './styles/app.css';

// Bootstrap CSS
import 'bootstrap/dist/css/bootstrap.min.css';

// Font Awesome CSS
import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/css/solid.min.css';

// Bootstrap JS
import 'bootstrap';

// Stimulus (for Symfony UX controllers)
import './stimulus_bootstrap.js';

// Vue.js (für Symfony UX)
import { registerVueControllerComponents } from '@symfony/ux-vue';
registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));

// Lazy-Imports
if (document.querySelector('[data-mediathek-dropzone]')) {
    void import('./mediathek-dropzone.js');
}
if (document.querySelector('[data-media-item-crop]')) {
    void import('./media-item-crop.js');
}
if (document.querySelector('form textarea')) {
    void import('./easymde-init.js');
}
if (document.querySelector('[data-media-selector]')) {
    void import('./media-selector.js');
}