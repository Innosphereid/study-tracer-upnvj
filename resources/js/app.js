import "./bootstrap";
import Alpine from "alpinejs";
import { enhanceFormValidation } from "./login";

// Make Alpine available globally
window.Alpine = Alpine;
Alpine.start();

// Initialize global UI enhancements
document.addEventListener("DOMContentLoaded", () => {
    // Apply fade-in animations sequentially
    const fadeElements = document.querySelectorAll(".animate-fade-in");
    fadeElements.forEach((el, index) => {
        el.style.animationDelay = `${0.1 + index * 0.1}s`;
    });

    // Add ripple effect to buttons
    const buttons = document.querySelectorAll(
        "button:not([disabled]), .btn-animation"
    );
    buttons.forEach((button) => {
        button.addEventListener("click", function (e) {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const ripple = document.createElement("span");
            ripple.className =
                "absolute bg-white bg-opacity-30 rounded-full pointer-events-none";
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            ripple.style.width = ripple.style.height = "0";

            button.appendChild(ripple);

            const size = Math.max(button.offsetWidth, button.offsetHeight);
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x - size / 2}px`;
            ripple.style.top = `${y - size / 2}px`;
            ripple.style.transform = "scale(1)";
            ripple.style.opacity = "0";
            ripple.style.transition = "all 0.6s ease-out";

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});
