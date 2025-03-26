<template>
    <div class="mt-1">
        <select
            disabled
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 bg-gray-50 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md cursor-not-allowed"
        >
            <option>-- Pilih Opsi --</option>
            <option v-for="option in question.options" :key="option.id">
                {{ option.text }}
            </option>
            <option v-if="question.allowOther">Lainnya...</option>
        </select>
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
