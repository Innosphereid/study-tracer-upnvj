<template>
    <div class="question-label">
        <label class="block text-base font-medium text-gray-800">
            {{ question.text }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>

        <p v-if="helpText" class="mt-1.5 text-sm text-gray-600">
            {{ helpText }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    question: {
        type: Object,
        required: true,
    },
    required: {
        type: Boolean,
        default: false,
    },
    helpText: {
        type: String,
        default: "",
    },
});
</script>

<style scoped>
.question-label {
    margin-bottom: 0.75rem;
}
</style>
