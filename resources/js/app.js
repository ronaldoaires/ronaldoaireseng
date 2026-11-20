// While a form is being sent, disable its submit button and show a spinner
// (styled in app.css via [data-loading]). This blocks double submissions and
// gives feedback on slow connections.
// Opt out on a form with the data-no-loading attribute, e.g. when the response
// is a file download and the page never reloads.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (! (form instanceof HTMLFormElement) || form.hasAttribute('data-no-loading')) {
        return;
    }

    const button = event.submitter
        ?? form.querySelector('button[type="submit"], button:not([type])');

    if (! button || button.disabled) {
        return;
    }

    // A disabled button is left out of the submitted data, so a named submit
    // button (e.g. name="action" value="approve") would lose its value.
    // Carry it in a hidden input before disabling the button.
    if (button.name) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = button.name;
        hidden.value = button.value;
        hidden.setAttribute('data-submitter-value', '');
        form.appendChild(hidden);
    }

    button.setAttribute('data-loading', '');
    button.setAttribute('aria-busy', 'true');
    button.disabled = true;
});

// Coming back with the browser's back button can restore the page with the
// button still disabled, so reset it.
window.addEventListener('pageshow', (event) => {
    if (! event.persisted) {
        return;
    }

    document.querySelectorAll('button[data-loading]').forEach((button) => {
        button.removeAttribute('data-loading');
        button.removeAttribute('aria-busy');
        button.disabled = false;
    });

    // Drop the hidden copies of the submit button values added above.
    document.querySelectorAll('input[data-submitter-value]').forEach((input) => input.remove());
});

// Brazilian phone mask for inputs marked with data-mask="phone".
// Landline: (99) 9999-9999. Mobile: (99) 99999-9999.
document.addEventListener('input', (event) => {
    const input = event.target;

    if (! (input instanceof HTMLInputElement) || input.dataset.mask !== 'phone') {
        return;
    }

    const digits = input.value.replace(/\D/g, '').slice(0, 11);

    if (digits.length > 10) {
        input.value = digits.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
    } else if (digits.length > 6) {
        input.value = digits.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
    } else if (digits.length > 2) {
        input.value = digits.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
    } else if (digits.length > 0) {
        // No closing parenthesis yet, so backspace keeps working at this point.
        input.value = '(' + digits;
    } else {
        input.value = '';
    }
});

// Image preview for inputs with data-image-preview="<img id>".
// Optional attributes:
//   data-max-kb       refuses a bigger file right away, before any upload.
//   data-error-target id of an element where the refusal message is shown.
document.addEventListener('change', (event) => {
    const input = event.target;

    if (! (input instanceof HTMLInputElement) || ! input.dataset.imagePreview) {
        return;
    }

    const errorBox = input.dataset.errorTarget
        ? document.getElementById(input.dataset.errorTarget)
        : null;

    const showError = (message) => {
        if (! errorBox) {
            return;
        }

        errorBox.textContent = message ?? '';
        errorBox.classList.toggle('hidden', ! message);
    };

    // A new selection always starts without the previous message.
    showError(null);

    const file = input.files?.[0];

    if (! file) {
        return;
    }

    if (! file.type.startsWith('image/')) {
        input.value = '';
        showError('O arquivo precisa ser uma imagem JPG, PNG ou WebP.');

        return;
    }

    const maxKb = Number(input.dataset.maxKb || 0);

    if (maxKb && file.size > maxKb * 1024) {
        const limit = maxKb >= 1024 ? (maxKb / 1024) + ' MB' : maxKb + ' KB';
        const size  = (file.size / 1024 / 1024).toFixed(1).replace('.', ',');

        input.value = '';
        showError('A imagem escolhida tem ' + size + ' MB. O máximo permitido é ' + limit + '.');

        return;
    }

    const preview = document.getElementById(input.dataset.imagePreview);

    if (! preview) {
        return;
    }

    const url = URL.createObjectURL(file);

    preview.onload = () => URL.revokeObjectURL(url);
    preview.src = url;
    // The <img> starts hidden when the user has no photo yet.
    preview.classList.remove('hidden');
});

