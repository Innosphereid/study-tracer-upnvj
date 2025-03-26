<template>
    <div class="mt-2">
        <div
            class="flex items-center justify-between text-xs text-gray-500 mb-2"
        >
            <span v-if="question.labels && question.labels[question.minRating]">
                {{ question.labels[question.minRating] }}
            </span>
            <span v-else>{{ question.minRating || 1 }}</span>

            <span
                v-if="
                    question.labels && question.labels[question.maxRatingValue]
                "
            >
                {{ question.labels[question.maxRatingValue] }}
            </span>
            <span v-else>{{ question.maxRatingValue || 5 }}</span>
        </div>

        <div class="flex items-center justify-center space-x-1">
            <template v-for="n in question.maxRating || 5" :key="n">
                <span
                    :class="
                        n <= Math.ceil((question.maxRating || 5) / 2)
                            ? 'text-yellow-400'
                            : 'text-gray-300'
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                </span>
            </template>
        </div>

        <div
            v-if="question.stepValue && question.stepValue !== 1"
            class="mt-2 text-center text-xs text-gray-500"
        >
            <span>Langkah: {{ question.stepValue }}</span>
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
