<template>
    <div class="checkbox-question">
        <!-- Info message about selection requirements -->
        <div
            v-if="showSelectionRequirements"
            class="mb-3 text-sm rounded-md p-3 flex items-center"
            :class="[
                isValidSelection
                    ? 'bg-indigo-50 text-indigo-700 border border-indigo-200'
                    : 'bg-yellow-50 text-yellow-700 border border-yellow-200',
            ]"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 mr-2 flex-shrink-0"
                :class="[
                    isValidSelection ? 'text-indigo-500' : 'text-yellow-500',
                ]"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd"
                />
            </svg>
            <span v-if="maxSelectionCount > 0">
                Pilih {{ minSelectionCount }} hingga
                {{ maxSelectionCount }} opsi
                <span class="font-semibold"
                    >({{ currentSelectionCount }} dipilih)</span
                >
            </span>
            <span v-else>
                Pilih minimum {{ minSelectionCount }} opsi
                <span class="font-semibold"
                    >({{ currentSelectionCount }} dipilih)</span
                >
            </span>
        </div>

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
            <p
                v-if="error || localValidationError"
                class="mt-2 text-sm text-red-600 flex items-center"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1 flex-shrink-0"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
                {{ error || localValidationError }}
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
    return props.modelValue?.values?.some(
        (v) => v === "other" || v === "Lainnya"
    );
});

// Computed property to check if "none" option is selected
const noneSelected = computed(() => {
    return props.modelValue?.values?.some(
        (v) => v === "none" || v === "Tidak Ada"
    );
});

