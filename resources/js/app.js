import "./bootstrap";
import Alpine from "alpinejs";
import "./email-check";
import collapse from "@alpinejs/collapse";

// Register collapse plugin
Alpine.plugin(collapse);

// Tambahkan konfigurasi untuk dropdown
document.addEventListener("alpine:init", () => {
    Alpine.data("dropdown", () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        copyShareLink(link) {
            navigator.clipboard
                .writeText(link)
                .then(() => {
                    // Panggil fungsi global untuk menampilkan toast
                    const linkDisplay =
                        link.length > 35 ? link.substring(0, 30) + "..." : link;
                    window.showGlobalToast(
                        "Berhasil!",
                        `Link berhasil disalin:\n${linkDisplay}`
                    );
                    // Tutup dropdown setelah delay singkat
                    setTimeout(() => {
                        this.open = false;
                    }, 300);
                })
                .catch((err) => {
                    console.error("Gagal menyalin: ", err);
                    window.showGlobalToast(
                        "Gagal!",
                        "Tidak dapat menyalin link. Coba copy manual.",
                        3000
                    );
                });
        },
    }));
});

// Make Alpine available globally
window.Alpine = Alpine;

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

    // Add animation for form labels
    const formInputs = document.querySelectorAll(
        ".form-input, .form-textarea, .form-select"
    );

    formInputs.forEach((input) => {
        input.addEventListener("focus", () => {
            const label = input.previousElementSibling;
            if (label && label.classList.contains("form-label")) {
                label.classList.add("form-label-focus");
            }
        });

        input.addEventListener("blur", () => {
            if (input.value === "") {
                const label = input.previousElementSibling;
                if (label && label.classList.contains("form-label")) {
                    label.classList.remove("form-label-focus");
                }
            }
        });

        // Check on load if input has value
        if (input.value !== "") {
            const label = input.previousElementSibling;
            if (label && label.classList.contains("form-label")) {
                label.classList.add("form-label-focus");
            }
        }
    });
});
