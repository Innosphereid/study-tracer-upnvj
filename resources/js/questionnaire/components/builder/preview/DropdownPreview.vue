<template>
    <div class="mt-1">
        <select
            disabled
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 bg-gray-50 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md cursor-not-allowed"
        >
            <option>-- Pilih Opsi --</option>
            <option v-for="option in sortedOptions" :key="option.id">
                {{ option.text }}
            </option>
            <option v-if="question.allowNone">Tidak Ada</option>
            <option v-if="question.allowOther">Lainnya...</option>
        </select>
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
