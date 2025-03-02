//Sleep said this works
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('login-error');
    
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        // Basic validation
        if (username === '' || password === '') {
            displayError('Please enter both username and password.');
            return;
        }
        
        // XSS prevention - sanitize input
        const sanitizedUsername = sanitizeInput(username);
        
        // Submit the form with AJAX to prevent form hijacking
        fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest' // Helps prevent CSRF
            },
            body: `username=${encodeURIComponent(sanitizedUsername)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                displayError(data.message || 'Login failed. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayError('An error occurred during login. Please try again.');
        });
    });
    
    function displayError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }
    
    function sanitizeInput(input) {
        // Basic sanitization for XSS prevention
        return input.replace(/[<>]/g, '');
    }
});