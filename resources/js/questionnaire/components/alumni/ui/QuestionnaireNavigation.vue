<template>
    <div
        class="questionnaire-navigation flex justify-between items-center py-4"
    >
        <button
            v-if="showPrevious"
            @click="$emit('previous')"
            class="btn-previous px-5 py-2.5 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 flex items-center space-x-2"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                />
            </svg>
            <span>Sebelumnya</span>
        </button>
        <div v-else class="invisible"></div>

        <div v-if="showNext || showSubmit" class="btn-container">
            <button
                v-if="showNext"
                @click="$emit('next')"
                class="btn-next px-5 py-2.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200 flex items-center space-x-2"
            >
                <span>Selanjutnya</span>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </button>

            <button
                v-if="showSubmit"
                @click="$emit('submit')"
                :disabled="isSubmitting"
                class="btn-submit px-5 py-2.5 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2"
            >
                <template v-if="isSubmitting">
                    <svg
                        class="animate-spin h-5 w-5 mr-2"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    <span>Mengirim...</span>
                </template>
                <template v-else>
                    <span>Selesai</span>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
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
                </template>
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    showPrevious: {
        type: Boolean,
        default: false,
    },
    showNext: {
        type: Boolean,
        default: false,
    },
    showSubmit: {
        type: Boolean,
        default: false,
    },
    isSubmitting: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["previous", "next", "submit"]);
</script>

<style scoped>
.questionnaire-navigation {
    margin-top: 2rem;
}

button {
    font-weight: 500;
    min-width: 130px;
}

button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
