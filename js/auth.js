// auth.js — клиентская валидация форм входа и регистрации
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[data-auth-form]');
    if (!form) return;

    const clearHighlight = function (input) { input.style.borderColor = ''; };

    form.querySelectorAll('.auth-input').forEach(function (input) {
        input.addEventListener('input', function () { clearHighlight(input); });
    });

    form.addEventListener('submit', function (e) {
        let valid = true;

        form.querySelectorAll('.auth-input[required]').forEach(function (input) {
            if (!input.value.trim()) {
                valid = false;
                input.style.borderColor = '#ef4444';
            }
        });

        const email = form.querySelector('input[type="email"]');
        if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            valid = false;
            email.style.borderColor = '#ef4444';
        }

        const pass = form.querySelector('input[type="password"]');
        if (pass && pass.hasAttribute('minlength') && pass.value && pass.value.length < parseInt(pass.getAttribute('minlength'), 10)) {
            valid = false;
            pass.style.borderColor = '#ef4444';
        }

        if (!valid) { e.preventDefault(); }
    });
});
