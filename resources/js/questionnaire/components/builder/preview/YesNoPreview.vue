<template>
    <div class="mt-2">
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
            <!-- Radio buttons in horizontal layout with visual distinction -->
            <div class="flex items-center justify-center space-x-8">
                <!-- Yes option with green accent -->
                <div
                    class="flex items-center bg-green-50 px-4 py-2 rounded-md border border-green-100"
                >
                    <input
                        type="radio"
                        disabled
                        class="h-4 w-4 text-green-600 cursor-not-allowed border-gray-300"
                        checked
                    />
                    <label class="ml-2 text-sm font-medium text-green-700">
                        {{ question.yesLabel || "Ya" }}
                    </label>
                </div>

                <!-- No option with red accent -->
                <div
                    class="flex items-center bg-red-50 px-4 py-2 rounded-md border border-red-100"
                >
                    <input
                        type="radio"
                        disabled
                        class="h-4 w-4 text-red-600 cursor-not-allowed border-gray-300"
                    />
                    <label class="ml-2 text-sm font-medium text-red-700">
                        {{ question.noLabel || "Tidak" }}
                    </label>
                </div>
            </div>

            <!-- Help text for binary choice -->
            <div
                class="mt-3 flex items-center justify-center text-xs text-gray-500"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
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
                <span>
                    Responden harus memilih salah satu dari dua opsi yang
                    disediakan
                </span>
            </div>
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
