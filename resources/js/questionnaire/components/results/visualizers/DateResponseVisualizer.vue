<!-- 
* @component DateResponseVisualizer
* @description Component for visualizing date responses with timeline and frequency analysis
-->
<template>
    <div data-question-id="date-visualizer-{{ question.id }}">
        <!-- Timeline visualization -->
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-500 mb-3">
                Timeline Distribution
            </h4>
            <div class="h-60">
                <canvas ref="timelineCanvas"></canvas>
            </div>
        </div>

        <!-- Date stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Earliest Date
                </h4>
                <p class="text-xl font-bold">{{ stats.earliest || "N/A" }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Latest Date
                </h4>
                <p class="text-xl font-bold">{{ stats.latest || "N/A" }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Most Common
                </h4>
                <p class="text-xl font-bold">{{ stats.mostCommon || "N/A" }}</p>
            </div>
        </div>

        <!-- Monthly breakdown if data spans more than a month -->
        <div v-if="monthlyDistribution.length > 1" class="mb-6">
            <h4 class="text-sm font-medium text-gray-500 mb-3">
                Monthly Breakdown
            </h4>
            <div class="h-60">
                <canvas ref="monthlyCanvas"></canvas>
            </div>
        </div>

        <!-- Detailed responses -->
        <div class="mt-6" v-if="responses.length > 0">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-sm font-medium text-gray-500">All Dates</h4>
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
                                Date
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
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                            >
                                {{ formatDate(response.answer_value) }}
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
    name: "DateResponseVisualizer",

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
        const timelineCanvas = ref(null);
        const monthlyCanvas = ref(null);
        const timelineChart = ref(null);
        const monthlyChart = ref(null);
        const showResponses = ref(false);

        // Parse dates from responses
        const parsedDates = computed(() => {
            return props.responses
                .map((response) => {
                    try {
                        const dateValue = response.answer_value;
                        if (!dateValue) return null;

                        // Handle date format variations
                        return new Date(dateValue);
                    } catch (e) {
                        console.error("Error parsing date:", e);
                        return null;
                    }
                })
                .filter((date) => date && !isNaN(date.getTime()));
        });

        // Calculate date statistics
        const stats = computed(() => {
            const dates = parsedDates.value;
            if (!dates.length)
                return { earliest: null, latest: null, mostCommon: null };

            // Sort dates for easier calculations
            const sortedDates = [...dates].sort((a, b) => a - b);

            // Find most common date
            const dateFrequency = {};
            let maxFreq = 0;
            let mostCommonDate = null;

            dates.forEach((date) => {
                const dateStr = formatDate(date);
                dateFrequency[dateStr] = (dateFrequency[dateStr] || 0) + 1;

                if (dateFrequency[dateStr] > maxFreq) {
                    maxFreq = dateFrequency[dateStr];
                    mostCommonDate = date;
                }
            });

            return {
                earliest: formatDate(sortedDates[0]),
                latest: formatDate(sortedDates[sortedDates.length - 1]),
                mostCommon: mostCommonDate
                    ? `${formatDate(mostCommonDate)} (${maxFreq} responses)`
                    : null,
            };
        });

        // Group dates by month
        const monthlyDistribution = computed(() => {
            const dates = parsedDates.value;
            if (!dates.length) return [];

            const monthData = {};

            dates.forEach((date) => {
                const monthYear = `${date.getFullYear()}-${String(
                    date.getMonth() + 1
                ).padStart(2, "0")}`;
                monthData[monthYear] = (monthData[monthYear] || 0) + 1;
            });

            // Convert to array and sort by date
            return Object.entries(monthData)
                .map(([monthYear, count]) => ({
                    monthYear,
                    count,
                    label: new Date(monthYear + "-01").toLocaleDateString(
                        undefined,
                        {
                            year: "numeric",
                            month: "short",
                        }
                    ),
                }))
                .sort((a, b) => a.monthYear.localeCompare(b.monthYear));
        });

        // Create histogram data for timeline chart
        const generateTimelineData = () => {
            const dates = parsedDates.value;
            if (!dates.length) return { labels: [], data: [] };

            // Sort dates
            const sortedDates = [...dates].sort((a, b) => a - b);

            // Determine date range
            const earliestDate = sortedDates[0];
            const latestDate = sortedDates[sortedDates.length - 1];

            // Determine appropriate bin size based on range
            const daysDiff = Math.ceil(
                (latestDate - earliestDate) / (1000 * 60 * 60 * 24)
            );

            let binSize = 1; // Default 1 day

            // Adjust bin size based on range to avoid too many or too few bins
            if (daysDiff > 90) {
                binSize = 30; // Monthly for large ranges
            } else if (daysDiff > 30) {
                binSize = 7; // Weekly for medium ranges
            }

            // Calculate number of bins
            const numBins = Math.ceil(daysDiff / binSize);
            const bins = Array(numBins).fill(0);
            const labels = [];

            // Create bin labels
            for (let i = 0; i < numBins; i++) {
                const binDate = new Date(earliestDate);
                binDate.setDate(binDate.getDate() + i * binSize);
                labels.push(formatDate(binDate));
            }

            // Count dates in each bin
            dates.forEach((date) => {
                const daysDiffFromStart = Math.floor(
                    (date - earliestDate) / (1000 * 60 * 60 * 24)
                );
                const binIndex = Math.min(
                    numBins - 1,
                    Math.floor(daysDiffFromStart / binSize)
                );
                bins[binIndex]++;
            });

            return { labels, data: bins };
        };

        // Initialize timeline chart
        const initTimelineChart = () => {
            if (!timelineCanvas.value) return;

            const ctx = timelineCanvas.value.getContext("2d");
            const { labels, data } = generateTimelineData();

            // Destroy existing chart if it exists
            if (timelineChart.value) {
                timelineChart.value.destroy();
            }

            // Create new chart
            timelineChart.value = new Chart(ctx, {
                type: "bar",
                data: {
                    labels,
                    datasets: [
                        {
                            label: "Number of Responses",
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
                                text: "Count",
                            },
                            ticks: {
                                precision: 0,
                            },
                        },
                        x: {
                            title: {
                                display: true,
                                text: "Date Range",
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
                                    return `Responses: ${context.raw}`;
                                },
                            },
                        },
                    },
                },
            });
        };

        // Initialize monthly chart
        const initMonthlyChart = () => {
            if (!monthlyCanvas.value || monthlyDistribution.value.length <= 1)
                return;

            const ctx = monthlyCanvas.value.getContext("2d");

            // Destroy existing chart if it exists
            if (monthlyChart.value) {
                monthlyChart.value.destroy();
            }

            // Create new chart
            monthlyChart.value = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: monthlyDistribution.value.map((item) => item.label),
                    datasets: [
                        {
                            label: "Responses per Month",
                            data: monthlyDistribution.value.map(
                                (item) => item.count
                            ),
                            backgroundColor: "rgba(16, 185, 129, 0.5)",
                            borderColor: "rgba(16, 185, 129, 1)",
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
                                text: "Count",
                            },
                            ticks: {
                                precision: 0,
                            },
                        },
                        x: {
                            title: {
                                display: true,
                                text: "Month",
                            },
                        },
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `Responses: ${context.raw}`;
                                },
                            },
                        },
                    },
                },
            });
        };

        // Format date for display
        const formatDate = (date) => {
            if (!date) return "N/A";
            if (typeof date === "string") {
                date = new Date(date);
            }

            if (isNaN(date.getTime())) {
                return "Invalid Date";
            }

            return date.toLocaleDateString(undefined, {
                year: "numeric",
                month: "short",
                day: "numeric",
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
                initTimelineChart();
                initMonthlyChart();
            },
            { deep: true }
        );

        // Initialize on mount
        onMounted(() => {
            emit("loading", true);
            setTimeout(() => {
                initTimelineChart();
                initMonthlyChart();
                emit("loading", false);
            }, 200);
        });

        return {
            timelineCanvas,
            monthlyCanvas,
            showResponses,
            stats,
            monthlyDistribution,
            formatDate,
            formatTimestamp,
            toggleResponses,
        };
    },
};
</script>

<style scoped>
/* Component-specific styles */
</style>
