/**
 * Global Client-Side Form Validation Architecture
 * Provides real-time field validation, error indicators, and submit validation.
 */
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form[data-validate="true"], .needs-validation');

    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            input.addEventListener('input', () => validateField(input));
            input.addEventListener('blur', () => validateField(input));
        });

        form.addEventListener('submit', function (e) {
            let isValid = true;

            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();

                // Focus first invalid element
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }

                showToast('Please fix the errors in the form before submitting.', 'error');
            }
        });
    });

    function validateField(input) {
        if (input.type === 'hidden' || input.disabled) return true;

        const name = input.name;
        
        // Filter mobile numbers to numbers only and max 10 digits
        if (name === 'mobile' || input.dataset.type === 'mobile') {
            const cleanVal = input.value.replace(/[^0-9]/g, '').slice(0, 10);
            if (input.value !== cleanVal) {
                input.value = cleanVal;
            }
        }

        const val = input.value.trim();
        let valid = true;
        let errorMsg = '';

        // Required check
        if (input.hasAttribute('required') && !val) {
            valid = false;
            errorMsg = 'This field is required.';
        }

        // Checkbox terms
        if (input.type === 'checkbox' && input.hasAttribute('required') && !input.checked) {
            valid = false;
            errorMsg = 'You must accept to continue.';
        }

        // Email check (only if non-empty, since email is optional)
        if (valid && input.type === 'email' && val) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(val)) {
                valid = false;
                errorMsg = 'Please enter a valid email address.';
            }
        }

        // Mobile check (strictly 10 digits starting with 6-9)
        if (valid && (name === 'mobile' || input.dataset.type === 'mobile') && val) {
            if (val.length !== 10) {
                valid = false;
                errorMsg = 'Mobile number must be exactly 10 digits.';
            } else {
                const mobileRegex = /^[6-9]\d{9}$/;
                if (!mobileRegex.test(val)) {
                    valid = false;
                    errorMsg = 'Enter a valid 10-digit mobile number starting with 6-9.';
                }
            }
        }

        // Date of Birth check (no future dates)
        if (valid && (name === 'dob' || input.type === 'date') && val) {
            const selectedDate = new Date(val);
            const today = new Date();
            today.setHours(23, 59, 59, 999);
            if (selectedDate > today) {
                valid = false;
                errorMsg = 'Date of birth cannot be a future date.';
            }
        }

        // Username check (3-25 alphanumeric/underscore)
        if (valid && (name === 'username' || input.dataset.type === 'username') && val) {
            const userRegex = /^[a-zA-Z0-9_]{3,25}$/;
            if (!userRegex.test(val)) {
                valid = false;
                errorMsg = '3-25 chars (letters, numbers, underscore only).';
            }
        }

   // Password confirmation
if (valid && name === 'password_confirmation') {
    const pwd = input.form ? input.form.querySelector('input[name="password"]') : null;
    if (pwd && pwd.value !== val) {
        valid = false;
        errorMsg = 'Passwords do not match.';
    }
}

        // Password min length
        if (valid && name === 'password' && val) {
            if (val.length < 6) {
                valid = false;
                errorMsg = 'Password must be at least 6 characters.';
            }
        }

        // Numeric min/max
        if (valid && (input.type === 'number' || input.dataset.type === 'number') && val) {
            const num = parseFloat(val);
            if (input.hasAttribute('min') && num < parseFloat(input.getAttribute('min'))) {
                valid = false;
                errorMsg = `Minimum value is ${input.getAttribute('min')}.`;
            }
            if (input.hasAttribute('max') && num > parseFloat(input.getAttribute('max'))) {
                valid = false;
                errorMsg = `Maximum value is ${input.getAttribute('max')}.`;
            }
        }

        // Render UI feedback
        applyFieldFeedback(input, valid, errorMsg);
        return valid;
    }

    function applyFieldFeedback(input, valid, errorMsg) {
        let feedbackEl = input.parentElement.querySelector('.invalid-feedback-custom');
        
        if (!valid) {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            if (!feedbackEl) {
                feedbackEl = document.createElement('div');
                feedbackEl.className = 'invalid-feedback-custom';
                input.parentElement.appendChild(feedbackEl);
            }
            feedbackEl.textContent = errorMsg;
            feedbackEl.style.display = 'block';
        } else {
            input.classList.remove('is-invalid');
            if (input.value.trim().length > 0) {
                input.classList.add('is-valid');
            } else {
                input.classList.remove('is-valid');
            }
            if (feedbackEl) {
                feedbackEl.style.display = 'none';
            }
        }
    }

    // Global Toast Notification Helper
    window.showToast = function (message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 380px;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const bgColors = {
            success: 'linear-gradient(135deg, #059669, #10b981)',
            error: 'linear-gradient(135deg, #b91c1c, #ef4444)',
            warning: 'linear-gradient(135deg, #d97706, #f59e0b)',
            info: 'linear-gradient(135deg, #3730a3, #4f46e5)',
        };

        toast.style.cssText = `
            background: ${bgColors[type] || bgColors.info};
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.3s ease;
        `;
        toast.innerHTML = `<span>${message}</span><button style="background:none;border:none;color:#fff;margin-left:12px;cursor:pointer;font-size:1.1rem;">&times;</button>`;

        toast.querySelector('button').onclick = () => toast.remove();
        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.4s ease';
                setTimeout(() => toast.remove(), 400);
            }
        }, 4500);
    };
});
