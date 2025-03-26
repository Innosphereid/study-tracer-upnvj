<template>
    <div class="mt-1">
        <div class="relative rounded-md shadow-sm">
            <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-gray-400"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
            <input
                type="text"
                disabled
                value="01/01/2023"
                :placeholder="question.format || 'DD/MM/YYYY'"
                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 pl-10 pr-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
            />
        </div>
        <div class="mt-1 flex items-center text-xs text-gray-500">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 mr-1 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            Format tanggal: {{ question.format || "DD/MM/YYYY" }}
            <span v-if="question.minDate || question.maxDate">
                (Batasan:
                <span v-if="question.minDate">dari {{ question.minDate }}</span>
                <span v-if="question.minDate && question.maxDate"> - </span>
                <span v-if="question.maxDate"
                    >sampai {{ question.maxDate }}</span
                >)
            </span>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});
</script>
