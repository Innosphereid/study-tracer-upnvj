<!-- 
* @component YesNoVisualizer
* @description Component for visualizing Yes/No question responses
-->
<template>
    <div data-question-id="yes-no-visualizer-{{ question.id }}">
        <!-- Pie chart visualization -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center justify-center">
                <div
                    class="chart-container"
                    style="position: relative; height: 250px; width: 250px"
                >
                    <canvas ref="pieCanvas"></canvas>
                </div>
            </div>

            <div class="flex flex-col justify-center">
                <!-- Statistics cards -->
                <div class="grid grid-cols-2 gap-4">
                    <div
                        class="bg-green-50 p-4 rounded-lg border border-green-100"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center"
                            >
                                <svg
                                    class="h-6 w-6 text-green-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-green-800">
                                    Yes
                                </h4>
                                <p class="mt-1">
                                    <span
                                        class="text-2xl font-bold text-green-900"
                                        >{{ stats.yesCount }}</span
                                    >
                                    <span class="ml-2 text-sm text-green-600"
                                        >({{ stats.yesPercent }}%)</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center"
                            >
                                <svg
                                    class="h-6 w-6 text-red-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-red-800">
                                    No
                                </h4>
                                <p class="mt-1">
                                    <span
                                        class="text-2xl font-bold text-red-900"
                                        >{{ stats.noCount }}</span
                                    >
                                    <span class="ml-2 text-sm text-red-600"
                                        >({{ stats.noPercent }}%)</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key insight -->
                <div
                    class="mt-6 bg-blue-50 p-4 rounded-lg border border-blue-100"
                >
                    <h4 class="text-sm font-medium text-blue-800 mb-1">
                        Key Insight
                    </h4>
                    <p class="text-blue-700">
                        {{ keyInsight }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Detailed responses -->
        <div class="mt-8" v-if="responses.length > 0">
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
                                Answer
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
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        isYesResponse(response.answer_value)
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800',
                                    ]"
                                >
                                    {{
                                        formatYesNoValue(response.answer_value)
                                    }}
                                </span>
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
    name: "YesNoVisualizer",

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
        const pieCanvas = ref(null);
        const chart = ref(null);
        const showResponses = ref(false);

        // Calculate statistics from responses
        const stats = computed(() => {
            const total = props.responses.length;
            if (total === 0) {
                return {
                    yesCount: 0,
                    noCount: 0,
                    yesPercent: 0,
                    noPercent: 0,
                };
            }

            const yesCount = props.responses.filter((response) =>
                isYesResponse(response.answer_value)
            ).length;

            const noCount = total - yesCount;

            return {
                yesCount,
                noCount,
                yesPercent: Math.round((yesCount / total) * 100),
                noPercent: Math.round((noCount / total) * 100),
            };
        });

        // Generate insight based on the data
        const keyInsight = computed(() => {
            const { yesCount, noCount, yesPercent, noPercent } = stats.value;
            const total = props.responses.length;

            if (total === 0) {
                return "No responses have been collected yet.";
            }

            if (yesCount === total) {
                return 'All respondents answered "Yes" to this question.';
            }

            if (noCount === total) {
                return 'All respondents answered "No" to this question.';
            }

            if (yesPercent >= 75) {
                return `Strong majority (${yesPercent}%) of respondents answered "Yes" to this question.`;
            }

            if (noPercent >= 75) {
                return `Strong majority (${noPercent}%) of respondents answered "No" to this question.`;
            }

            if (yesPercent > noPercent) {
                return `More respondents answered "Yes" (${yesPercent}%) than "No" (${noPercent}%).`;
            }

            if (noPercent > yesPercent) {
                return `More respondents answered "No" (${noPercent}%) than "Yes" (${yesPercent}%).`;
            }

            return 'Responses are evenly split between "Yes" and "No".';
        });

        // Check if a response value is considered "Yes"
        const isYesResponse = (value) => {
            if (value === null || value === undefined) return false;

            if (typeof value === "boolean") {
                return value === true;
            }

            if (typeof value === "number") {
                return value === 1;
            }

            if (typeof value === "string") {
                const normalized = value.toLowerCase().trim();
                return [
                    "yes",
                    "y",
                    "true",
                    "1",
                    "ya",
                    "setuju",
                    "agreed",
                ].includes(normalized);
            }

            return false;
        };

        // Format yes/no value for display
        const formatYesNoValue = (value) => {
            return isYesResponse(value) ? "Yes" : "No";
        };

        // Format timestamp for display
        const formatTimestamp = (timestamp) => {
            if (!timestamp) return "N/A";
            return new Date(timestamp).toLocaleString();
        };

        // Initialize pie chart
        const initChart = () => {
            if (!pieCanvas.value) return;

            const ctx = pieCanvas.value.getContext("2d");

            // Destroy existing chart if it exists
            if (chart.value) {
                chart.value.destroy();
            }

            // Create new chart
            chart.value = new Chart(ctx, {
                type: "pie",
                data: {
                    labels: ["Yes", "No"],
                    datasets: [
                        {
                            data: [stats.value.yesCount, stats.value.noCount],
                            backgroundColor: [
                                "rgba(16, 185, 129, 0.7)", // Green for Yes
                                "rgba(239, 68, 68, 0.7)", // Red for No
                            ],
                            borderColor: [
                                "rgba(16, 185, 129, 1)",
                                "rgba(239, 68, 68, 1)",
                            ],
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || "";
                                    const value = context.raw || 0;
                                    const total =
                                        stats.value.yesCount +
                                        stats.value.noCount;
                                    const percentage = Math.round(
                                        (value / total) * 100
                                    );
                                    return `${label}: ${value} (${percentage}%)`;
                                },
                            },
                        },
                    },
                },
            });
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
            pieCanvas,
            showResponses,
            stats,
            keyInsight,
            isYesResponse,
            formatYesNoValue,
            formatTimestamp,
            toggleResponses,
        };
    },
};
</script>

<style scoped>
/* Component-specific styles */
</style>
