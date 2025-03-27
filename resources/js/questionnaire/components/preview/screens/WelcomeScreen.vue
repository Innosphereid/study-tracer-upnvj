<template>
    <div class="welcome-screen p-8">
        <div class="max-w-2xl mx-auto text-center">
            <!-- Logo/Icon -->
            <div
                class="mb-6 inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8 text-indigo-600"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>

            <!-- Title with animation -->
            <h1 class="text-3xl font-bold text-gray-900 welcome-title mb-4">
                {{
                    questionnaire.welcomeScreen?.title ||
                    "Selamat Datang di Kuesioner"
                }}
            </h1>

            <!-- Description with animation -->
            <div class="mt-4 text-lg text-gray-600 welcome-description">
                {{
                    questionnaire.welcomeScreen?.description ||
                    "Terima kasih telah berpartisipasi dalam kuesioner ini. Kami menghargai waktu dan masukan Anda."
                }}
            </div>

            <!-- Start button with animation -->
            <div class="mt-8 welcome-cta">
                <button
                    type="button"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:scale-105"
                    @click="$emit('start')"
                >
                    Mulai
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="ml-2 h-5 w-5 animate-pulse"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6"
                        />
                    </svg>
                </button>
            </div>

            <!-- Additional Info/Instructions -->
            <div class="mt-10 text-sm text-gray-500 welcome-info">
                <div class="inline-flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1 text-gray-400"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <span
                        >Mode Pratinjau - Tidak ada data yang akan
                        disimpan</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    questionnaire: {
        type: Object,
        required: true,
    },
});

defineEmits(["start"]);
</script>

<style scoped>
/* Staggered animation for welcome elements */
.welcome-title,
.welcome-description,
.welcome-cta,
.welcome-info {
    animation-duration: 0.6s;
    animation-fill-mode: both;
    animation-name: fadeInUp;
}

.welcome-title {
    animation-delay: 0.1s;
}

.welcome-description {
    animation-delay: 0.3s;
}

.welcome-cta {
    animation-delay: 0.5s;
}

.welcome-info {
    animation-delay: 0.7s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }

    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}
</style>
