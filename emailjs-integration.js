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
    // Initialize EmailJS - replace with your EmailJS user ID
    emailjs.init('UvK7p3qdpGga1NnGA');

    /**
     * Send verification email using EmailJS.
     * @param {string} to_email Recipient email address
     * @param {string} verification_link Verification URL
     * @returns {Promise} Promise resolving on success or rejecting on failure
     */
    function sendVerificationEmail(to_email, verification_link) {
    const templateParams = {
        to_email: to_email,
        verification_link: verification_link
    };
    return emailjs.send('service_5k4dd1o', 'template_sp012zu', templateParams);
}

    console.log('Email:', to_email);
    console.log('Verification Link:', verification_link);


    /**
     * Send password reset email using EmailJS.
     * @param {string} to_email Recipient email address
     * @param {string} reset_code Password reset code
     * @returns {Promise} Promise resolving on success or rejecting on failure
     */
    function sendPasswordResetEmail(to_email, reset_code) {
  const templateParams = {
    to_email:   to_email,
    reset_code: reset_code
  };
  return emailjs.send(
    'service_5k4dd1o',      // your Service ID
    'template_g01z3lg',     // your Template ID
    templateParams
  );
}


    // Expose functions to global scope
    window.sendVerificationEmail = sendVerificationEmail;
    window.sendPasswordResetEmail = sendPasswordResetEmail;
})();
