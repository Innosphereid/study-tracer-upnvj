<template>
    <div class="question-component">
        <fieldset>
            <legend class="text-sm font-medium text-gray-700">
                {{ question.text }}
                <span v-if="question.required" class="text-red-500">*</span>
            </legend>

            <p v-if="question.helpText" class="mt-1 text-sm text-gray-500">
                {{ question.helpText }}
            </p>

            <div class="mt-4 space-y-3">
                <!-- "Select All" option -->
                <div v-if="question.allowSelectAll" class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-select-all`"
                            type="checkbox"
                            v-model="selectAllChecked"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            @change="toggleSelectAll"
                            :disabled="isBuilder"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label
                            :for="`option-${question.id}-select-all`"
                            class="font-medium text-gray-700"
                        >
                            Pilih Semua
                        </label>
                    </div>
                </div>

                <!-- Regular options -->
                <div
                    v-for="option in sortedOptions"
                    :key="option.id"
                    class="flex items-start"
                >
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-${option.id}`"
                            :name="`question_${question.id}[]`"
                            type="checkbox"
                            :value="option.value"
                            v-model="selectedValues"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            @change="updateValue"
                            :disabled="
                                isBuilder ||
                                (noneSelected && question.allowNone)
                            "
                            :aria-describedby="`option-${question.id}-${option.id}-description`"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label
                            :for="`option-${question.id}-${option.id}`"
                            class="font-medium text-gray-700"
                        >
                            {{ option.text }}
                        </label>
                        <p
                            v-if="option.description"
                            :id="`option-${question.id}-${option.id}-description`"
                            class="text-gray-500"
                        >
                            {{ option.description }}
                        </p>
                    </div>
                </div>

                <!-- "None" option -->
                <div v-if="question.allowNone" class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-none`"
                            type="checkbox"
                            v-model="noneSelected"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            @change="toggleNone"
                            :disabled="isBuilder"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label
                            :for="`option-${question.id}-none`"
                            class="font-medium text-gray-700"
                        >
                            Tidak Ada
                        </label>
                    </div>
                </div>

                <!-- "Other" option -->
                <div v-if="question.allowOther" class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-other`"
                            :name="`question_${question.id}[]`"
                            type="checkbox"
                            value="other"
                            v-model="otherSelected"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            @change="updateOtherSelected"
                            :disabled="
                                isBuilder ||
                                (noneSelected && question.allowNone)
                            "
                        />
                    </div>
                    <div class="ml-3 text-sm flex items-center">
                        <label
                            :for="`option-${question.id}-other`"
                            class="font-medium text-gray-700 mr-2"
                        >
                            Lainnya:
                        </label>
                        <input
                            type="text"
                            v-model="otherText"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block sm:text-sm border-gray-300 rounded-md"
                            :disabled="
                                !otherSelected ||
                                isBuilder ||
                                (noneSelected && question.allowNone)
                            "
                            @input="updateOtherValue"
                            placeholder="Sebutkan..."
                        />
                    </div>
                </div>

                <!-- Min/Max selection info -->
                <div
                    v-if="hasSelectionLimits"
                    class="mt-2 text-xs text-gray-500"
                >
                    {{ selectionLimitsText }}
                </div>
            </div>
        </fieldset>

        <div v-if="error" class="mt-2 text-sm text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";

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
    isBuilder: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Internal state
const selectedValues = ref(props.modelValue.values || []);
const otherText = ref(props.modelValue.otherText || "");
const otherSelected = ref(selectedValues.value.includes("other"));
const noneSelected = ref(selectedValues.value.includes("none"));
const selectAllChecked = ref(false);

// Computed property to sort options based on optionsOrder
const sortedOptions = computed(() => {
    if (!props.question.options || !props.question.options.length) {
        return [];
    }

    // Create a copy to avoid mutating original data
    const options = [...props.question.options];

    // Apply sorting based on optionsOrder
    if (props.question.optionsOrder === "asc") {
        return options.sort((a, b) => a.text.localeCompare(b.text));
    } else if (props.question.optionsOrder === "desc") {
        return options.sort((a, b) => b.text.localeCompare(a.text));
    }

    // Default: return in original order
    return options;
});

// Check if selection limits are set
const hasSelectionLimits = computed(() => {
    return (
        props.question.minSelected > 0 ||
        (props.question.maxSelected > 0 &&
            props.question.maxSelected < props.question.options.length)
    );
});

