<template>
    <div class="mt-1">
        <input
            type="text"
            disabled
            :placeholder="question.placeholder || 'Jawaban singkat'"
            class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
        />
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
