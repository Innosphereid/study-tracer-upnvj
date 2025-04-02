<template>
    <div class="checkbox-question">
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
                            type="checkbox"
                            :id="`option-${question.id}-${index}`"
                            :name="`question-${question.id}`"
                            :value="option.value"
                            :checked="isOptionSelected(option.value)"
                            @change="toggleOption(option.value)"
                            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
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
                            type="checkbox"
                            :id="`option-${question.id}-other`"
                            :name="`question-${question.id}-other`"
                            value="other"
                            :checked="isOtherSelected"
                            @change="toggleOtherOption"
                            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
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

            <!-- Maximum selection warning if applicable -->
            <div v-if="hasMaxSelections" class="mt-2 text-sm text-gray-500">
                {{ selectedCount }} dari {{ maxSelections }} pilihan maksimum
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
        default: () => ({ values: [], otherText: "" }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Local copy of the value to handle the two-way binding
const localValue = ref({
    values: [...(props.modelValue.values || [])],
    otherText: props.modelValue.otherText || "",
});

// Watch for external changes to the modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        localValue.value = {
            values: [...(newValue.values || [])],
            otherText: newValue.otherText || "",
        };
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

// Get settings
const settings = computed(() => {
    if (!props.question.settings) return {};

    if (typeof props.question.settings === "string") {
        try {
            return JSON.parse(props.question.settings);
        } catch (e) {
            console.error("Failed to parse settings:", e);
            return {};
        }
    }

    return props.question.settings;
});

// Check if the question has "other" option
const hasOtherOption = computed(() => {
    return settings.value.allowOther || false;
});

// Label for the "other" option
const otherOptionLabel = computed(() => {
    return settings.value.otherLabel || "Lainnya";
});

// Placeholder for the "other" text input
const otherPlaceholder = computed(() => {
    return settings.value.otherPlaceholder || "Ketik di sini...";
});

// Check if the "other" option is selected
const isOtherSelected = computed(() => {
    return localValue.value.values.includes("other");
});

// Check if the question has maximum selections
const hasMaxSelections = computed(() => {
    return settings.value.maxSelections > 0;
});

// Get maximum selections
const maxSelections = computed(() => {
    return settings.value.maxSelections || Infinity;
});

// Count of selected options
const selectedCount = computed(() => {
    return localValue.value.values.length;
});

// Check if an option is selected
const isOptionSelected = (value) => {
    return localValue.value.values.includes(value);
};

// Toggle an option
const toggleOption = (value) => {
    const values = [...localValue.value.values];
    const index = values.indexOf(value);

    if (index === -1) {
        // If we're at max selections, return without adding
        if (hasMaxSelections.value && values.length >= maxSelections.value) {
            return;
        }

        values.push(value);
    } else {
        values.splice(index, 1);
    }

    localValue.value.values = values;
    emit("update:modelValue", { ...localValue.value });
    validate();
};

// Toggle the "other" option
const toggleOtherOption = () => {
    const values = [...localValue.value.values];
    const index = values.indexOf("other");

    if (index === -1) {
        // If we're at max selections, return without adding
        if (hasMaxSelections.value && values.length >= maxSelections.value) {
            return;
        }

        values.push("other");
    } else {
        values.splice(index, 1);
        // Clear other text when unchecking "other"
        localValue.value.otherText = "";
    }

    localValue.value.values = values;
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
    if (props.question.required && localValue.value.values.length === 0) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    } else if (
        localValue.value.values.includes("other") &&
        !localValue.value.otherText.trim()
    ) {
        isValid = false;
        errorMessage = 'Harap isi kolom "Lainnya".';
    }

    // Max selections validation
    if (
        hasMaxSelections.value &&
        localValue.value.values.length > maxSelections.value
    ) {
        isValid = false;
        errorMessage = `Maksimal ${maxSelections.value} opsi yang dapat dipilih.`;
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
