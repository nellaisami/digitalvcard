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

    document.querySelectorAll('form[data-client-validation]').forEach(function (form) {
        const mode = form.dataset.clientValidation;
        const fields = mode === 'login'
            ? ['email', 'password']
            : mode === 'otp' ? ['code']
                : mode === 'admin' ? ['name', 'email', 'password', 'password_confirmation']
                    : mode === 'account' ? ['name', 'username', 'email']
                        : mode === 'password-change' ? ['current_password', 'password', 'password_confirmation'] : [];
        const editing = form.dataset.validationEditing === 'true';

        function validateField(name) {
            const input = form.elements.namedItem(name);
            const message = form.querySelector('[data-validation-message="' + name + '"]');
            if (!input || !message) return true;

            const value = mode === 'password-change' ? input.value : input.value.trim();
            let error = '';

            if (mode === 'password-change' && name === 'current_password' && !value) {
                error = 'Please enter your current password.';
            } else if (mode === 'password-change' && name === 'password') {
                if (!value) error = 'Please enter a new password.';
                else if (value.length < 8 || !/[a-z]/.test(value) || !/[A-Z]/.test(value) ||
                    !/[0-9]/.test(value) || !/[^A-Za-z0-9]/.test(value)) {
                    error = 'Use at least 8 characters with an uppercase and lowercase letter, a number, and a special character.';
                }
            } else if (mode === 'password-change' && name === 'password_confirmation') {
                const password = form.elements.namedItem('password').value;
                if (!value) error = 'Please confirm your new password.';
                else if (value !== password) error = 'The passwords do not match.';
            } else if (mode === 'login' && name === 'email') {
                if (!value) error = 'Please enter your email address.';
                else if (input.validity.typeMismatch || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    error = 'Please enter a valid email address.';
                }
            } else if (mode === 'login' && name === 'password' && !input.value.trim()) {
                error = 'Please enter your password.';
            } else if (mode === 'account' && name === 'name') {
                if (!value) error = 'Please enter your full name.';
                else if (value.length > 255) error = 'Your full name must be 255 characters or fewer.';
            } else if (mode === 'account' && name === 'username') {
                if (!value) error = 'Please enter a username.';
                else if (value.length > 50) error = 'Your username must be 50 characters or fewer.';
            } else if (mode === 'account' && name === 'email') {
                if (!value) error = 'Please enter an email address.';
                else if (input.validity.typeMismatch || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    error = 'Please enter a valid email address.';
                } else if (value.length > 255) error = 'Your email address must be 255 characters or fewer.';
            } else if (mode === 'admin' && name === 'name' && !value) {
                error = 'Please enter the admin’s full name.';
            } else if (mode === 'admin' && name === 'email') {
                if (!value) error = 'Please enter an email address.';
                else if (input.validity.typeMismatch || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    error = 'Please enter a valid email address.';
                }
            } else if (mode === 'admin' && name === 'password') {
                if (!value && !editing) error = 'Please enter a password.';
                else if (value && (value.length < 8 || !/[0-9]/.test(value) || !/[^A-Za-z0-9]/.test(value))) {
                    error = 'Use at least 8 characters, including a number and a special character.';
                }
            } else if (mode === 'admin' && name === 'password_confirmation') {
                const password = form.elements.namedItem('password').value;
                if ((!editing || password) && !value) error = 'Please confirm the password.';
                else if (value !== password) error = 'The passwords do not match.';
            } else if (mode === 'otp' && name === 'code' && !/^\d{6}$/.test(value)) {
                error = 'Please enter the 6-digit verification code.';
            }

            message.textContent = error;
            input.setAttribute('aria-invalid', error ? 'true' : 'false');
            input.setAttribute('aria-describedby', message.id || (message.id = 'validation-' + name));
            input.classList.toggle('is-invalid', Boolean(error));
            const wrap = input.closest('.input-wrap');
            if (wrap) wrap.classList.toggle('is-invalid', Boolean(error));
            return !error;
        }

        fields.forEach(function (name) {
            const input = form.elements.namedItem(name);
            if (!input) return;
            input.addEventListener('blur', function () { validateField(name); });
            input.addEventListener('input', function () {
                if (input.getAttribute('aria-invalid') === 'true') validateField(name);
                if (mode === 'admin' && name === 'password' &&
                    form.elements.namedItem('password_confirmation').getAttribute('aria-invalid') === 'true') {
                    validateField('password_confirmation');
                }
                if (mode === 'password-change' && name === 'password') {
                    const confirmation = form.elements.namedItem('password_confirmation');
                    if (confirmation.value || confirmation.getAttribute('aria-invalid') === 'true') {
                        validateField('password_confirmation');
                    }
                }
            });
        });

        form.addEventListener('submit', function (event) {
            const valid = fields.map(validateField).every(Boolean);
            if (!valid) {
                event.preventDefault();
                const firstInvalid = form.querySelector('[aria-invalid="true"]');
                if (firstInvalid) firstInvalid.focus();
            }
        });
    });

    document.querySelectorAll('form[data-template-validation]').forEach(function (form) {
        const editing = form.dataset.validationEditing === 'true';
        const nameInput = form.elements.namedItem('name');
        const descriptionInput = form.elements.namedItem('description');
        const statusInput = form.elements.namedItem('status');
        const imageInput = form.elements.namedItem('image');
        const submitButton = form.querySelector('button[type="submit"]');

        function setError(input, name, error) {
            const message = form.querySelector('[data-validation-message="' + name + '"]');
            if (!input || !message) return;

            message.textContent = error;
            input.classList.toggle('is-invalid', Boolean(error));
            input.setAttribute('aria-invalid', error ? 'true' : 'false');
            input.setAttribute('aria-describedby', message.id);

            if (name === 'image') {
                const editor = input.closest('.template-image-editor');
                if (editor) editor.classList.toggle('is-invalid', Boolean(error));
            }
        }

        function validateField(input, name) {
            if (!input) return true;

            const value = input.value.trim();
            let error = '';

            if (name === 'name' && !value) {
                error = 'Please enter a template name.';
            } else if (name === 'name' && value.length > 255) {
                error = 'The template name must be 255 characters or fewer.';
            } else if (name === 'description' && input.value.length > 2000) {
                error = 'The description must be 2000 characters or fewer.';
            } else if (name === 'status' && !['active', 'inactive'].includes(value)) {
                error = 'Please select a valid status.';
            }

            setError(input, name, error);
            return !error;
        }

        function validateImageFile() {
            const file = imageInput.files && imageInput.files[0];
            let error = '';

            if (!file && !editing) {
                error = 'Please choose a template image.';
            } else if (file) {
                const extension = file.name.split('.').pop().toLowerCase();
                const validTypes = {
                    jpg: ['jpg', 'jpeg'],
                    jpeg: ['jpg', 'jpeg'],
                    png: ['png'],
                    webp: ['webp']
                };
                const validExtensions = validTypes[file.type.toLowerCase().replace('image/', '')];

                if (!validExtensions || !validExtensions.includes(extension)) {
                    error = 'Choose a JPG, JPEG, PNG, or WebP image.';
                } else if (file.size > 2 * 1024 * 1024) {
                    error = 'The image must be 2 MB or smaller.';
                }
            }

            setError(imageInput, 'image', error);
            return { file: file, error: error };
        }

        function validateImageDimensions(file) {
            return new Promise(function (resolve) {
                const image = new Image();
                const objectUrl = URL.createObjectURL(file);

                image.onload = function () {
                    URL.revokeObjectURL(objectUrl);
                    resolve(image.naturalWidth > 0 && image.naturalHeight > 0 &&
                        image.naturalWidth <= 8192 && image.naturalHeight <= 8192
                        ? ''
                        : 'Image dimensions must be between 1 × 1 and 8192 × 8192 pixels.');
                };
                image.onerror = function () {
                    URL.revokeObjectURL(objectUrl);
                    resolve('The selected file is not a valid image.');
                };
                image.src = objectUrl;
            });
        }

        [nameInput, descriptionInput].forEach(function (input) {
            if (!input) return;
            input.addEventListener('blur', function () { validateField(input, input.name); });
            input.addEventListener('input', function () {
                if (input.getAttribute('aria-invalid') === 'true') validateField(input, input.name);
            });
        });

        if (statusInput) {
            statusInput.addEventListener('change', function () { validateField(statusInput, 'status'); });
        }
        if (imageInput) {
            imageInput.addEventListener('change', async function () {
                const result = validateImageFile();
                if (result.file && !result.error) {
                    const error = await validateImageDimensions(result.file);
                    if (imageInput.files && imageInput.files[0] === result.file) {
                        setError(imageInput, 'image', error);
                    }
                }
            });
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (form.dataset.validating === 'true') return;
            form.dataset.validating = 'true';
            if (submitButton) submitButton.disabled = true;

            const validFields = [
                validateField(nameInput, 'name'),
                validateField(descriptionInput, 'description'),
                validateField(statusInput, 'status')
            ].every(Boolean);
            const imageResult = validateImageFile();
            let imageError = imageResult.error;

            if (!imageError && imageResult.file) {
                imageError = await validateImageDimensions(imageResult.file);
                setError(imageInput, 'image', imageError);
            }

            if (validFields && !imageError) {
                form.submit();
                return;
            }

            form.dataset.validating = 'false';
            if (submitButton) submitButton.disabled = false;
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid === imageInput) {
                const uploadButton = form.querySelector('.photo-upload-button');
                if (uploadButton) uploadButton.focus();
            } else if (firstInvalid) {
                firstInvalid.focus();
            }
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