// CPF mask for inputs marked with data-mask="cpf": 999.999.999-99.
document.addEventListener('input', (event) => {
    const input = event.target;

    if (! (input instanceof HTMLInputElement) || input.dataset.mask !== 'cpf') {
        return;
    }

    const digits = input.value.replace(/\D/g, '').slice(0, 11);

    input.value = digits
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
});
// Lazy Cloudflare Turnstile for forms marked with <div data-turnstile-lazy>
// (the footer "Fale conosco" form, present on every page). The challenge
// script is downloaded only when the visitor first focuses the form, so
// ordinary page views stay light. Forms with a regular .cf-turnstile widget
// are not affected.
const renderLazyTurnstile = (container) => {
    if (container.dataset.rendered) {
        return;
    }

    container.dataset.rendered = '1';

    window.turnstile.render(container, {
        sitekey: container.dataset.sitekey,
        language: 'pt-br',
        size: 'flexible',
    });
};

let turnstileLoading = null;

const loadTurnstile = () => {
    if (window.turnstile) {
        return Promise.resolve();
    }

    turnstileLoading ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');

        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
        script.async = true;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });

    return turnstileLoading;
};

document.addEventListener('focusin', (event) => {
    const form = event.target instanceof Element ? event.target.closest('form') : null;
    const container = form?.querySelector('[data-turnstile-lazy]');

    if (! container || container.dataset.rendered) {
        return;
    }

    loadTurnstile().then(() => renderLazyTurnstile(container)).catch(() => {
        // Script blocked or offline: the server answers with a clear error on submit.
    });
});

// A form that came back with errors already has the visitor's attention.
document.querySelectorAll('[data-turnstile-lazy][data-autoload]').forEach((container) => {
    loadTurnstile().then(() => renderLazyTurnstile(container)).catch(() => {});
});

// Carousels (Swiper) are imported only when the page has one.
const carousels = document.querySelectorAll('[data-carousel]');

if (carousels.length) {
    import('./carousel.js').then(({ mountCarousels }) => mountCarousels(carousels));
}

const structureHero = document.querySelector('[data-structure-hero]');

if (structureHero) {
    import('./structure-hero.js').then(({ mountStructureHero }) => mountStructureHero(structureHero));
}

const stickyBuyBar = document.querySelector('[data-sticky-buy-bar]');
const primaryBuyButton = document.querySelector('[data-primary-buy-box] a');

if (stickyBuyBar && primaryBuyButton) {
    new IntersectionObserver(([entry]) => {
        stickyBuyBar.classList.toggle('hidden', entry.isIntersecting);
    }, { threshold: 0.5 }).observe(primaryBuyButton);
}

// One native dialog pattern for previews and future site modals.
document.addEventListener('click', (event) => {
    if (! (event.target instanceof Element)) {
        return;
    }

    const trigger = event.target.closest('[data-modal-open]');

    if (trigger) {
        const dialog = document.getElementById(trigger.dataset.modalOpen);

        if (! (dialog instanceof HTMLDialogElement)) {
            return;
        }

        const image = dialog.querySelector('[data-modal-image]');

        if (image instanceof HTMLImageElement && trigger.dataset.modalImageSrc) {
            image.src = trigger.dataset.modalImageSrc;
            image.alt = trigger.dataset.modalImageAlt ?? '';

            const caption = dialog.querySelector('[data-modal-image-caption]');

            if (caption) {
                caption.textContent = image.alt;
            }
        }

        if (! dialog.open) {
            dialog.showModal();
        }

        return;
    }

    const close = event.target.closest('[data-modal-close]');

    if (close) {
        close.closest('dialog')?.close();

        return;
    }

    if (event.target instanceof HTMLDialogElement && event.target.hasAttribute('data-modal-backdrop')) {
        const bounds = event.target.getBoundingClientRect();

        if (event.clientX < bounds.left || event.clientX > bounds.right
            || event.clientY < bounds.top || event.clientY > bounds.bottom) {
            event.target.close();
        }
    }
});
