<template>
    <div class="mt-2 space-y-2">
        <div
            v-for="option in sortedOptions"
            :key="option.id"
            class="flex items-center"
        >
            <div class="flex items-center h-5">
                <input
                    type="radio"
                    disabled
                    class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
                />
            </div>
            <div class="ml-3 text-sm">
                <label class="font-medium text-gray-700">{{
                    option.text
                }}</label>
            </div>
        </div>
        <!-- None option -->
        <div v-if="question.allowNone" class="flex items-center">
            <div class="flex items-center h-5">
                <input
                    type="radio"
                    disabled
                    class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
                />
            </div>
            <div class="ml-3 text-sm">
                <label class="font-medium text-gray-700">Tidak Ada</label>
            </div>
        </div>
        <!-- Other option -->
        <div v-if="question.allowOther" class="flex items-center">
            <div class="flex items-center h-5">
                <input
                    type="radio"
                    disabled
                    class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
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
import { defineProps, computed } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

// Computed property to sort options based on optionsOrder
const sortedOptions = computed(() => {
    if (!props.question.options || !props.question.options.length) {
        return [];
    }

    // Create a copy to avoid mutating original data
    const options = [...props.question.options];

    // Apply sorting based on optionsOrder
    if (props.question.optionsOrder === "asc") {
        return options.sort((a, b) => a.text.localeCompare(b.text));
    } else if (props.question.optionsOrder === "desc") {
        return options.sort((a, b) => b.text.localeCompare(a.text));
    }

    // Default: return in original order
    return options;
});
</script>
