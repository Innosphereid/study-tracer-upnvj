<!-- 
* @component MatrixResponseVisualizer
* @description Component for visualizing matrix/grid question responses
-->
<template>
    <div data-question-id="matrix-visualizer-{{ question.id }}">
        <div v-if="matrixData">
            <!-- Matrix visualization -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Questions / Options
                            </th>
                            <th
                                v-for="option in matrixData.options"
                                :key="option"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                {{ option }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="(row, rowName) in matrixData.rows"
                            :key="rowName"
                            class="hover:bg-gray-50"
                        >
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                            >
                                {{ rowName }}
                            </td>
                            <td
                                v-for="(count, option) in row"
                                :key="option"
                                class="px-6 py-4 whitespace-nowrap text-center"
                            >
                                <div class="flex flex-col items-center">
                                    <span
                                        class="inline-flex justify-center items-center w-10 h-10 rounded-full text-sm font-medium"
                                        :class="
                                            getCountBgClass(
                                                count,
                                                getMaxCount(row)
                                            )
                                        "
                                    >
                                        {{ count }}
                                    </span>
                                    <span class="mt-1 text-xs text-gray-500">
                                        {{
                                            getPercentage(
                                                count,
                                                getTotalRowCount(row)
                                            )
                                        }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Row-based charts -->
            <div v-if="showRowCharts" class="mt-10 space-y-8">
                <div
                    v-for="(row, rowName) in matrixData.rows"
                    :key="`chart-${rowName}`"
                    class="rounded-lg bg-gray-50 p-4"
                >
                    <h4 class="text-sm font-medium text-gray-500 mb-3">
                        {{ rowName }}
                    </h4>
                    <div class="h-60">
                        <canvas
                            :ref="
                                (el) => {
                                    if (el) rowCharts[rowName] = el;
                                }
                            "
                        ></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="text-center py-8 px-4 bg-gray-50 rounded-lg">
            <svg
                class="mx-auto h-12 w-12 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
                No matrix data available
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Either no responses have been collected yet, or there was an
                issue processing the data.
            </p>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch, reactive } from "vue";
import Chart from "chart.js/auto";

export default {
    name: "MatrixResponseVisualizer",

    props: {
        question: {
            type: Object,
            required: true,
        },
        responses: {
            type: Array,
            required: true,
            default: () => [],
        },
    },

    setup(props, { emit }) {
        const rowCharts = reactive({});
        const charts = ref([]);
        const showRowCharts = ref(true);

        // Parse matrix data from question settings and responses
        const matrixData = computed(() => {
            try {
                // Check if we have responses
                if (!props.responses || props.responses.length === 0) {
                    return null;
                }

                // Try to get matrix configuration from question settings
                let rows = [];
                let options = [];

                // Extract rows and options from question settings if available
                if (props.question.settings && props.question.settings.matrix) {
                    rows = props.question.settings.matrix.rows || [];
                    options = props.question.settings.matrix.options || [];
                } else {
                    // If settings are not available, try to extract from responses
                    const sampleResponses = getSampleMatrixResponses();
                    if (!sampleResponses.length) return null;

                    // Extract unique rows and options
                    const uniqueRows = new Set();
                    const uniqueOptions = new Set();

                    sampleResponses.forEach((response) => {
                        Object.keys(response).forEach((row) => {
                            uniqueRows.add(row);
                            const value = response[row];
                            if (typeof value === "string") {
                                uniqueOptions.add(value);
                            }
                        });
                    });

                    rows = Array.from(uniqueRows);
                    options = Array.from(uniqueOptions);
                }

                if (rows.length === 0 || options.length === 0) {
                    return null;
                }

                // Initialize counts object
                const matrixCounts = {};
                rows.forEach((row) => {
                    matrixCounts[row] = {};
                    options.forEach((option) => {
                        matrixCounts[row][option] = 0;
                    });
                });

                // Count responses
                props.responses.forEach((response) => {
                    const answerValue = response.answer_value;
                    if (!answerValue) return;

                    let matrixResponse;
                    try {
                        // Try to parse as JSON if it's a string
                        if (typeof answerValue === "string") {
                            matrixResponse = JSON.parse(answerValue);
                        } else {
                            matrixResponse = answerValue;
                        }

                        // If it's an object with row keys
                        if (
                            matrixResponse &&
                            typeof matrixResponse === "object" &&
                            !Array.isArray(matrixResponse)
                        ) {
                            for (const row in matrixResponse) {
                                if (row in matrixCounts) {
                                    const selectedOption = matrixResponse[row];
                                    if (selectedOption in matrixCounts[row]) {
                                        matrixCounts[row][selectedOption]++;
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        console.error("Error parsing matrix response:", e);
                    }
                });

                return {
                    rows: matrixCounts,
                    options: options,
                };
            } catch (e) {
                console.error("Error processing matrix data:", e);
                return null;
            }
        });

        // Helper function to extract sample matrix responses for structure analysis
        const getSampleMatrixResponses = () => {
            const sampleResponses = [];

            for (const response of props.responses) {
                if (!response.answer_value) continue;

                try {
                    let matrixResponse;
                    if (typeof response.answer_value === "string") {
                        matrixResponse = JSON.parse(response.answer_value);
                    } else {
                        matrixResponse = response.answer_value;
                    }

                    if (
                        matrixResponse &&
                        typeof matrixResponse === "object" &&
                        !Array.isArray(matrixResponse)
                    ) {
                        sampleResponses.push(matrixResponse);
                        if (sampleResponses.length >= 5) break; // Get up to 5 samples
                    }
                } catch (e) {
                    continue;
                }
            }

            return sampleResponses;
        };

        // Get the total count for a row
        const getTotalRowCount = (row) => {
            return Object.values(row).reduce((sum, count) => sum + count, 0);
        };

        // Get the maximum count in a row
        const getMaxCount = (row) => {
            return Math.max(...Object.values(row));
        };

        // Calculate percentage for a cell
        const getPercentage = (count, total) => {
            if (!total) return 0;
            return Math.round((count / total) * 100);
        };

        // Get background color class based on count value
        const getCountBgClass = (count, maxCount) => {
            if (count === 0) return "bg-gray-100 text-gray-400";

            const ratio = count / maxCount;

            if (ratio < 0.2) return "bg-blue-100 text-blue-800";
            if (ratio < 0.4) return "bg-blue-200 text-blue-800";
            if (ratio < 0.6) return "bg-blue-300 text-blue-800";
            if (ratio < 0.8) return "bg-blue-400 text-white";
            return "bg-blue-500 text-white";
        };

        // Initialize row-based charts
        const initRowCharts = () => {
            // Clean up any existing charts
            charts.value.forEach((chart) => {
                if (chart) chart.destroy();
            });
            charts.value = [];

            // Skip if no matrix data
            if (!matrixData.value) return;

            // Create a chart for each row
            for (const [rowName, rowData] of Object.entries(
                matrixData.value.rows
            )) {
                const canvas = rowCharts[rowName];
                if (!canvas) continue;

                const ctx = canvas.getContext("2d");
                const options = Object.keys(rowData);
                const counts = Object.values(rowData);

                const chart = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: options,
                        datasets: [
                            {
                                label: rowName,
                                data: counts,
                                backgroundColor: "rgba(59, 130, 246, 0.5)",
                                borderColor: "rgba(59, 130, 246, 1)",
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const value = context.raw || 0;
                                        const total = counts.reduce(
                                            (sum, val) => sum + val,
                                            0
                                        );
                                        const percentage = total
                                            ? Math.round((value / total) * 100)
                                            : 0;
                                        return `${value} responses (${percentage}%)`;
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Number of Responses",
                                },
                                ticks: {
                                    precision: 0,
                                },
                            },
                        },
                    },
                });

                charts.value.push(chart);
            }
        };

        // Watch for changes in responses or matrix data
        watch(
            () => [props.responses, matrixData.value],
            () => {
                // Wait for DOM update before initializing charts
                setTimeout(() => {
                    initRowCharts();
                }, 100);
            },
            { deep: true }
        );

        // Initialize on mount
        onMounted(() => {
            emit("loading", true);
            setTimeout(() => {
                initRowCharts();
                emit("loading", false);
            }, 300);
        });

        return {
            matrixData,
            rowCharts,
            showRowCharts,
            getTotalRowCount,
            getMaxCount,
            getPercentage,
            getCountBgClass,
        };
    },
};
</script>

<style scoped>
/* Component-specific styles */
</style>
