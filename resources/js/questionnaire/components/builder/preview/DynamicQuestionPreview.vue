<template>
    <component
        v-if="previewComponent"
        :is="previewComponent"
        :question="normalizedQuestion"
        @add-options="(count) => $emit('add-options', count)"
    />
    <div
        v-else
        class="mt-1 py-2 px-3 text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-md"
    >
        [Preview untuk tipe pertanyaan {{ question.type }}]
    </div>
</template>

<script setup>
import { computed } from "vue";
import { getPreviewComponent } from "./QuestionPreviewRegistry";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["add-options"]);

// Process settings to ensure all question properties are available
const normalizedQuestion = computed(() => {
    // Start with a copy of the question
    const questionData = { ...props.question };

    // If settings exist in JSON format, parse it
    if (questionData.settings && typeof questionData.settings === "string") {
        try {
            questionData.settings = JSON.parse(questionData.settings);
            console.log("Parsed question settings:", questionData.settings);
        } catch (e) {
            console.error("Failed to parse question settings:", e);
            questionData.settings = {};
        }
    }

    // If settings is an object, copy all properties to the question root
    if (questionData.settings && typeof questionData.settings === "object") {
        // Apply settings to question root for direct access
        Object.entries(questionData.settings).forEach(([key, value]) => {
            // Don't override existing properties
            if (questionData[key] === undefined) {
                questionData[key] = value;
            }
        });

        // Ensure required fields have correct values
        if (
            questionData.settings.required !== undefined &&
            questionData.required === undefined
        ) {
            questionData.required = Boolean(questionData.settings.required);
        }

        if (
            questionData.settings.text !== undefined &&
            questionData.text === undefined
        ) {
            questionData.text = questionData.settings.text;
        }

        if (
            questionData.settings.helpText !== undefined &&
            questionData.helpText === undefined
        ) {
            questionData.helpText = questionData.settings.helpText;
        }

        // Handle type-specific fields
        if (questionData.type) {
            switch (questionData.type) {
                case "radio":
                case "checkbox":
                case "dropdown":
                    // Ensure options are available
                    if (
                        !questionData.options &&
                        questionData.settings.options
                    ) {
                        questionData.options = questionData.settings.options;
                    }
                    break;

                case "matrix":
                    // Ensure matrix properties are available
                    if (!questionData.rows && questionData.settings.rows) {
                        questionData.rows = questionData.settings.rows;
                    }

                    if (
                        !questionData.columns &&
                        questionData.settings.columns
                    ) {
                        questionData.columns = questionData.settings.columns;
                    }

                    if (
                        !questionData.matrixType &&
                        questionData.settings.matrixType
                    ) {
                        questionData.matrixType =
                            questionData.settings.matrixType;
                    }
                    break;

                case "rating":
                    // Ensure rating properties are available
                    if (
                        questionData.settings.maxRating !== undefined &&
                        questionData.maxRating === undefined
                    ) {
                        questionData.maxRating =
                            questionData.settings.maxRating;
                    }

                    if (
                        questionData.settings.showValues !== undefined &&
                        questionData.showValues === undefined
                    ) {
                        questionData.showValues =
                            questionData.settings.showValues;
                    }

                    if (
                        questionData.settings.icon !== undefined &&
                        questionData.icon === undefined
                    ) {
                        questionData.icon = questionData.settings.icon;
                    }
                    break;
            }
        }
    }

    return questionData;
});

// Mendapatkan komponen yang sesuai dengan tipe pertanyaan
const previewComponent = computed(() => {
    return getPreviewComponent(props.question.type);
});
</script>
