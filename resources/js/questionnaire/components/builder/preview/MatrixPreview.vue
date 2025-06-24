<template>
    <div class="mt-2">
        <div
            class="bg-gray-50 rounded-md border border-gray-200 overflow-hidden"
        >
            <!-- Matrix table with header and rows -->
            <div class="overflow-x-auto hover:shadow-inner">
                <table class="min-w-full divide-y divide-gray-200 table-fixed">
                    <!-- Table Header -->
                    <thead class="bg-gray-100">
                        <tr>
                            <!-- Empty corner cell -->
                            <th
                                scope="col"
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4"
                            >
                                <!-- Empty corner cell -->
                            </th>
                            <!-- Column headers - limit to max-width for better display -->
                            <th
                                v-for="(column, colIndex) in matrixColumns &&
                                matrixColumns.length
                                    ? matrixColumns
                                    : defaultMatrixColumns"
                                :key="colIndex"
                                scope="col"
                                class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                style="max-width: 100px"
                            >
                                <div class="truncate">
                                    {{ column.text }}
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Show maximum 3 rows or default ones if not defined -->
                        <tr
                            v-for="(row, rowIndex) in previewRows &&
                            previewRows.length
                                ? previewRows
                                : defaultMatrixRows.slice(0, 3)"
                            :key="rowIndex"
                            class="bg-white hover:bg-gray-50"
                        >
                            <!-- Row header -->
                            <td
                                class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-700 bg-gray-50"
                            >
                                <div class="truncate">
                                    {{ row.text }}
                                </div>
                            </td>

                            <!-- Matrix cells -->
                            <td
                                v-for="(column, colIndex) in matrixColumns &&
                                matrixColumns.length
                                    ? matrixColumns
                                    : defaultMatrixColumns"
                                :key="colIndex"
                                class="px-2 py-2 whitespace-nowrap text-center text-sm"
                                :class="{
                                    'bg-indigo-50':
                                        rowIndex === 0 && colIndex === 1,
                                }"
                            >
                                <!-- Radio button if matrixType is radio or default -->
                                <div
                                    v-if="matrixType !== 'checkbox'"
                                    class="flex justify-center"
                                >
                                    <input
                                        type="radio"
                                        disabled
                                        class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
                                        :checked="
                                            rowIndex === 0 && colIndex === 1
                                        "
                                    />
                                </div>

                                <!-- Checkbox if matrixType is checkbox -->
                                <div v-else class="flex justify-center">
                                    <input
                                        type="checkbox"
                                        disabled
                                        class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 rounded"
                                        :checked="
                                            rowIndex === 0 && colIndex === 1
                                        "
                                    />
                                </div>
                            </td>
                        </tr>

                        <!-- Indicator for more rows if applicable -->
                        <tr v-if="hasMoreRows" class="bg-white">
                            <td
                                colspan="100%"
                                class="px-3 py-2 text-center text-xs text-gray-500 border-t border-dashed border-gray-300"
                            >
                                <span class="flex items-center justify-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 mr-1"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                    {{ matrixRows.length - 3 }} baris lainnya
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Help text -->
            <div class="py-2 px-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center text-xs text-gray-500">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <span>
                        Matriks
                        {{
                            matrixType === "checkbox"
                                ? "pilihan ganda"
                                : "pilihan tunggal"
                        }}
                        dengan
                        {{ matrixRows ? matrixRows.length : 0 }}
                        baris dan
                        {{ matrixColumns ? matrixColumns.length : 0 }}
                        kolom
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, computed } from "vue";
import { useDefaultConfigs } from "./composables/useDefaultConfigs";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const { defaultMatrixRows, defaultMatrixColumns } = useDefaultConfigs();

// Computed properties for displaying matrix data
const matrixRows = computed(() => {
    return props.question.rows && props.question.rows.length
        ? props.question.rows
        : defaultMatrixRows;
});

const matrixColumns = computed(() => {
    return props.question.columns && props.question.columns.length
        ? props.question.columns
        : defaultMatrixColumns;
});

const matrixType = computed(() => {
    return props.question.matrixType || "radio";
});

const previewRows = computed(() => {
    // Return up to 3 rows for preview
    return matrixRows.value.slice(0, 3);
});

// Whether to show "more rows" indicator
const hasMoreRows = computed(() => {
    return matrixRows.value.length > 3;
});
</script>
