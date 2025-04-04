/** * @component AlumniQuestionnaireApp * @description Legacy component that now
delegates to the refactored wrapper * This component is kept for backward
compatibility and simply delegates * to the new AlumniQuestionnaireWrapper
component */
<template>
    <alumni-questionnaire-wrapper
        :questionnaire="questionnaire"
        :is-preview="isPreview"
    />
</template>

<script setup>
// Import the new wrapper component
import AlumniQuestionnaireWrapper from "./AlumniQuestionnaireWrapper.vue";

/**
 * Component props
 */
const props = defineProps({
    /**
     * Questionnaire data object
     */
    questionnaire: {
        type: Object,
        required: true,
    },

    /**
     * Whether the questionnaire is in preview mode
     */
    isPreview: {
        type: Boolean,
        default: false,
    },
});
</script>
