import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/css/solid.min.css';
import './styles/app.css';
import 'bootstrap';

if (document.querySelector('[data-mediathek-dropzone]')) {
    void import('./mediathek-dropzone.js');
}

if (document.querySelector('[data-media-item-crop]')) {
    void import('./media-item-crop.js');
}
