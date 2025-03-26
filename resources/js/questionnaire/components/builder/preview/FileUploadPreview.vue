<template>
    <div class="mt-1">
        <div
            class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50"
        >
            <div class="space-y-1 text-center">
                <svg
                    class="mx-auto h-8 w-8 text-gray-400"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                    />
                </svg>
                <p class="text-xs text-gray-500">
                    Upload file
                    <span
                        v-if="
                            question.allowedTypes &&
                            question.allowedTypes.length > 0
                        "
                    >
                        ({{ question.allowedTypes.join(", ") }})
                    </span>
                </p>
                <p v-if="question.maxFileSize" class="text-xs text-gray-500">
                    Ukuran maksimal: {{ formatFileSize(question.maxFileSize) }}
                </p>
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

// Format file size in bytes to human-readable format
const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};
</script>
