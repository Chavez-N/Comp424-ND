document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const strengthDisplay = document.getElementById('password-strength');

    passwordInput.addEventListener('input', function () {
        const value = passwordInput.value;
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
    });
});
