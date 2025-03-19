document.addEventListener('DOMContentLoaded', function() {
    const step1Form = document.getElementById('step1-form');
    const step2Form = document.getElementById('step2-form');
    const step3Form = document.getElementById('step3-form');
    const errorMessage = document.getElementById('recovery-error');
    const successMessage = document.getElementById('recovery-success');
    
    // Password input elements for step 3
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_new_password');
    const passwordStrength = document.getElementById('password-strength');
    const passwordMatch = document.getElementById('password-match');
    
    // Password requirements elements
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    
    // Step 1: Find account
    step1Form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const identifier = document.getElementById('identifier').value.trim();
        
        if (!identifier) {
            displayError('Please enter your username or email address.');
            return;
        }
        
        // Submit the request
        fetch('find_account.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `identifier=${encodeURIComponent(identifier)}&csrf_token=${encodeURIComponent(document.querySelector('input[name="csrf_token"]').value)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store user ID for the next step
                document.getElementById('user_id').value = data.user_id;
                
                // Load security questions
                const questionsContainer = document.getElementById('security-questions-container');
                questionsContainer.innerHTML = '';
                
                data.questions.forEach((question, index) => {
                    const questionDiv = document.createElement('div');
                    questionDiv.className = 'form-group';
                    questionDiv.innerHTML = `
                        <label>${question}</label>
                        <input type="text" name="security_answer_${index + 1}" required>
                    `;
                    questionsContainer.appendChild(questionDiv);
                });
                
                // Show step 2
                step1Form.style.display = 'none';
                step2Form.style.display = 'block';
            } else {
                displayError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayError('An error occurred. Please try again.');
        });
    });
    
    // Step 2: Verify security questions
    step2Form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(step2Form);
        
        fetch('verify_security_questions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store reset token for the next step
                document.getElementById('reset_token').value = data.reset_token;
                
                // Show step 3
                step2Form.style.display = 'none';
                step3Form.style.display = 'block';
            } else {
                displayError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayError('An error occurred. Please try again.');
        });
    });
    
    // Step 3: Reset password
    if (newPasswordInput) {
        // Check password strength in real-time
        newPasswordInput.addEventListener('input', function() {
            const password = newPasswordInput.value;
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
            if (newPasswordInput.value === confirmPasswordInput.value) {
                passwordMatch.textContent = 'Passwords match';
                passwordMatch.className = 'strong';
            } else {
                passwordMatch.textContent = 'Passwords do not match';
                passwordMatch.className = 'weak';
            }
        });
    }
    
    step3Form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const newPassword = newPasswordInput.value;
        const confirmNewPassword = confirmPasswordInput.value;
        
        // Validate password
        if (checkPasswordStrength(newPassword) === 'weak') {
            displayError('Your password is too weak. Please choose a stronger password.');
            return;
        }
        
        if (newPassword !== confirmNewPassword) {
            displayError('Passwords do not match.');
            return;
        }
        
        const formData = new FormData(step3Form);
        
        fetch('reset_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySuccess(data.message);
                
                // Redirect to login page after 3 seconds
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 3000);
            } else {
                displayError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayError('An error occurred. Please try again.');
        });
    });
    
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
