<template>
    <div class="thank-you-screen p-8">
        <div class="max-w-2xl mx-auto text-center">
            <!-- Success Icon with Animation -->
            <div
                class="mb-8 inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 success-icon"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-12 w-12 text-green-600"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>

            <!-- Title with Animation -->
            <h1 class="text-3xl font-bold text-gray-900 thank-you-title">
                {{ questionnaire.thankYouScreen?.title || "Terima Kasih!" }}
            </h1>

            <!-- Message with Animation -->
            <div class="mt-4 text-lg text-gray-600 thank-you-message">
                {{
                    questionnaire.thankYouScreen?.description ||
                    "Terima kasih telah menyelesaikan kuesioner ini. Respon Anda telah berhasil dicatat."
                }}
            </div>

            <!-- Preview Mode Notification -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg thank-you-info">
                <p class="text-blue-700 text-sm">
                    Ini adalah mode pratinjau. Dalam mode aktual, data respon
                    akan disimpan ke database.
                </p>
            </div>

            <!-- Action Buttons -->
            <div
                class="mt-10 flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4 thank-you-actions"
            >
                <button
                    type="button"
                    class="sm:order-2 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:scale-105"
                    @click="handlePublish"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="-ml-1 mr-2 h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                    Terbitkan Kuesioner
                </button>

                <button
                    type="button"
                    class="sm:order-1 inline-flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                    @click="$emit('restart')"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="-ml-1 mr-2 h-5 w-5 text-gray-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                    Ulangi Pratinjau
                </button>
            </div>

            <!-- Confetti Animation on Mount -->
            <div
                ref="confettiContainer"
                class="confetti-container fixed inset-0 pointer-events-none z-50"
            ></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const props = defineProps({
    questionnaire: {
        type: Object,
        required: true,
    },
});

defineEmits(["restart"]);

// Reference for confetti container
const confettiContainer = ref(null);

// Method to handle publishing
const handlePublish = () => {
    // In a real scenario, this would redirect to publish flow
    if (props.questionnaire.id) {
        window.location.href = `/kuesioner/${props.questionnaire.id}/edit?publish=true`;
    } else {
        // Handle case where there's no questionnaire ID (sample preview)
        alert(
            "Ini adalah pratinjau sampel. Dalam aplikasi sebenarnya, ini akan mengarahkan ke halaman penerbitan."
        );
    }
};

// Launch confetti animation on mount
onMounted(() => {
    if (typeof window !== "undefined") {
        // Simple confetti effect
        launchConfetti();
    }
});

// Simple confetti function
const launchConfetti = () => {
    if (!confettiContainer.value) return;

    const colors = ["#FF6D6D", "#4CAF50", "#2196F3", "#FFEB3B", "#9C27B0"];
    const confettiCount = 100;

    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement("div");
        confetti.className = "confetti-piece";

        const size = Math.random() * 10 + 5;
        const color = colors[Math.floor(Math.random() * colors.length)];

        confetti.style.width = `${size}px`;
        confetti.style.height = `${size}px`;
        confetti.style.backgroundColor = color;
        confetti.style.position = "absolute";
        confetti.style.top = "-10px";
        confetti.style.left = `${Math.random() * 100}%`;
        confetti.style.opacity = "1";
        confetti.style.transform = `rotate(${Math.random() * 360}deg)`;

        // Animation
        confetti.style.animation = `confetti-fall ${
            Math.random() * 3 + 2
        }s linear forwards`;

        confettiContainer.value.appendChild(confetti);

        // Remove after animation
        setTimeout(() => {
            if (confetti.parentNode === confettiContainer.value) {
                confettiContainer.value.removeChild(confetti);
            }
        }, 5000);
    }
};
</script>

<style scoped>
/* Staggered animation for thank you elements */
.success-icon,
.thank-you-title,
.thank-you-message,
.thank-you-info,
.thank-you-actions {
    animation-duration: 0.6s;
    animation-fill-mode: both;
    animation-name: scaleIn;
}

.success-icon {
    animation-delay: 0.1s;
}

.thank-you-title {
    animation-delay: 0.3s;
}

.thank-you-message {
    animation-delay: 0.5s;
}

.thank-you-info {
    animation-delay: 0.7s;
}

.thank-you-actions {
    animation-delay: 0.9s;
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Confetti animation */
@keyframes confetti-fall {
    0% {
        top: -10px;
        transform: translateX(0) rotate(0deg);
        opacity: 1;
    }
    100% {
        top: 100vh;
        transform: translateX(calc(100px - 200px * var(--random, 0.5)))
            rotate(720deg);
        opacity: 0;
    }
}

/* Pulse animation for success icon */
.success-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(72, 187, 120, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(72, 187, 120, 0);
    }
}
</style>
