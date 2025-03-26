<template>
    <component
        :is="questionTypeComponent"
        v-if="questionTypeComponent"
        :question="question"
        @update:question="$emit('update:question', $event)"
        @duplicate-question="$emit('duplicate-question')"
        @delete-question="$emit('delete-question')"
    />
    <div v-else class="py-6 text-center text-gray-500">
        Loading question settings...
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from "vue";
import RadioQuestionSettings from "./RadioQuestionSettings.vue";
import CheckboxQuestionSettings from "./CheckboxQuestionSettings.vue";
import DropdownQuestionSettings from "./DropdownQuestionSettings.vue";
import RatingQuestionSettings from "./RatingQuestionSettings.vue";
import LikertQuestionSettings from "./LikertQuestionSettings.vue";
import FileUploadQuestionSettings from "./FileUploadQuestionSettings.vue";
import QuestionSettingsPanel from "./QuestionSettingsPanel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "update:question",
    "duplicate-question",
    "delete-question",
]);

// Map question types to their respective components
const questionTypeComponent = computed(() => {
    // Implement specialized components
    if (props.question.type === "radio") {
        return RadioQuestionSettings;
    } else if (props.question.type === "checkbox") {
        return CheckboxQuestionSettings;
    } else if (props.question.type === "dropdown") {
        return DropdownQuestionSettings;
    } else if (props.question.type === "rating") {
        return RatingQuestionSettings;
    } else if (props.question.type === "likert") {
        return LikertQuestionSettings;
    } else if (props.question.type === "file-upload") {
        return FileUploadQuestionSettings;
    }

    // Default to basic settings panel for other question types
    // Later we'll add more specialized components for other question types
    return QuestionSettingsPanel;
});
</script>