// Computed property that combines all options, adds "None" and "Other" options if enabled
const normalizedOptions = computed(() => {
    let options = [...(props.question.options || [])];

    // Check if options already contain "None" and "Other"
    const hasNoneOption = options.some(
        (opt) =>
            opt.value === "none" ||
            opt.value === "Tidak Ada" ||
            opt.text === "Tidak Ada"
    );

    const hasOtherOption = options.some(
        (opt) =>
            opt.value === "other" ||
            opt.value === "Lainnya" ||
            opt.text === "Lainnya"
    );

    // Add "None" option if allowed and not already present
    if (props.question.allowNone && !hasNoneOption) {
        options.push({
            id: "none",
            text: "Tidak Ada",
            value: "none",
            isSpecial: true,
        });
    }

    // Add "Other" option if allowed and not already present
    if (props.question.allowOther && !hasOtherOption) {
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

// Helper to check if an option is the "Select All" option
const isSelectAllOption = (option) => {
    return (
        option.value === "Pilih Semua" ||
        option.isSelectAll === true ||
        option.value === "selectAll"
    );
};

// Local state for validation
const localValidationError = ref("");

// Extract min and max selection limits from question settings
const minSelectionCount = computed(() => {
    return parseInt(props.question.minSelected || 0);
});

const maxSelectionCount = computed(() => {
    return parseInt(props.question.maxSelected || 0);
});

// Current count of selected options
const currentSelectionCount = computed(() => {
    const values = props.modelValue?.values || [];
    // Don't count "Select All" option in the total
    return values.filter((v) => v !== "Pilih Semua" && v !== "selectAll")
        .length;
});

// Check if selection is valid
const isValidSelection = computed(() => {
    const count = currentSelectionCount.value;
    const min = minSelectionCount.value;
    const max = maxSelectionCount.value;

    if (min > 0 && count < min) {
        return false;
    }

    if (max > 0 && count > max) {
        return false;
    }

    return true;
});

// Should we show selection requirements?
const showSelectionRequirements = computed(() => {
    return minSelectionCount.value > 0 || maxSelectionCount.value > 0;
});

// Method to handle "Select All" option
const handleSelectAllOption = (option) => {
    if (isSelectAllOption(option)) {
        const isCurrentlySelected = isOptionSelected(option);
        // Only consider regular options (not special ones) for "Select All"
        const regularOptions = normalizedOptions.value.filter(
            (opt) =>
                !isSelectAllOption(opt) &&
                opt.value !== "none" &&
                opt.value !== "Tidak Ada" &&
                opt.value !== "other" &&
                opt.value !== "Lainnya"
        );

        // Check max selection
        if (
            !isCurrentlySelected &&
            maxSelectionCount.value > 0 &&
            regularOptions.length > maxSelectionCount.value
        ) {
            localValidationError.value = `"Pilih Semua" tidak dapat dipilih karena melebihi batas maksimal ${maxSelectionCount.value} opsi`;
            return true;
        }

        const newValues = [];

        // If "Select All" was just checked, select all regular options
        if (!isCurrentlySelected) {
            // Add all regular options to the selection
            regularOptions.forEach((opt) => {
                newValues.push(opt.value);
            });

            // Also select the "Select All" option itself
            newValues.push(option.value);

            // Clear validation error since we're definitely meeting min requirements
            if (regularOptions.length >= minSelectionCount.value) {
                localValidationError.value = "";
            }
        }

        // Create labels array for better tracking
        const labels = newValues.map((v) => {
            if (v === "other" || v === "Lainnya") return "Lainnya";
            if (v === "none" || v === "Tidak Ada") return "Tidak Ada";
            if (v === "Pilih Semua" || v === "selectAll") return "Pilih Semua";

            // Find the text for this value
            const opt = normalizedOptions.value.find((o) => o.value === v);
            return opt ? opt.text : v;
        });

        emit("update:modelValue", {
            values: newValues,
            otherText: props.modelValue?.otherText || "",
            labels,
        });

        return true;
    }

    return false;
};

// Method to toggle option selection - updated to handle "Select All"
const toggleOption = (option) => {
    // Handle "Select All" option specially
    if (handleSelectAllOption(option)) {
        return;
    }

    const value = option.value;
    const values = [...(props.modelValue?.values || [])];

    // Handle "none" option (both "none" and "Tidak Ada" values)
    if (value === "none" || value === "Tidak Ada") {
        if (values.includes("none") || values.includes("Tidak Ada")) {
            // Remove both possible values for "none"
            values.splice(
                values.indexOf(values.includes("none") ? "none" : "Tidak Ada"),
                1
            );
            // When unchecking "None", we need to validate min selection again
            if (
                minSelectionCount.value > 0 &&
                values.length < minSelectionCount.value
            ) {
                localValidationError.value = `Anda harus memilih minimal ${minSelectionCount.value} opsi`;
            } else {
                localValidationError.value = "";
            }
        } else {
            // Selecting "none" - clear all other selections
            values.length = 0;
            values.push(value); // Keep the original value format

            // "None" option is always valid, even if it doesn't meet min selection
            localValidationError.value = "";
        }

        emit("update:modelValue", {
            values,
            otherText: "", // Clear other text when "none" is selected
            labels: values.map((v) => {
                if (v === "none" || v === "Tidak Ada") return "Tidak Ada";
                return v;
            }),
        });
        return;
    }

    // If selecting an option but "none" is already selected, remove "none"
    if (values.includes("none") || values.includes("Tidak Ada")) {
        const noneIndex = values.findIndex(
            (v) => v === "none" || v === "Tidak Ada"
        );
        if (noneIndex > -1) {
            values.splice(noneIndex, 1);
        }
    }

    // If "Select All" is selected and we're unchecking a regular option,
    // we should also uncheck the "Select All" option
    const selectAllIndex = values.findIndex(
        (v) => v === "Pilih Semua" || v === "selectAll"
    );
    if (selectAllIndex > -1 && !isSelectAllOption(option)) {
        values.splice(selectAllIndex, 1);
    }

    // Check if we're trying to add an option
    const index = values.indexOf(value);
    const isAddingOption = index === -1;

    // Validate maximum selection when adding a new option
    if (isAddingOption && maxSelectionCount.value > 0) {
        // Don't count "Select All" when checking maximum
        const currentCount = values.filter(
            (v) => v !== "Pilih Semua" && v !== "selectAll"
        ).length;

        if (currentCount >= maxSelectionCount.value) {
            localValidationError.value = `Anda hanya dapat memilih maksimal ${maxSelectionCount.value} opsi`;
            return;
        }
    }

    // Toggle the option value
    if (index > -1) {
        values.splice(index, 1);

        // If this was the "other" option, clear the otherText
        if (value === "other" || value === "Lainnya") {
            otherText.value = "";
        }

        // When removing an option, check minimum requirement
        if (minSelectionCount.value > 0) {
            const regularCount = values.filter(
                (v) => v !== "Pilih Semua" && v !== "selectAll"
            ).length;

            if (regularCount < minSelectionCount.value) {
                localValidationError.value = `Anda harus memilih minimal ${minSelectionCount.value} opsi`;
            } else {
                localValidationError.value = "";
            }
        }
    } else {
        values.push(value);

        // When adding, clear error if we meet minimum requirement
        if (minSelectionCount.value > 0) {
            const regularCount = values.filter(
                (v) => v !== "Pilih Semua" && v !== "selectAll"
            ).length;

            if (regularCount >= minSelectionCount.value) {
                localValidationError.value = "";
            }
        }
    }

    // Create labels array for better tracking
    const labels = values.map((v) => {
        if (v === "other" || v === "Lainnya") return "Lainnya";
        if (v === "none" || v === "Tidak Ada") return "Tidak Ada";
        if (v === "Pilih Semua" || v === "selectAll") return "Pilih Semua";

        // Find the text for this value
        const opt = normalizedOptions.value.find((o) => o.value === v);
        return opt ? opt.text : v;
    });

    emit("update:modelValue", {
        values,
        otherText:
            value === "other" || value === "Lainnya"
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
            if (v === "other" || v === "Lainnya") return "Lainnya";
            if (v === "none" || v === "Tidak Ada") return "Tidak Ada";
            if (v === "Pilih Semua" || v === "selectAll") return "Pilih Semua";

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

// Watch for changes in selection to validate minimum requirements
watch(
    () => props.modelValue?.values,
    (newValues) => {
        if (newValues) {
            selectedValues.value = newValues;

            // Validate minimum selection
            if (
                minSelectionCount.value > 0 &&
                currentSelectionCount.value < minSelectionCount.value
            ) {
                localValidationError.value = `Anda harus memilih minimal ${minSelectionCount.value} opsi`;
            } else if (
                maxSelectionCount.value > 0 &&
                currentSelectionCount.value > maxSelectionCount.value
            ) {
                localValidationError.value = `Anda hanya dapat memilih maksimal ${maxSelectionCount.value} opsi`;
            } else {
                localValidationError.value = "";
            }
        }
    },
    { immediate: true }
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
