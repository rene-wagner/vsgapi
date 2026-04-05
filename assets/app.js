import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/css/solid.min.css';
import './styles/app.css';
import 'bootstrap';

document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const id = btn.getAttribute('data-copy-target');
        const el = document.getElementById(id);
        if (!el) {
            return;
        }
        el.select();
        el.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(el.value).catch(function () {
            document.execCommand('copy');
        });
    });
});