// Text to display about selection limits
const selectionLimitsText = computed(() => {
    const min = props.question.minSelected;
    const max = props.question.maxSelected;

    if (min > 0 && max > 0) {
        return `Pilih ${min} hingga ${max} opsi`;
    } else if (min > 0) {
        return `Pilih minimal ${min} opsi`;
    } else if (max > 0) {
        return `Pilih maksimal ${max} opsi`;
    }

    return "";
});

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        selectedValues.value = newVal.values || [];
        otherText.value = newVal.otherText || "";
        otherSelected.value = selectedValues.value.includes("other");
        noneSelected.value = selectedValues.value.includes("none");
        updateSelectAllState();
    },
    { deep: true }
);

// Update "other" selection when selectedValues changes
watch(
    () => selectedValues.value,
    (newVal) => {
        otherSelected.value = newVal.includes("other");
        noneSelected.value = newVal.includes("none");
        updateSelectAllState();
    }
);

// Update the select all state based on selected values
const updateSelectAllState = () => {
    // Check if all normal options are selected
    const allOptionsSelected = sortedOptions.value.every((option) =>
        selectedValues.value.includes(option.value)
    );

    selectAllChecked.value =
        allOptionsSelected &&
        selectedValues.value.length >= sortedOptions.value.length;
};

// Toggle "Select All" functionality
const toggleSelectAll = () => {
    if (selectAllChecked.value) {
        // Select all options
        selectedValues.value = sortedOptions.value.map(
            (option) => option.value
        );

        // Add "other" if it was already selected
        if (otherSelected.value) {
            selectedValues.value.push("other");
        }

        // If "none" was selected, deselect it
        if (noneSelected.value) {
            noneSelected.value = false;
            selectedValues.value = selectedValues.value.filter(
                (val) => val !== "none"
            );
        }
    } else {
        // Deselect all regular options
        selectedValues.value = selectedValues.value.filter(
            (val) => val === "other" || val === "none"
        );
    }

    updateValue();
};

// Toggle "None" functionality
const toggleNone = () => {
    if (noneSelected.value) {
        // If "None" is selected, clear all other selections
        selectedValues.value = ["none"];
        otherSelected.value = false;
        otherText.value = "";
        selectAllChecked.value = false;
    } else {
        // If "None" is deselected, just remove it from the array
        selectedValues.value = selectedValues.value.filter(
            (val) => val !== "none"
        );
    }

    updateValue();
};

const updateValue = () => {
    emit("update:modelValue", {
        values: selectedValues.value,
        otherText: otherSelected.value ? otherText.value : "",
    });

    validate();
};

const updateOtherSelected = () => {
    // Sync selectedValues with otherSelected
    if (otherSelected.value && !selectedValues.value.includes("other")) {
        // If "none" is selected, deselect it
        if (noneSelected.value) {
            noneSelected.value = false;
            selectedValues.value = selectedValues.value.filter(
                (val) => val !== "none"
            );
        }

        selectedValues.value.push("other");
    } else if (!otherSelected.value && selectedValues.value.includes("other")) {
        selectedValues.value = selectedValues.value.filter(
            (val) => val !== "other"
        );
    }

    updateValue();
};

const updateOtherValue = () => {
    if (otherSelected.value) {
        emit("update:modelValue", {
            values: selectedValues.value,
            otherText: otherText.value,
        });

        validate();
    }
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && selectedValues.value.length === 0) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    // Min selected validation
    if (
        props.question.minSelected > 0 &&
        selectedValues.value.length < props.question.minSelected &&
        !noneSelected.value // Skip if "None" is selected
    ) {
        isValid = false;
        errorMessage = `Pilih minimal ${props.question.minSelected} opsi.`;
    }

    // Max selected validation
    if (
        props.question.maxSelected > 0 &&
        selectedValues.value.length > props.question.maxSelected &&
        !noneSelected.value // Skip if "None" is selected
    ) {
        isValid = false;
        errorMessage = `Pilih maksimal ${props.question.maxSelected} opsi.`;
    }

    // Other validation
    if (
        otherSelected.value &&
        !otherText.value.trim() &&
        props.question.required
    ) {
        isValid = false;
        errorMessage = 'Harap isi opsi "Lainnya".';
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>
