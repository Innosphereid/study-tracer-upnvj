<!-- 
* @component NumericVisualizer
* @description Component for visualizing numerical data responses
-->
<template>
    <div data-question-id="numeric-visualizer-{{ question.id }}">
        <!-- Stats overview -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Average</h4>
                <p class="text-xl font-bold">{{ stats.average }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Median</h4>
                <p class="text-xl font-bold">{{ stats.median }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Min</h4>
                <p class="text-xl font-bold">{{ stats.min }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Max</h4>
                <p class="text-xl font-bold">{{ stats.max }}</p>
            </div>
        </div>

        <!-- Distribution chart -->
        <div>
            <h4 class="text-sm font-medium text-gray-500 mb-3">Distribution</h4>
            <div class="h-60">
                <canvas ref="chartCanvas"></canvas>
            </div>
        </div>

        <!-- Detailed responses -->
        <div class="mt-6" v-if="responses.length > 0">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-sm font-medium text-gray-500">All Responses</h4>
                <div>
                    <button
                        @click="toggleResponses"
                        class="text-sm text-blue-600 hover:text-blue-800"
                    >
                        {{ showResponses ? "Hide Details" : "Show Details" }}
                    </button>
                </div>
            </div>

            <div v-if="showResponses" class="mt-2 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Response ID
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Value
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Timestamp
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="(response, index) in responses"
                            :key="index"
                            class="hover:bg-gray-50"
                        >
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ response.response_id || "N/A" }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                            >
                                {{ formatNumericValue(response.answer_value) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ formatTimestamp(response.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from "vue";
import Chart from "chart.js/auto";

export default {
    name: "NumericVisualizer",

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
        const chartCanvas = ref(null);
        const chart = ref(null);
        const showResponses = ref(false);

        // Extract numeric values from responses
        const numericValues = computed(() => {
            return props.responses
                .map((response) => {
                    const value = response.answer_value;
                    return typeof value === "string"
                        ? parseFloat(value)
                        : value;
                })
                .filter((value) => !isNaN(value));
        });

        // Calculate statistics
        const stats = computed(() => {
            const values = numericValues.value;
            if (!values.length) {
                return {
                    average: "N/A",
                    median: "N/A",
                    min: "N/A",
                    max: "N/A",
                };
            }

            // Sort values for easier calculations
            const sortedValues = [...values].sort((a, b) => a - b);

            // Calculate average (mean)
            const sum = sortedValues.reduce((a, b) => a + b, 0);
            const average = sum / sortedValues.length;

            // Calculate median
            const mid = Math.floor(sortedValues.length / 2);
            const median =
                sortedValues.length % 2 === 0
                    ? (sortedValues[mid - 1] + sortedValues[mid]) / 2
                    : sortedValues[mid];

            return {
                average: formatNumericValue(average),
                median: formatNumericValue(median),
                min: formatNumericValue(sortedValues[0]),
                max: formatNumericValue(sortedValues[sortedValues.length - 1]),
            };
        });

        // Handle creating bins for histogram
        const generateHistogramData = () => {
            const values = numericValues.value;
            if (!values.length) return { labels: [], data: [] };

            // Determine bin count (square root rule)
            const binCount = Math.max(5, Math.ceil(Math.sqrt(values.length)));

            // Sort values and determine range
            const sortedValues = [...values].sort((a, b) => a - b);
            const min = sortedValues[0];
            const max = sortedValues[sortedValues.length - 1];

            // Create bins
            const binSize = (max - min) / binCount;
            const bins = Array(binCount).fill(0);
            const labels = [];

            // Create bin labels
            for (let i = 0; i < binCount; i++) {
                const binStart = min + i * binSize;
                const binEnd = binStart + binSize;
                labels.push(
                    `${formatNumericValue(binStart)} - ${formatNumericValue(
                        binEnd
                    )}`
                );
            }

            // Count values in each bin
            values.forEach((value) => {
                const binIndex = Math.min(
                    binCount - 1,
                    Math.floor((value - min) / binSize)
                );
                bins[binIndex]++;
            });

            return { labels, data: bins };
        };

        // Initialize chart
        const initChart = () => {
            if (!chartCanvas.value) return;

            const ctx = chartCanvas.value.getContext("2d");
            const { labels, data } = generateHistogramData();

            // Destroy existing chart if it exists
            if (chart.value) {
                chart.value.destroy();
            }

            // Create new chart
            chart.value = new Chart(ctx, {
                type: "bar",
                data: {
                    labels,
                    datasets: [
                        {
                            label: "Frequency",
                            data,
                            backgroundColor: "rgba(59, 130, 246, 0.5)",
                            borderColor: "rgba(59, 130, 246, 1)",
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Frequency",
                            },
                            ticks: {
                                precision: 0,
                            },
                        },
                        x: {
                            title: {
                                display: true,
                                text: "Value",
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                title: function (tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function (context) {
                                    return `Count: ${context.raw}`;
                                },
                            },
                        },
                    },
                },
            });
        };

        // Format numeric value for display
        const formatNumericValue = (value) => {
            if (value === null || value === undefined || Number.isNaN(value)) {
                return "N/A";
            }

            // Handle decimal precision based on value
            if (Number.isInteger(value)) {
                return value.toString();
            } else {
                return value.toFixed(2);
            }
        };

        // Format timestamp for display
        const formatTimestamp = (timestamp) => {
            if (!timestamp) return "N/A";
            return new Date(timestamp).toLocaleString();
        };

        // Toggle detailed responses visibility
        const toggleResponses = () => {
            showResponses.value = !showResponses.value;
        };

        // Watch for changes in responses
        watch(
            () => props.responses,
            () => {
                initChart();
            },
            { deep: true }
        );

        // Initialize on mount
        onMounted(() => {
            emit("loading", true);
            setTimeout(() => {
                initChart();
                emit("loading", false);
            }, 200);
        });

        return {
            chartCanvas,
            showResponses,
            stats,
            formatNumericValue,
            formatTimestamp,
            toggleResponses,
        };
    },
};
</script>

<style scoped>
/* Component-specific styles */
</style>
