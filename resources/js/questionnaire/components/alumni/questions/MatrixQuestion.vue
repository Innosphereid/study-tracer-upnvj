<template>
    <div class="matrix-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr>
                            <th
                                class="py-3 px-4 text-left text-sm font-medium text-gray-700 border-b border-gray-200 bg-gray-50 w-1/3"
                            ></th>
                            <th
                                v-for="(column, columnIndex) in columns"
                                :key="columnIndex"
                                class="py-3 px-4 text-center text-sm font-medium text-gray-700 border-b border-gray-200 bg-gray-50"
                            >
                                {{ column.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(row, rowIndex) in rows"
                            :key="rowIndex"
                            :class="{ 'bg-gray-50': rowIndex % 2 === 0 }"
                        >
                            <td
                                class="py-3 px-4 text-sm text-gray-800 border-b border-gray-200"
                            >
                                {{ row.label }}
                            </td>
                            <td
                                v-for="(column, columnIndex) in columns"
                                :key="columnIndex"
                                class="py-3 px-4 text-center border-b border-gray-200"
                            >
                                <!-- Radio type -->
                                <input
                                    v-if="matrixType === 'radio'"
                                    type="radio"
                                    :id="`matrix-${question.id}-${rowIndex}-${columnIndex}`"
                                    :name="`matrix-${question.id}-${rowIndex}`"
                                    :value="column.value"
                                    :checked="
                                        getRadioValue(row.value) ===
                                        column.value
                                    "
                                    @change="
                                        updateRadioValue(
                                            row.value,
                                            column.value
                                        )
                                    "
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer"
                                />

                                <!-- Checkbox type -->
                                <input
                                    v-if="matrixType === 'checkbox'"
                                    type="checkbox"
                                    :id="`matrix-${question.id}-${rowIndex}-${columnIndex}`"
                                    :name="`matrix-${question.id}-${rowIndex}-${columnIndex}`"
                                    :value="column.value"
                                    :checked="
                                        isCheckboxChecked(
                                            row.value,
                                            column.value
                                        )
                                    "
                                    @change="
                                        toggleCheckboxValue(
                                            row.value,
                                            column.value
                                        )
                                    "
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 rounded border-gray-300 cursor-pointer"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
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
import { defineProps, defineEmits, computed, ref, watch } from "vue";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Object,
        default: () => ({
            responses: {}, // For radio type
            checkboxResponses: {}, // For checkbox type
        }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Local copy of the value for two-way binding
const localValue = ref({
    responses: { ...props.modelValue.responses } || {},
    checkboxResponses: { ...props.modelValue.checkboxResponses } || {},
});

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        localValue.value = {
            responses: { ...newValue.responses } || {},
            checkboxResponses: { ...newValue.checkboxResponses } || {},
        };
    },
    { deep: true }
);

// Parse settings
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

// Matrix type (radio or checkbox)
const matrixType = computed(() => {
    return props.question.matrixType || settings.value.matrixType || "radio";
});

// Matrix rows
const rows = computed(() => {
    return settings.value.rows || [];
});

// Matrix columns
const columns = computed(() => {
    return settings.value.columns || [];
});

// Get value for a row in radio matrix
const getRadioValue = (rowId) => {
    return localValue.value.responses[rowId] || "";
};

// Update value for a row in radio matrix
const updateRadioValue = (rowId, value) => {
    const responses = { ...localValue.value.responses };
    responses[rowId] = value;

    localValue.value.responses = responses;
    emit("update:modelValue", { ...localValue.value });
    validate();
};

// Check if a checkbox is checked
const isCheckboxChecked = (rowId, columnId) => {
    if (!localValue.value.checkboxResponses[rowId]) {
        return false;
    }

    return localValue.value.checkboxResponses[rowId].includes(columnId);
};

// Toggle checkbox value
const toggleCheckboxValue = (rowId, columnId) => {
    const checkboxResponses = { ...localValue.value.checkboxResponses };

    if (!checkboxResponses[rowId]) {
        checkboxResponses[rowId] = [];
    }

    const index = checkboxResponses[rowId].indexOf(columnId);
    if (index === -1) {
        checkboxResponses[rowId].push(columnId);
    } else {
        checkboxResponses[rowId].splice(index, 1);
    }

    localValue.value.checkboxResponses = checkboxResponses;
    emit("update:modelValue", { ...localValue.value });
    validate();
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation for radio matrix
    if (props.question.required && matrixType.value === "radio") {
        // Check if all rows have a selected value
        for (const row of rows.value) {
            if (!localValue.value.responses[row.value]) {
                isValid = false;
                errorMessage = "Semua baris harus diisi.";
                break;
            }
        }
    }

    // Required validation for checkbox matrix
    if (props.question.required && matrixType.value === "checkbox") {
        // Check if at least one checkbox is selected in each row
        for (const row of rows.value) {
            const rowResponses =
                localValue.value.checkboxResponses[row.value] || [];
            if (rowResponses.length === 0) {
                isValid = false;
                errorMessage =
                    "Setiap baris harus memiliki minimal satu pilihan.";
                break;
            }
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
