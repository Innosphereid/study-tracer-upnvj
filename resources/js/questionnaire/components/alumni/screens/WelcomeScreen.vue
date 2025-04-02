<template>
    <div class="welcome-screen bg-white shadow-md rounded-lg overflow-hidden">
        <div class="welcome-header bg-indigo-50 p-8 border-b border-indigo-100">
            <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ title }}</h1>
            <p v-if="subtitle" class="text-lg text-gray-600">{{ subtitle }}</p>
        </div>

        <div class="welcome-content p-8">
            <div
                class="prose prose-indigo max-w-none"
                v-html="description"
            ></div>

            <div class="mt-10">
                <button
                    @click="$emit('start')"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                >
                    {{ startButtonText }}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="ml-2 h-5 w-5"
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
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: "",
    },
    description: {
        type: String,
        default: "",
    },
    startButtonText: {
        type: String,
        default: "Mulai Kuesioner",
    },
});

defineEmits(["start"]);
</script>

<style scoped>
.welcome-screen {
    transition: all 0.3s ease;
}

.welcome-header {
    background-image: linear-gradient(
        to right,
        rgba(238, 242, 255, 0.8),
        rgba(224, 231, 255, 0.8)
    );
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
</style>
