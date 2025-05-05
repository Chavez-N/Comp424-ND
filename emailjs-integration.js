/**
 * EmailJS integration for sending verification and password reset emails.
 * 
 * Usage:
 * 1. Include EmailJS SDK in your HTML:
 *    <script src="https://cdn.emailjs.com/sdk/3.2/email.min.js"></script>
 * 2. Initialize EmailJS with your user ID:
 *    emailjs.init('YOUR_EMAILJS_USER_ID');
 * 3. Call sendVerificationEmail or sendPasswordResetEmail with appropriate parameters.
 */

(function() {
  // Initialize EmailJS - replace with your EmailJS Public Key
  emailjs.init('UvK7p3qdpGga1NnGA');

  /**
   * Send verification email using EmailJS.
   * @param {string} to_email Recipient email address
   * @param {string} verification_link Verification URL
   * @returns {Promise} Promise resolving on success or rejecting on failure
   */
  function sendVerificationEmail(to_email, reset_code) {
    // Debug: ensure parameters are received correctly
    console.log('Email (sendVerificationEmail):', to_email);
    console.log('reset_code:', reset_code);

    const templateParams = {
      to_email: to_email,
      reset_code: reset_code
    };
    return emailjs.send(
      'service_5k4dd1o', // your Service ID
      'template_sp012zu', // your Template ID
      templateParams
    );
  }

  /**
   * Send password reset email using EmailJS.
   * @param {string} to_email Recipient email address
   * @param {string} reset_code Password reset code
   * @returns {Promise} Promise resolving on success or rejecting on failure
   */
  function sendPasswordResetEmail(to_email, reset_code) {
    // Debug: ensure parameters are received correctly
    console.log('Email (sendPasswordResetEmail):', to_email);
    console.log('Reset Code:', reset_code);

    const templateParams = {
      to_email: to_email,
      reset_code: reset_code
    };
    return emailjs.send(
      'service_5k4dd1o', // your Service ID
      'template_g01z3lg', // your Template ID
      templateParams
    );
  }

  // Expose functions to global scope
  window.sendVerificationEmail = sendVerificationEmail;
  window.sendPasswordResetEmail = sendPasswordResetEmail;
})();
