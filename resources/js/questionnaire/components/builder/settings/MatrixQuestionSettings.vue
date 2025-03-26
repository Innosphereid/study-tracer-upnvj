<template>
    <div>
        <QuestionSettingsPanel
            :question="question"
            @update:question="updateQuestionBase"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        >
            <template #type-specific-settings>
                <!-- Matrix question specific settings -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Pengaturan Matriks Pilihan
                    </h3>

                    <!-- Matrix Type Selection -->
                    <div class="mb-4">
                        <label
                            for="matrix-type"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Tipe Matriks
                        </label>
                        <select
                            id="matrix-type"
                            v-model="matrixType"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        >
                            <option value="radio">
                                Pilihan Tunggal (Radio)
                            </option>
                            <option value="checkbox">
                                Pilihan Ganda (Checkbox)
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Pilih apakah responden dapat memilih satu atau
                            beberapa opsi per baris
                        </p>
                    </div>

                    <!-- Rows Management -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Baris
                            </label>
                            <button
                                type="button"
                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                @click="addRow"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Tambah Baris
                            </button>
                        </div>

                        <!-- Rows list with input fields -->
                        <div
                            v-for="(row, index) in rows"
                            :key="row.id"
                            class="flex items-center mt-2"
                        >
                            <div class="flex-grow">
                                <div class="flex items-center">
                                    <span class="text-xs text-gray-500 w-7"
                                        >{{ index + 1 }}.</span
                                    >
                                    <input
                                        type="text"
                                        v-model="row.text"
                                        class="flex-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Teks baris"
                                        @change="updateQuestion"
                                    />
                                </div>
                            </div>
                            <button
                                type="button"
                                class="ml-2 p-1 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                                @click="removeRow(index)"
                                :disabled="rows.length <= 1"
                                :class="{
                                    'opacity-50 cursor-not-allowed':
                                        rows.length <= 1,
                                }"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Columns Management -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Kolom
                            </label>
                            <button
                                type="button"
                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                @click="addColumn"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Tambah Kolom
                            </button>
                        </div>

                        <!-- Columns list with input fields -->
                        <div
                            v-for="(column, index) in columns"
                            :key="column.id"
                            class="flex items-center mt-2"
                        >
                            <div class="flex-grow">
                                <div class="flex items-center">
                                    <span class="text-xs text-gray-500 w-7"
                                        >{{ index + 1 }}.</span
                                    >
                                    <input
                                        type="text"
                                        v-model="column.text"
                                        class="flex-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Teks kolom"
                                        @change="updateQuestion"
                                    />
                                </div>
                            </div>
                            <button
                                type="button"
                                class="ml-2 p-1 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                                @click="removeColumn(index)"
                                :disabled="columns.length <= 2"
                                :class="{
                                    'opacity-50 cursor-not-allowed':
                                        columns.length <= 2,
                                }"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div
                        class="p-3 bg-gray-50 rounded border border-gray-200 mt-4"
                    >
                        <h4 class="text-sm font-medium text-gray-700 mb-2">
                            Preview
                        </h4>
                        <div class="text-xs text-gray-600">
                            <p>
                                {{
                                    matrixType === "radio"
                                        ? "Pilihan Tunggal"
                                        : "Pilihan Ganda"
                                }}
                                dengan {{ rows.length }} baris dan
                                {{ columns.length }} kolom.
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </QuestionSettingsPanel>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";
import QuestionSettingsPanel from "./QuestionSettingsPanel.vue";
import { v4 as uuidv4 } from "uuid";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "update:question",
    "duplicate-question",
    "delete-question",
]);

// Local state
const matrixType = ref(props.question.matrixType || "radio");
const rows = ref([]);
const columns = ref([]);

// Initialize rows and columns from question or defaults
function initializeMatrixData() {
    // Initialize rows
    if (props.question.rows && props.question.rows.length > 0) {
        rows.value = JSON.parse(JSON.stringify(props.question.rows));
    } else {
        // Default rows
        rows.value = [
            { id: uuidv4(), text: "Baris 1" },
            { id: uuidv4(), text: "Baris 2" },
        ];
    }

    // Initialize columns
    if (props.question.columns && props.question.columns.length > 0) {
        columns.value = JSON.parse(JSON.stringify(props.question.columns));
    } else {
        // Default columns
        columns.value = [
            { id: uuidv4(), text: "Kolom 1" },
            { id: uuidv4(), text: "Kolom 2" },
            { id: uuidv4(), text: "Kolom 3" },
        ];
    }
}

// Initialize data
initializeMatrixData();

// Watch for changes in the question prop
watch(
    () => props.question,
    (newQuestion) => {
        matrixType.value = newQuestion.matrixType || "radio";

        if (newQuestion.rows) {
            rows.value = JSON.parse(JSON.stringify(newQuestion.rows));
        }

        if (newQuestion.columns) {
            columns.value = JSON.parse(JSON.stringify(newQuestion.columns));
        }
    },
    { deep: true }
);

// Functions for managing rows
function addRow() {
    const newRowNumber = rows.value.length + 1;
    rows.value.push({
        id: uuidv4(),
        text: `Baris ${newRowNumber}`,
    });
    updateQuestion();
}

function removeRow(index) {
    if (rows.value.length <= 1) return; // Prevent removing the last row
    rows.value.splice(index, 1);
    updateQuestion();
}

// Functions for managing columns
function addColumn() {
    const newColumnNumber = columns.value.length + 1;
    columns.value.push({
        id: uuidv4(),
        text: `Kolom ${newColumnNumber}`,
    });
    updateQuestion();
}

function removeColumn(index) {
    if (columns.value.length <= 2) return; // Always keep at least 2 columns
    columns.value.splice(index, 1);
    updateQuestion();
}

// Update base question properties
const updateQuestionBase = (updatedBaseQuestion) => {
    emit("update:question", {
        ...updatedBaseQuestion,
        matrixType: matrixType.value,
        rows: [...rows.value],
        columns: [...columns.value],
    });
};

// Update matrix-specific settings
const updateQuestion = () => {
    emit("update:question", {
        ...props.question,
        matrixType: matrixType.value,
        rows: [...rows.value],
        columns: [...columns.value],
    });
};
</script>
