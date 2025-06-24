<template>
    <div class="radio-question">
        <div class="space-y-2">
            <!-- Regular options -->
            <div
                v-for="(option, index) in normalizedOptions"
                :key="option.id || index"
                class="relative flex items-start transition-transform transform hover:translate-x-1"
            >
                <div class="flex items-center h-6">
                    <input
                        type="radio"
                        :id="`option-${option.id || index}-${question.id}`"
                        :value="option.value"
                        :name="`question-${question.id}`"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer transition-all duration-200"
                        :checked="isOptionSelected(option)"
                        @change="selectOption(option)"
                    />
                </div>
                <div class="ml-3 text-sm flex-1">
                    <label
                        :for="`option-${option.id || index}-${question.id}`"
                        class="font-medium text-gray-700 cursor-pointer hover:text-indigo-600 transition-colors duration-200"
                    >
                        {{ option.text }}
                    </label>

                    <!-- Input field for "Other" option -->
                    <input
                        v-if="option.value === 'other' && otherSelected"
                        v-model="otherText"
                        type="text"
                        class="mt-2 w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                        placeholder="Masukkan jawaban lainnya..."
                        @input="updateOtherText"
                    />
                </div>
            </div>
        </div>

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";

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

const emit = defineEmits(["update:modelValue"]);

// Log props for debugging
onMounted(() => {
    console.log("RadioQuestion mounted with props:", {
        id: props.question.id,
        options: props.question.options,
        allowOther: props.question.allowOther,
        allowNone: props.question.allowNone,
        optionsOrder: props.question.optionsOrder,
        modelValue: props.modelValue,
    });
});

// Local state for "other" option
const otherText = ref(props.modelValue?.otherText || "");

// Computed property to check if "other" option is selected
const otherSelected = computed(() => {
    return props.modelValue?.value === "other";
});

// Computed property to check if "none" option is selected
const noneSelected = computed(() => {
    return props.modelValue?.value === "none";
});

// Computed property that combines all options, adds "None" and "Other" options if enabled
const normalizedOptions = computed(() => {
    let options = [...(props.question.options || [])];

    // Normalize option values to match text if they use option_X format
    options = options.map((option) => {
        // Create a copy of the option to avoid mutating props
        const normalizedOption = { ...option };

        // If value follows the option_X pattern, replace it with the text
        if (
            normalizedOption.value &&
            normalizedOption.value.match(/^option_\d+$/)
        ) {
            normalizedOption.value = normalizedOption.text;
        }

        return normalizedOption;
    });

    // Sort options if needed
    if (props.question.optionsOrder === "desc") {
        // Sort regular options (not special ones)
        options.sort((a, b) => b.text.localeCompare(a.text));
    }

    // Add "None" option if allowed
    if (props.question.allowNone) {
        options.push({
            id: "none",
            text: "Tidak Ada",
            value: "none",
            isSpecial: true,
        });
    }

    // Add "Other" option if allowed
    if (props.question.allowOther) {
        options.push({
            id: "other",
            text: "Lainnya",
            value: "other",
            isSpecial: true,
        });
    }

    console.log("Normalized options:", options);
    return options;
});

// Helper to check if an option is selected
const isOptionSelected = (option) => {
    const optionValue = option.value;
    return props.modelValue?.value === optionValue;
};

// Method to handle option selection
const selectOption = (option) => {
    const optionValue = option.value;

    // Special handling for "other" option
    if (optionValue === "other") {
        emit("update:modelValue", {
            value: "other",
            otherText: otherText.value,
            label: "Lainnya",
        });
        return;
    }

    // Special handling for "none" option
    if (optionValue === "none") {
        emit("update:modelValue", {
            value: "none",
            otherText: "",
            label: "Tidak Ada",
        });
        return;
    }

    // Regular options
    emit("update:modelValue", {
        value: optionValue,
        otherText: "",
        label: option.text, // Include the label/text for better tracking
    });
};

// Method to update the "other" text (used in the template when "other" is selected)
const updateOtherText = () => {
    emit("update:modelValue", {
        value: "other",
        otherText: otherText.value,
        label: "Lainnya",
    });
};

// Watch for external changes to model value (for example, when a default value is set)
watch(
    () => props.modelValue?.otherText,
    (newValue) => {
        if (newValue !== undefined) {
            otherText.value = newValue;
        }
    }
);
</script>

<style scoped>
.radio-question input[type="radio"] {
    position: relative;
    transition: all 0.2s ease;
}

.radio-question input[type="radio"]:checked {
    transform: scale(1.2);
}

.radio-question input[type="radio"]:checked + div label {
    color: #4f46e5;
    font-weight: 600;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
