// Login page specific JavaScript

// Form validation enhancement
const enhanceFormValidation = () => {
    const form = document.querySelector("form");
    const inputs = form?.querySelectorAll(
        'input:not([type="checkbox"]):not([type="hidden"])'
    );

    if (!inputs) return;

    inputs.forEach((input) => {
        // Add real-time validation feedback
        input.addEventListener("blur", () => {
            if (input.required && !input.value.trim()) {
                input.classList.add("border-red-300", "bg-red-50");

                // Add error message if it doesn't exist
                let errorMsg =
                    input.parentElement.querySelector(".text-red-600");
                if (!errorMsg) {
                    errorMsg = document.createElement("div");
                    errorMsg.className =
                        "mt-1 text-sm text-red-600 animate-fade-in";
                    errorMsg.textContent = "This field is required";
                    input.parentElement.appendChild(errorMsg);
                }
            } else {
                input.classList.remove("border-red-300", "bg-red-50");

                // Remove error message if it exists
                const errorMsg =
                    input.parentElement.querySelector(".text-red-600");
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });

        // Clear error styling on input
        input.addEventListener("input", () => {
            input.classList.remove("border-red-300", "bg-red-50");

            // Remove error message if it exists
            const errorMsg = input.parentElement.querySelector(".text-red-600");
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    });

    // Add form submission handling for animation
    form?.addEventListener("submit", (e) => {
        const requiredInputs = form.querySelectorAll("input[required]");
        let isValid = true;

        requiredInputs.forEach((input) => {
            if (!input.value.trim()) {
                input.classList.add("border-red-300", "bg-red-50");
                isValid = false;

                // Add error message if it doesn't exist
                let errorMsg =
                    input.parentElement.querySelector(".text-red-600");
                if (!errorMsg) {
                    errorMsg = document.createElement("div");
                    errorMsg.className =
                        "mt-1 text-sm text-red-600 animate-fade-in";
                    errorMsg.textContent = "This field is required";
                    input.parentElement.appendChild(errorMsg);
                }
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });
};

// Add subtle background animation
const initBackgroundAnimation = () => {
    const bgPattern = document.querySelector(".bg-repeat");
    if (!bgPattern) return;

    let position = 0;

    setInterval(() => {
        position += 0.5;
        bgPattern.style.backgroundPosition = `${position}px ${position}px`;
    }, 50);
};

// Add logo animation
const animateLogo = () => {
    const logo = document.querySelector('img[alt="UPN Veteran Jakarta Logo"]');
    if (!logo) return;

    // Add subtle hover effect
    logo.addEventListener("mouseenter", () => {
        logo.style.transform = "scale(1.05)";
        logo.style.transition = "transform 0.3s ease";
    });

    logo.addEventListener("mouseleave", () => {
        logo.style.transform = "scale(1)";
    });

    // Add initial animation
    setTimeout(() => {
        logo.classList.add("animate-pulse-subtle");
    }, 1000);
};

// Initialize all login page functions
document.addEventListener("DOMContentLoaded", () => {
    enhanceFormValidation();
    initBackgroundAnimation();
    animateLogo();

    // Add input field focus effect
    const inputs = document.querySelectorAll(
        'input:not([type="checkbox"]):not([type="hidden"])'
    );
    inputs.forEach((input) => {
        input.addEventListener("focus", () => {
            input.parentElement.classList.add("animate-shimmer");
        });

        input.addEventListener("blur", () => {
            input.parentElement.classList.remove("animate-shimmer");
        });
    });
});

// Export any functions that might be needed elsewhere
export { enhanceFormValidation };
