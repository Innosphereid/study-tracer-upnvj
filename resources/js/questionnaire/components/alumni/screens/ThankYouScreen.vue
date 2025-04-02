<template>
    <div class="thank-you-screen bg-white shadow-md rounded-lg overflow-hidden">
        <div class="thank-you-content p-8 text-center">
            <div
                class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8 text-green-600"
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
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ title }}</h1>

            <div
                class="prose prose-indigo max-w-none mx-auto"
                v-html="description"
            ></div>

            <div v-if="showHomeButton" class="mt-8">
                <a
                    :href="homeUrl"
                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="mr-2 h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                        />
                    </svg>
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: "Terima Kasih!",
    },
    description: {
        type: String,
        default: "Jawaban Anda telah berhasil disimpan.",
    },
    showHomeButton: {
        type: Boolean,
        default: true,
    },
    homeUrl: {
        type: String,
        default: "/",
    },
});
</script>

<style scoped>
.thank-you-screen {
    max-width: 680px;
    margin: 0 auto;
    transition: all 0.3s ease;
}
</style>
