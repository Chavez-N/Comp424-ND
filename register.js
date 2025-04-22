document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthDisplay = document.getElementById('password-strength');
    const confirmFeedback = document.getElementById('confirm-password-feedback');

    function checkPasswordStrength(value) {
        let strength = 0;

        if (value.length >= 8) strength++;
        if (/[A-Z]/.test(value)) strength++;
        if (/[0-9]/.test(value)) strength++;
        if (/[^A-Za-z0-9]/.test(value)) strength++;

        let message = '';
        switch (strength) {
            case 0:
            case 1:
                message = 'Weak';
                strengthDisplay.style.color = 'red';
                break;
            case 2:
                message = 'Moderate';
                strengthDisplay.style.color = 'orange';
                break;
            case 3:
                message = 'Strong';
                strengthDisplay.style.color = 'green';
                break;
            case 4:
                message = 'Very Strong';
                strengthDisplay.style.color = 'darkgreen';
                break;
        }
        strengthDisplay.textContent = 'Password Strength: ' + message;
    }

    function checkPasswordMatch() {
        if (confirmPasswordInput.value === '') {
            confirmFeedback.textContent = '';
            return;
        }
        if (passwordInput.value === confirmPasswordInput.value) {
            confirmFeedback.textContent = 'Passwords match';
            confirmFeedback.style.color = 'green';
        } else {
            confirmFeedback.textContent = 'Passwords do not match';
            confirmFeedback.style.color = 'red';
        }
    }

    passwordInput.addEventListener('input', function () {
        checkPasswordStrength(passwordInput.value);
        checkPasswordMatch();
    });

    confirmPasswordInput.addEventListener('input', function () {
        checkPasswordMatch();
    });
});
