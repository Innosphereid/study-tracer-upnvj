document.addEventListener("DOMContentLoaded", function () {
    // Get form element
    const resetForm = document.getElementById("resetPasswordForm");
    const emailInput = document.getElementById("email");
    const submitBtn = document.getElementById("submitBtn");

    if (resetForm) {
        console.log(
            "Reset password form detected, initializing client-side validation"
        );

        // Add form submit listener
        resetForm.addEventListener("submit", function (e) {
            // Make sure email is valid first using browser validation
            if (emailInput && !emailInput.validity.valid) {
                console.log(
                    "Email validation failed: ",
                    emailInput.validationMessage
                );
                return; // Let browser handle basic validation
            }

            console.log("Email is valid, submitting form");

            // Additional validation if needed can be added here
            // For now, we're just letting the server handle the existence check
        });

        // Add input listener for email validation
        if (emailInput) {
            emailInput.addEventListener("input", function () {
                // Basic email format validation
                const isValidFormat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
                    emailInput.value
                );

                if (isValidFormat) {
                    console.log("Email format is valid");
                    emailInput.classList.remove("border-red-500");
                    emailInput.classList.add("border-green-500");
                } else {
                    console.log("Email format is invalid");
                    emailInput.classList.remove("border-green-500");
                    emailInput.classList.add("border-red-500");
                }
            });
        }
    }
});
