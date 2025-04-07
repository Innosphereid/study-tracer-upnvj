<!-- 
* @component LikertScaleVisualizer
* @description Component for visualizing Likert scale responses
-->
<template>
    <div data-question-id="likert-visualizer-{{ question.id }}">
        <!-- Horizontal stacked bar chart -->
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-500 mb-3">
                Response Distribution
            </h4>
            <div class="h-80">
                <canvas ref="barCanvas"></canvas>
            </div>
        </div>

        <!-- Statistics summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Average Rating
                </h4>
                <p class="text-xl font-bold">{{ stats.average }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Most Common
                </h4>
                <p class="text-xl font-bold">{{ stats.mode }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Distribution
                </h4>
                <p class="text-md">
                    <span
                        v-for="(value, key) in stats.distribution"
                        :key="key"
                        class="inline-block mr-2"
                    >
                        {{ key }}: <span class="font-medium">{{ value }}%</span>
                    </span>
                </p>
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
                                Rating
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Label
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Submitted
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="
                                        getRatingColor(response.answer_value)
                                    "
                                >
                                    {{ response.answer_value }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                            >
                                {{ getLikertLabel(response.answer_value) }}
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
    name: "LikertScaleVisualizer",

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
        const barCanvas = ref(null);
        const chart = ref(null);
        const showResponses = ref(false);

        // Define Likert scale options based on question settings or use defaults
        const likertOptions = computed(() => {
            // Try to parse options from question settings if available
            try {
                if (
                    props.question.settings &&
                    props.question.settings.likert_options
                ) {
                    return props.question.settings.likert_options;
                }
            } catch (e) {
                console.error("Error parsing Likert options:", e);
            }

            // Default 5-point Likert scale
            return {
                min: 1,
                max: 5,
                labels: {
                    1: "Strongly Disagree",
                    2: "Disagree",
                    3: "Neutral",
                    4: "Agree",
                    5: "Strongly Agree",
                },
            };
        });

        // Extract numeric values from responses
        const likertValues = computed(() => {
            return props.responses
                .map((response) => {
                    let value = response.answer_value;

                    // Handle different formats
                    if (typeof value === "string") {
                        // Try to parse as number
                        value = parseInt(value, 10);
                    }

                    // Ensure value is within valid range
                    if (typeof value === "number" && !isNaN(value)) {
                        const min = likertOptions.value.min || 1;
                        const max = likertOptions.value.max || 5;

                        if (value >= min && value <= max) {
                            return value;
                        }
                    }

                    return null;
                })
                .filter((value) => value !== null);
        });

        // Calculate statistics
        const stats = computed(() => {
            const values = likertValues.value;
            if (!values.length) {
                return {
                    average: "N/A",
                    mode: "N/A",
                    distribution: {},
                };
            }

            // Calculate average
            const sum = values.reduce((a, b) => a + b, 0);
            const average = sum / values.length;

            // Calculate mode (most common value)
            const frequency = {};
            let maxFreq = 0;
            let mode = null;

            values.forEach((value) => {
                frequency[value] = (frequency[value] || 0) + 1;
                if (frequency[value] > maxFreq) {
                    maxFreq = frequency[value];
                    mode = value;
                }
            });

            // Calculate distribution percentages
            const distribution = {};
            const min = likertOptions.value.min || 1;
            const max = likertOptions.value.max || 5;

            for (let i = min; i <= max; i++) {
                const count = frequency[i] || 0;
                distribution[i] = Math.round((count / values.length) * 100);
            }

            return {
                average: average.toFixed(1),
                mode: `${mode} (${getLikertLabel(mode)})`,
                distribution,
            };
        });

        // Get label for a Likert value
        const getLikertLabel = (value) => {
            const labels = likertOptions.value.labels || {};
            return labels[value] || `Level ${value}`;
        };

        // Get color class based on rating value
        const getRatingColor = (value) => {
            const numValue = parseInt(value, 10);
            if (isNaN(numValue)) return "bg-gray-100 text-gray-800";

            const min = likertOptions.value.min || 1;
            const max = likertOptions.value.max || 5;
            const middle = Math.ceil((max + min) / 2);

            if (numValue < middle - 1) {
                return "bg-red-100 text-red-800"; // Low values
            } else if (numValue === middle - 1) {
                return "bg-orange-100 text-orange-800"; // Below middle
            } else if (numValue === middle) {
                return "bg-blue-100 text-blue-800"; // Middle value
            } else if (numValue === middle + 1) {
                return "bg-indigo-100 text-indigo-800"; // Above middle
            } else {
                return "bg-green-100 text-green-800"; // High values
            }
        };

        // Initialize chart
        const initChart = () => {
            if (!barCanvas.value) return;

            const ctx = barCanvas.value.getContext("2d");

            // Destroy existing chart if it exists
            if (chart.value) {
                chart.value.destroy();
            }

            // Prepare data for horizontal stacked bar
            const min = likertOptions.value.min || 1;
            const max = likertOptions.value.max || 5;
            const labels = likertOptions.value.labels || {};

            // Count responses for each value
            const counts = {};
            for (let i = min; i <= max; i++) {
                counts[i] = 0;
            }

            likertValues.value.forEach((value) => {
                counts[value] = (counts[value] || 0) + 1;
            });

            // Calculate percentages
            const total = likertValues.value.length;
            const datasets = [];
            const colors = [
                "rgba(239, 68, 68, 0.7)", // Red (Strongly Disagree)
                "rgba(249, 115, 22, 0.7)", // Orange (Disagree)
                "rgba(59, 130, 246, 0.7)", // Blue (Neutral)
                "rgba(79, 70, 229, 0.7)", // Indigo (Agree)
                "rgba(16, 185, 129, 0.7)", // Green (Strongly Agree)
            ];

            // Create dataset for each value
            for (let i = min; i <= max; i++) {
                datasets.push({
                    label: labels[i] || `Level ${i}`,
                    data: [Math.round((counts[i] / total) * 100) || 0],
                    backgroundColor: colors[i - min],
                    borderColor: colors[i - min].replace("0.7", "1"),
                    borderWidth: 1,
                });
            }

            // Create new chart
            chart.value = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: [props.question.title],
                    datasets,
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: "Percentage of Responses",
                            },
                            max: 100,
                        },
                        y: {
                            stacked: true,
                        },
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.dataset.label || "";
                                    const value = context.raw || 0;
                                    return `${label}: ${value}%`;
                                },
                            },
                        },
                    },
                },
            });
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
            barCanvas,
            showResponses,
            stats,
            likertOptions,
            getLikertLabel,
            getRatingColor,
            formatTimestamp,
            toggleResponses,
        };
    },
};
</script>

<style scoped>
/* Component-specific styles */
</style>
