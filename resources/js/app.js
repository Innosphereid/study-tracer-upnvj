import "./bootstrap";
import Alpine from "alpinejs";
import "./email-check";
import collapse from "@alpinejs/collapse";

// Make Alpine available globally
window.Alpine = Alpine;

Alpine.plugin(collapse);

// Initialize Alpine
Alpine.start();

// Add custom form animations
document.addEventListener("DOMContentLoaded", () => {
    // Animated form entrance
    const loginForm = document.querySelector("form");
    if (loginForm) {
        loginForm.classList.add("opacity-0", "translate-y-4");
        setTimeout(() => {
            loginForm.classList.add("transition-all", "duration-700");
            loginForm.classList.remove("opacity-0", "translate-y-4");
        }, 200);
    }

    // Input focus animations
    const inputs = document.querySelectorAll(
        'input[type="text"], input[type="password"], input[type="email"]'
    );
    inputs.forEach((input) => {
        input.addEventListener("focus", () => {
            input.parentElement.parentElement.classList.add("scale-[1.02]");
        });

        input.addEventListener("blur", () => {
            input.parentElement.parentElement.classList.remove("scale-[1.02]");
        });
    });

    // Form submission animation
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", (e) => {
            // This is handled by Alpine.js x-data="{ loading: false }"
            // Just an additional effect for the whole form
            form.classList.add("opacity-95", "scale-[0.99]");
        });
    }
});
