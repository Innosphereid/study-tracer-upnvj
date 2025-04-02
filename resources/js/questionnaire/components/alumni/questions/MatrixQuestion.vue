<template>
    <div class="matrix-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div
                v-if="!rows.length || !columns.length"
                class="p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-700 my-4"
            >
                <p class="font-medium">Data matriks tidak lengkap</p>
                <p class="text-sm mt-1">
                    Harap periksa konfigurasi pertanyaan untuk memastikan baris
                    dan kolom telah didefinisikan.
                </p>
                <div class="text-xs mt-2">
                    <p>Jumlah baris: {{ rows.length }}</p>
                    <p>Jumlah kolom: {{ columns.length }}</p>
                </div>
            </div>

            <div v-else class="mt-4 overflow-x-auto">
                <table
                    class="min-w-full border-collapse bg-white shadow-sm rounded-lg"
                >
                    <thead>
                        <tr class="bg-gray-50">
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
                                class="py-3 px-4 text-sm text-gray-800 border-b border-gray-200 font-medium"
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
import { defineProps, defineEmits, computed, ref, watch, onMounted } from "vue";
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
            rowLabels: {},
            columnLabels: {},
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
    rowLabels: { ...props.modelValue.rowLabels } || {},
    columnLabels: { ...props.modelValue.columnLabels } || {},
});

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        localValue.value = {
            responses: { ...newValue.responses } || {},
            checkboxResponses: { ...newValue.checkboxResponses } || {},
            rowLabels: { ...newValue.rowLabels } || {},
            columnLabels: { ...newValue.columnLabels } || {},
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
    // Cek beberapa kemungkinan lokasi konfigurasi tipe matriks
    return (
        props.question.matrixType ||
        settings.value.matrixType ||
        (props.question.type === "matrix-checkbox" ? "checkbox" : "radio")
    );
});

// Matrix rows
const rows = computed(() => {
    // Handle different data structures
    if (settings.value.rows) {
        // Normalize row format to have id, value, and label properties
        return settings.value.rows.map((row) => {
            // If row is already in the right format
            if (row.id && row.label) {
                return row;
            }

            // If row has text property but not label (from builder)
            if (row.text && !row.label) {
                return {
                    id:
                        row.id ||
                        `row-${Math.random().toString(36).substr(2, 9)}`,
                    value:
                        row.id ||
                        row.value ||
                        `row-${Math.random().toString(36).substr(2, 9)}`,
                    label: row.text,
                };
            }

            // Default case
            return {
                id:
                    row.id ||
                    row.value ||
                    `row-${Math.random().toString(36).substr(2, 9)}`,
                value:
                    row.id ||
                    row.value ||
                    `row-${Math.random().toString(36).substr(2, 9)}`,
                label: row.label || row.text || "Unnamed Row",
            };
        });
    }

    return [];
});

// Matrix columns
const columns = computed(() => {
    // Handle different data structures
    if (settings.value.columns) {
        // Normalize column format to have id, value, and label properties
        return settings.value.columns.map((column) => {
            // If column is already in the right format
            if (column.id && column.label) {
                return column;
            }

            // If column has text property but not label (from builder)
            if (column.text && !column.label) {
                return {
                    id:
                        column.id ||
                        `col-${Math.random().toString(36).substr(2, 9)}`,
                    value:
                        column.id ||
                        column.value ||
                        `col-${Math.random().toString(36).substr(2, 9)}`,
                    label: column.text,
                };
            }

            // Default case
            return {
                id:
                    column.id ||
                    column.value ||
                    `col-${Math.random().toString(36).substr(2, 9)}`,
                value:
                    column.id ||
                    column.value ||
                    `col-${Math.random().toString(36).substr(2, 9)}`,
                label: column.label || column.text || "Unnamed Column",
            };
        });
    }

    return [];
});

// Get value for a row in radio matrix
const getRadioValue = (rowId) => {
    return localValue.value.responses[rowId] || "";
};

// Update value for a row in radio matrix
const updateRadioValue = (rowId, value) => {
    const responses = { ...localValue.value.responses };
    responses[rowId] = value;

    // Add row and column labels for better data readability
    const rowLabels = { ...localValue.value.rowLabels };
    const columnLabels = { ...localValue.value.columnLabels };

    // Find the row and column labels
    const row = rows.value.find((r) => r.value === rowId);
    const column = columns.value.find((c) => c.value === value);

    if (row) {
        rowLabels[rowId] = row.label;
    }

    if (column) {
        columnLabels[value] = column.label;
    }

    localValue.value = {
        ...localValue.value,
        responses,
        rowLabels,
        columnLabels,
    };

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
    const rowLabels = { ...localValue.value.rowLabels };
    const columnLabels = { ...localValue.value.columnLabels };

    if (!checkboxResponses[rowId]) {
        checkboxResponses[rowId] = [];
    }

    const index = checkboxResponses[rowId].indexOf(columnId);
    if (index === -1) {
        checkboxResponses[rowId].push(columnId);
    } else {
        checkboxResponses[rowId].splice(index, 1);
    }

    // Add row and column labels for better data readability
    const row = rows.value.find((r) => r.value === rowId);
    const column = columns.value.find((c) => c.value === columnId);

    if (row) {
        rowLabels[rowId] = row.label;
    }

    if (column) {
        columnLabels[columnId] = column.label;
    }

    localValue.value = {
        ...localValue.value,
        checkboxResponses,
        rowLabels,
        columnLabels,
    };

    emit("update:modelValue", { ...localValue.value });
    validate();
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Pastikan ada rows dan columns sebelum validasi
    if (!rows.value.length || !columns.value.length) {
        return true; // Skip validation if no rows/columns defined
    }

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

// Add an onMounted hook to initialize labels if responses already exist
onMounted(() => {
    // Initialize row labels
    rows.value.forEach((row) => {
        if (row.value) {
            localValue.value.rowLabels[row.value] = row.label;
        }
    });

    // Initialize column labels
    columns.value.forEach((column) => {
        if (column.value) {
            localValue.value.columnLabels[column.value] = column.label;
        }
    });

    // If we have existing responses, make sure we have the labels
    if (Object.keys(localValue.value.responses).length > 0) {
        emit("update:modelValue", { ...localValue.value });
    }
});
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
