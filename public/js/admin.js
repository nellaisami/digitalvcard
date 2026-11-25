document.addEventListener('DOMContentLoaded', function () {
    const defaultAvatar = document.querySelector('meta[name="default-avatar"]')?.content || '/images/default-avatar.svg';
    const sidebar = document.getElementById('sidebar');
    const scrim = document.querySelector('.sidebar-scrim');
    const closeSidebar = function () {
        if (sidebar) sidebar.classList.remove('is-open');
        if (scrim) scrim.classList.remove('is-open');
    };
    document.querySelectorAll('[data-sidebar-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            sidebar.classList.add('is-open');
            scrim.classList.add('is-open');
        });
    });
    document.querySelectorAll('[data-sidebar-close]').forEach(function (button) {
        button.addEventListener('click', closeSidebar);
    });

    const profileToggle = document.querySelector('[data-profile-toggle]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            profileMenu.classList.toggle('open');
            profileToggle.setAttribute('aria-expanded', profileMenu.classList.contains('open') ? 'true' : 'false');
        });
        document.addEventListener('click', function (event) {
            if (!profileMenu.contains(event.target) && !profileToggle.contains(event.target)) {
                profileMenu.classList.remove('open');
                profileToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const notificationToggle = document.querySelector('[data-notification-toggle]');
    const notificationPopover = document.querySelector('[data-notification-popover]');
    if (notificationToggle && notificationPopover) {
        notificationToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            notificationPopover.classList.toggle('open');
            notificationToggle.setAttribute('aria-expanded', notificationPopover.classList.contains('open') ? 'true' : 'false');
        });
        document.addEventListener('click', function (event) {
            if (!notificationPopover.contains(event.target) && !notificationToggle.contains(event.target)) {
                notificationPopover.classList.remove('open');
                notificationToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = button.parentElement.querySelector('input');
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
            button.innerHTML = visible ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
        });
    });

    document.querySelectorAll('[data-dismiss]').forEach(function (button) {
        button.addEventListener('click', function () { button.closest('.alert').remove(); });
    });
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-photo-input]').forEach(function (input) {
        const preview = document.querySelector('[data-photo-preview]');
        const removeCheckbox = document.querySelector('[data-photo-remove]');
        if (!preview) return;

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;
            if (removeCheckbox) removeCheckbox.checked = false;
            const reader = new FileReader();
            reader.addEventListener('load', function () { preview.src = reader.result; });
            reader.readAsDataURL(file);
        });

        if (removeCheckbox) {
            removeCheckbox.addEventListener('change', function () {
                if (removeCheckbox.checked) {
                    input.value = '';
                    preview.src = defaultAvatar;
                }
            });
        }
    });

    document.querySelectorAll('[data-template-image-input]').forEach(function (input) {
        const preview = document.querySelector('[data-template-preview]');
        if (!preview) return;

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.addEventListener('load', function () { preview.src = reader.result; });
            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('img.avatar-photo').forEach(function (image) {
        image.addEventListener('error', function () {
            image.src = defaultAvatar;
        }, { once: true });
    });

    document.querySelectorAll('[data-template-image]').forEach(function (image) {
        image.addEventListener('error', function () {
            const fallback = document.querySelector('meta[name="default-vcard-template"]');
            if (fallback) image.src = fallback.content;
        }, { once: true });
    });

    document.querySelectorAll('[data-template-image]').forEach(function (image) {
        image.addEventListener('error', function () {
            const fallback = document.querySelector('meta[name="default-vcard-template"]');
            if (fallback) image.src = fallback.content;
        }, { once: true });
    });

    function showToast(message, type) {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.textContent = message;
        container.appendChild(toast);
        window.setTimeout(function () { toast.classList.add('toast-visible'); }, 10);
        window.setTimeout(function () {
            toast.classList.remove('toast-visible');
            window.setTimeout(function () { toast.remove(); }, 200);
        }, 3500);
    }

    document.querySelectorAll('[data-flash-toast]').forEach(function (alert) {
        showToast(alert.dataset.toastMessage, alert.dataset.toastType);
        alert.remove();
    });

    document.querySelectorAll('[data-async-toggle]').forEach(function (button) {
        button.addEventListener('click', async function () {
            const csrf = document.querySelector('meta[name="csrf-token"]');
            const label = button.parentElement.querySelector('[data-toggle-label]');
            button.disabled = true;
            try {
                const response = await fetch(button.dataset.toggleUrl, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf ? csrf.content : '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'The update could not be completed.');

                const state = button.dataset.toggleKind === 'verification' ? result.verified : result.status === 'active';
                button.setAttribute('aria-checked', state ? 'true' : 'false');
                if (label) {
                    label.textContent = result.label;
                    if (label.classList.contains('status-badge')) {
                        label.classList.toggle('status-active', state);
                        label.classList.toggle('status-inactive', !state);
                    }
                }
                showToast(result.message, 'success');
            } catch (error) {
                showToast(error.message || 'The update failed. Please try again.', 'error');
            } finally {
                button.disabled = false;
            }
        });
    });

    const featureMaster = document.querySelector('[data-feature-master]');
    const featureInputs = Array.from(document.querySelectorAll('[data-plan-feature]'));
    if (featureMaster && featureInputs.length) {
        const syncFeatureControls = function () {
            const enabled = featureInputs.filter(function (input) { return input.checked; });
            featureMaster.checked = enabled.length === featureInputs.length;
            featureMaster.indeterminate = enabled.length > 0 && enabled.length < featureInputs.length;

            document.querySelectorAll('[data-limit-card]').forEach(function (card) {
                const key = card.dataset.limitCard;
                const feature = featureInputs.find(function (input) { return input.dataset.planFeature === key; });
                const enabledLimit = feature && feature.checked;
                const unlimited = card.querySelector('[data-unlimited-toggle]');
                const limit = card.querySelector('[data-limit-input]');
                if (unlimited) unlimited.disabled = !enabledLimit;
                if (limit) limit.disabled = !enabledLimit || (unlimited && unlimited.checked);
            });
        };

        featureMaster.addEventListener('change', function () {
            featureInputs.forEach(function (input) { input.checked = featureMaster.checked; });
            syncFeatureControls();
        });
        featureInputs.forEach(function (input) {
            input.addEventListener('change', syncFeatureControls);
        });
        document.querySelectorAll('[data-unlimited-toggle]').forEach(function (input) {
            input.addEventListener('change', syncFeatureControls);
        });
        syncFeatureControls();
    }

    const templateMaster = document.querySelector('[data-template-master]');
    const templateInputs = Array.from(document.querySelectorAll('[data-plan-template]'));
    if (templateMaster && templateInputs.length) {
        const syncTemplateControls = function () {
            const selectedCount = templateInputs.filter(function (input) { return input.checked; }).length;
            templateMaster.checked = selectedCount === templateInputs.length;
            templateMaster.indeterminate = selectedCount > 0 && selectedCount < templateInputs.length;
        };

        templateMaster.addEventListener('change', function () {
            templateInputs.forEach(function (input) { input.checked = templateMaster.checked; });
            syncTemplateControls();
        });
        templateInputs.forEach(function (input) {
            input.addEventListener('change', syncTemplateControls);
        });
        syncTemplateControls();
    }

    const planStatus = document.querySelector('.plan-status-toggle input[type="checkbox"]');
    if (planStatus) {
        planStatus.addEventListener('change', function () {
            const label = planStatus.parentElement.querySelector('[data-plan-status-label]');
            if (label) label.textContent = planStatus.checked ? 'Active' : 'Inactive';
        });
    }

    const themeButton = document.querySelector('.theme-toggle');
    if (themeButton) {
        const syncThemeIcon = function () {
            const dark = document.documentElement.dataset.theme === 'dark';
            themeButton.innerHTML = dark ? '<i class="fa-regular fa-sun"></i>' : '<i class="fa-regular fa-moon"></i>';
            themeButton.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
        };
        syncThemeIcon();
        themeButton.addEventListener('click', function () {
            const dark = document.documentElement.dataset.theme !== 'dark';
            if (dark) document.documentElement.dataset.theme = 'dark';
            else delete document.documentElement.dataset.theme;
            localStorage.setItem('admin-theme', dark ? 'dark' : 'light');
            syncThemeIcon();
        });
    }

    document.addEventListener('keydown', function (event) {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            const search = document.getElementById('quick-search');
            if (search) search.focus();
        }
        if (event.key === 'Escape') closeSidebar();
    });
});
