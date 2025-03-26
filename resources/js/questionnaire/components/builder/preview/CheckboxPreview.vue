<template>
    <div class="mt-2 space-y-2">
        <div
            v-for="option in question.options"
            :key="option.id"
            class="flex items-center"
        >
            <div class="flex items-center h-5">
                <input
                    type="checkbox"
                    disabled
                    class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 rounded"
                />
            </div>
            <div class="ml-3 text-sm">
                <label class="font-medium text-gray-700">{{
                    option.text
                }}</label>
            </div>
        </div>
        <div v-if="question.allowOther" class="flex items-center">
            <div class="flex items-center h-5">
                <input
                    type="checkbox"
                    disabled
                    class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 rounded"
                />
            </div>
            <div class="ml-3 text-sm flex items-center">
                <label class="font-medium text-gray-700 mr-2">Lainnya:</label>
                <input
                    type="text"
                    disabled
                    class="shadow-sm border border-gray-300 rounded-md py-1 px-2 bg-gray-50 text-gray-500 cursor-not-allowed text-xs"
                />
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
