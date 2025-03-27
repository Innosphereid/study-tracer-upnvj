<template>
    <div class="checkbox-question">
        <div class="space-y-2">
            <div
                v-for="(option, index) in normalizedOptions"
                :key="option.id || index"
                class="relative flex items-start transition-transform hover:translate-x-1"
            >
                <div class="flex items-center h-6">
                    <input
                        type="checkbox"
                        :id="`option-${option.id || index}-${question.id}`"
                        :value="option.value"
                        :checked="isOptionSelected(option)"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer transition-all duration-200"
                        @change="toggleOption(option)"
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
                        placeholder="Masukkan pilihan lainnya..."
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
        default: () => ({ values: [], otherText: "" }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// Log props for debugging
onMounted(() => {
    console.log("CheckboxQuestion mounted with props:", {
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
const selectedValues = ref(props.modelValue?.values || []);

// Computed property to check if "other" option is selected
const otherSelected = computed(() => {
    return props.modelValue?.values?.includes("other");
});

// Computed property to check if "none" option is selected
const noneSelected = computed(() => {
    return props.modelValue?.values?.includes("none");
});

// Computed property that combines all options, adds "None" and "Other" options if enabled
const normalizedOptions = computed(() => {
    let options = [...(props.question.options || [])];

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

    // Sort options if needed
    if (props.question.optionsOrder === "desc") {
        // Sort regular options (not special ones)
        const regularOptions = options.filter((opt) => !opt.isSpecial);
        const specialOptions = options.filter((opt) => opt.isSpecial);

        regularOptions.sort((a, b) => b.text.localeCompare(a.text));

        options = [...regularOptions, ...specialOptions];
    }

    return options;
});

// Helper to check if an option is selected
const isOptionSelected = (option) => {
    return props.modelValue?.values?.includes(option.value);
};

// Method to toggle option selection
const toggleOption = (option) => {
    const value = option.value;
    const values = [...(props.modelValue?.values || [])];

    // Special handling for "none" option
    if (value === "none") {
        if (values.includes("none")) {
            // Unselecting "none" - just remove it
            values.splice(values.indexOf("none"), 1);
        } else {
            // Selecting "none" - clear all other selections
            values.length = 0;
            values.push("none");
        }

        emit("update:modelValue", {
            values,
            otherText: "", // Clear other text when "none" is selected
            labels: values.map((v) => (v === "none" ? "Tidak Ada" : v)),
        });
        return;
    }

    // If selecting an option but "none" is already selected, remove "none"
    if (values.includes("none")) {
        values.splice(values.indexOf("none"), 1);
    }

    // Toggle the option value
    const index = values.indexOf(value);
    if (index > -1) {
        values.splice(index, 1);

        // If this was the "other" option, clear the otherText
        if (value === "other") {
            otherText.value = "";
        }
    } else {
        values.push(value);
    }

    // Create labels array for better tracking
    const labels = values.map((v) => {
        if (v === "other") return "Lainnya";
        if (v === "none") return "Tidak Ada";

        // Find the text for this value
        const opt = normalizedOptions.value.find((o) => o.value === v);
        return opt ? opt.text : v;
    });

    emit("update:modelValue", {
        values,
        otherText:
            value === "other"
                ? otherText.value
                : props.modelValue?.otherText || "",
        labels,
    });
};

// Method to update the "other" text
const updateOtherText = () => {
    emit("update:modelValue", {
        values: props.modelValue?.values || [],
        otherText: otherText.value,
        labels: props.modelValue?.values?.map((v) => {
            if (v === "other") return "Lainnya";
            if (v === "none") return "Tidak Ada";

            // Find the text for this value
            const opt = normalizedOptions.value.find((o) => o.value === v);
            return opt ? opt.text : v;
        }),
    });
};

// Watch for external changes to model value
watch(
    () => props.modelValue?.otherText,
    (newValue) => {
        if (newValue !== undefined) {
            otherText.value = newValue;
        }
    }
);

watch(
    () => props.modelValue?.values,
    (newValues) => {
        if (newValues) {
            selectedValues.value = newValues;
        }
    }
);
</script>

<style scoped>
.checkbox-question input[type="checkbox"] {
    position: relative;
    transition: all 0.2s ease;
}

.checkbox-question input[type="checkbox"]:checked {
    transform: scale(1.2);
}

.checkbox-question input[type="checkbox"]:checked + div label {
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
