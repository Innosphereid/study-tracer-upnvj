<template>
    <div class="radio-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3 space-y-3">
                <div
                    v-for="(option, index) in parsedOptions"
                    :key="index"
                    class="flex items-start"
                >
                    <div class="flex items-center h-5">
                        <input
                            type="radio"
                            :id="`option-${question.id}-${index}`"
                            :name="`question-${question.id}`"
                            :value="option.value"
                            :checked="localValue.value === option.value"
                            @change="updateValue(option.value)"
                            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer"
                        />
                    </div>
                    <div class="ml-3">
                        <label
                            :for="`option-${question.id}-${index}`"
                            class="text-base text-gray-700 cursor-pointer"
                            v-html="option.label"
                        ></label>
                    </div>
                </div>

                <!-- "Other" option if enabled -->
                <div v-if="hasOtherOption" class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            type="radio"
                            :id="`option-${question.id}-other`"
                            :name="`question-${question.id}`"
                            value="other"
                            :checked="isOtherSelected"
                            @change="updateValue('other')"
                            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer"
                        />
                    </div>
                    <div class="ml-3 flex-grow">
                        <label
                            :for="`option-${question.id}-other`"
                            class="text-base text-gray-700 cursor-pointer"
                        >
                            {{ otherOptionLabel }}
                        </label>
                        <input
                            v-if="isOtherSelected"
                            type="text"
                            v-model="localValue.otherText"
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :placeholder="
                                otherPlaceholder || 'Ketik di sini...'
                            "
                            @input="updateOtherText"
                            @blur="validate"
                        />
                    </div>
                </div>
            </div>

            <transition name="fade">
                <p v-if="error" class="mt-3 text-sm text-red-600">
                    {{ error }}
                </p>
            </transition>
        </question-container>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed, watch } from "vue";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Object,
        default: () => ({ value: "", otherText: "" }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Local copy of the value to handle the two-way binding
const localValue = ref({ ...props.modelValue });

// Watch for external changes to the modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        localValue.value = { ...newValue };
    },
    { deep: true }
);

// Parse options from the question
const parsedOptions = computed(() => {
    if (!props.question.options) return [];

    // If options is a string, try to parse it as JSON
    if (typeof props.question.options === "string") {
        try {
            return JSON.parse(props.question.options);
        } catch (e) {
            console.error("Failed to parse options:", e);
            return [];
        }
    }

    return props.question.options;
});

// Check if the question has "other" option
const hasOtherOption = computed(() => {
    if (!props.question.settings) return false;

    const settings =
        typeof props.question.settings === "string"
            ? JSON.parse(props.question.settings)
            : props.question.settings;

    return settings.allowOther || false;
});

// Label for the "other" option
const otherOptionLabel = computed(() => {
    if (!props.question.settings) return "Lainnya";

    const settings =
        typeof props.question.settings === "string"
            ? JSON.parse(props.question.settings)
            : props.question.settings;

    return settings.otherLabel || "Lainnya";
});

// Placeholder for the "other" text input
const otherPlaceholder = computed(() => {
    if (!props.question.settings) return "Ketik di sini...";

    const settings =
        typeof props.question.settings === "string"
            ? JSON.parse(props.question.settings)
            : props.question.settings;

    return settings.otherPlaceholder || "Ketik di sini...";
});

// Check if the "other" option is selected
const isOtherSelected = computed(() => {
    return localValue.value.value === "other";
});

// Update the radio selection
const updateValue = (value) => {
    localValue.value.value = value;

    // Clear other text if "other" is not selected
    if (value !== "other") {
        localValue.value.otherText = "";
    }

    emit("update:modelValue", { ...localValue.value });
    validate();
};

// Update the "other" text
const updateOtherText = () => {
    emit("update:modelValue", { ...localValue.value });
    validate();
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required) {
        if (!localValue.value.value) {
            isValid = false;
            errorMessage = "Pertanyaan ini wajib dijawab.";
        } else if (
            localValue.value.value === "other" &&
            !localValue.value.otherText.trim()
        ) {
            isValid = false;
            errorMessage = 'Harap isi kolom "Lainnya".';
        }
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
