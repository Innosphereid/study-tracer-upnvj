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
                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                    />
                </svg>
            </div>
            <input
                type="tel"
                disabled
                :placeholder="question.placeholder || '+62 8xx-xxxx-xxxx'"
                value="+62 812-3456-7890"
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
            Format: kode negara + nomor telepon (mis. +62 812-3456-7890)
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
