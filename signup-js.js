document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signup-form');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrength = document.getElementById('password-strength');
    const passwordMatch = document.getElementById('password-match');
    const errorMessage = document.getElementById('signup-error');
    const successMessage = document.getElementById('signup-success');
    
    // Password requirements elements
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    
    // Check password strength in real-time
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const strength = checkPasswordStrength(password);
        
        // Update password requirements display
        reqLength.className = password.length >= 10 ? 'met' : '';
        reqUppercase.className = /[A-Z]/.test(password) ? 'met' : '';
        reqLowercase.className = /[a-z]/.test(password) ? 'met' : '';
        reqNumber.className = /[0-9]/.test(password) ? 'met' : '';
        reqSpecial.className = /[^A-Za-z0-9]/.test(password) ? 'met' : '';
        
        // Update strength meter
        if (strength === 'weak') {
            passwordStrength.innerHTML = 'Password strength: <span class="weak">Weak</span>';
        } else if (strength === 'medium') {
            passwordStrength.innerHTML = 'Password strength: <span class="medium">Medium</span>';
        } else {
            passwordStrength.innerHTML = 'Password strength: <span class="strong">Strong</span>';
        }
    });
    
    // Check password match in real-time
    confirmPasswordInput.addEventListener('input', function() {
        if (passwordInput.value === confirmPasswordInput.value) {
            passwordMatch.textContent = 'Passwords match';
            passwordMatch.className = 'strong';
        } else {
            passwordMatch.textContent = 'Passwords do not match';
            passwordMatch.className = 'weak';
        }
    });
    
    // Form submission handler
    signupForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Reset messages
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Collect form data
        const formData = new FormData(signupForm);
        
        // Send data via AJAX
        fetch('process_signup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySuccess(data.message);
                signupForm.reset();
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 3000); // Redirect after 3 seconds
            } else {
                displayError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayError('An error occurred. Please try again.');
        });
    });
    
    // Check for duplicate username/email
    document.getElementById('username').addEventListener('blur', checkUsername);
    document.getElementById('email').addEventListener('blur', checkEmail);
    
    function checkUsername() {
        const username = document.getElementById('username').value.trim();
        if (username) {
            fetch('check_username.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `username=${encodeURIComponent(username)}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.available) {
                    displayError('Username is already taken. Please choose another.');
                }
            });
        }
    }
    
    function checkEmail() {
        const email = document.getElementById('email').value.trim();
        if (email) {
            fetch('check_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.available) {
                    displayError('Email is already registered. Please use another or recover your account.');
                }
            });
        }
    }
    
    // Validate the entire form before submission
    function validateForm() {
        // Get form values
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const email = document.getElementById('email').value.trim();
        const birthdate = document.getElementById('birthdate').value;
        const username = document.getElementById('username').value.trim();
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const securityQuestion1 = document.getElementById('security_question_1').value;
        const securityAnswer1 = document.getElementById('security_answer_1').value.trim();
        const securityQuestion2 = document.getElementById('security_question_2').value;
        const securityAnswer2 = document.getElementById('security_answer_2').value.trim();
        
        // Basic validation
        if (!firstName || !lastName || !email || !birthdate || !username || !password || !confirmPassword) {
            displayError('Please fill in all required fields.');
            return false;
        }
        
        // Email validation
        if (!isValidEmail(email)) {
            displayError('Please enter a valid email address.');
            return false;
        }
        
        // Password strength validation
        if (checkPasswordStrength(password) === 'weak') {
            displayError('Your password is too weak. Please choose a stronger password.');
            return false;
        }
        
        // Password match validation
        if (password !== confirmPassword) {
            displayError('Passwords do not match.');
            return false;
        }
        
        // Security questions validation
        if (!securityQuestion1 || !securityAnswer1 || !securityQuestion2 || !securityAnswer2) {
            displayError('Please select and answer both security questions.');
            return false;
        }
        
        if (securityQuestion1 === securityQuestion2) {
            displayError('Please select two different security questions.');
            return false;
        }
        
        // CAPTCHA validation will be done on the server
        
        return true;
    }
    
    // Password strength checker
    function checkPasswordStrength(password) {
        // Check for minimum requirements
        const hasLength = password.length >= 10;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        
        // Calculate score
        let score = 0;
        if (hasLength) score++;
        if (hasUpperCase) score++;
        if (hasLowerCase) score++;
        if (hasNumbers) score++;
        if (hasSpecialChars) score++;
        
        // Determine strength
        if (score < 3) {
            return 'weak';
        } else if (score < 5) {
            return 'medium';
        } else {
            return 'strong';
        }
    }
    
    // Email validation helper
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Display error message
    function displayError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        successMessage.style.display = 'none';
    }
    
    // Display success message
    function displaySuccess(message) {
        successMessage.textContent = message;
        successMessage.style.display = 'block';
        errorMessage.style.display = 'none';
    }
});
