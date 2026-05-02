import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    // Guardrail: buttons inside forms without explicit type may submit the form
    // on first click. For JS-driven buttons, force type="button" globally.
    document.querySelectorAll('form button:not([type])').forEach((button) => {
        const hasJsHandler = button.hasAttribute('@click')
            || button.hasAttribute('x-on:click')
            || button.hasAttribute('onclick');

        if (hasJsHandler) {
            button.setAttribute('type', 'button');
        }
    });
});
