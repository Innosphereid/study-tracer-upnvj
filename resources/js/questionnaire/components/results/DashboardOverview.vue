<!-- 
* @component DashboardOverview
* @description Komponen dashboard yang menampilkan metrik utama dan visualisasi gambaran
* umum dari semua respons kuesioner.
-->
<template>
    <div class="dashboard-overview">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Timeline Responses Card -->
            <div
                class="bg-white rounded-lg shadow-sm p-5 hover:shadow-md transition-shadow"
            >
                <h3 class="text-lg font-medium text-gray-900 mb-3">
                    Response Timeline
                </h3>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-sm font-medium text-gray-500">
                            {{ timelineData.length }} responses over time
                        </div>
                        <div class="flex space-x-1">
                            <button
                                @click="timelineView = 'daily'"
                                :class="[
                                    'px-2 py-1 text-xs rounded',
                                    timelineView === 'daily'
                                        ? 'bg-blue-100 text-blue-800'
                                        : 'bg-gray-100 text-gray-700',
                                ]"
                            >
                                Daily
                            </button>
                            <button
                                @click="timelineView = 'weekly'"
                                :class="[
                                    'px-2 py-1 text-xs rounded',
                                    timelineView === 'weekly'
                                        ? 'bg-blue-100 text-blue-800'
                                        : 'bg-gray-100 text-gray-700',
                                ]"
                            >
                                Weekly
                            </button>
                            <button
                                @click="timelineView = 'monthly'"
                                :class="[
                                    'px-2 py-1 text-xs rounded',
                                    timelineView === 'monthly'
                                        ? 'bg-blue-100 text-blue-800'
                                        : 'bg-gray-100 text-gray-700',
                                ]"
                            >
                                Monthly
                            </button>
                        </div>
                    </div>

                    <div class="chart-container h-48">
                        <!-- Placeholder for chart - will be replaced with actual chart in production -->
                        <div
                            v-if="!hasTimelineData"
                            class="flex justify-center items-center h-full"
                        >
                            <div class="text-center text-gray-500">
                                <svg
                                    class="mx-auto h-10 w-10 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                <p class="mt-2 text-sm">
                                    No timeline data available
                                </p>
                            </div>
                        </div>
                        <canvas
                            v-else
                            ref="timelineCanvas"
                            class="w-full h-full"
                        ></canvas>
                    </div>
                </div>
            </div>

            <!-- Completion Status Card -->
            <div
                class="bg-white rounded-lg shadow-sm p-5 hover:shadow-md transition-shadow"
            >
                <h3 class="text-lg font-medium text-gray-900 mb-3">
                    Completion Status
                </h3>
                <div class="flex flex-col items-center justify-center h-48">
                    <div
                        v-if="!hasCompletionData"
                        class="text-center text-gray-500"
                    >
                        <svg
                            class="mx-auto h-10 w-10 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <p class="mt-2 text-sm">No completion data available</p>
                    </div>
                    <div
                        v-else
                        class="donut-chart-container relative w-32 h-32"
                    >
                        <canvas
                            ref="completionCanvas"
                            class="w-full h-full"
                        ></canvas>
                        <div
                            class="absolute inset-0 flex items-center justify-center"
                        >
                            <div class="text-center">
                                <span
                                    class="block text-2xl font-semibold text-gray-800"
                                    >{{ completionPercentage }}%</span
                                >
                                <span class="text-xs text-gray-500"
                                    >completion</span
                                >
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="hasCompletionData"
                        class="flex justify-around w-full mt-4"
                    >
                        <div class="text-center">
                            <span class="text-sm font-medium text-gray-500"
                                >Completed</span
                            >
                            <div class="text-lg font-semibold text-gray-900">
                                {{ statistics.completed_responses || 0 }}
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="text-sm font-medium text-gray-500"
                                >In progress</span
                            >
                            <div class="text-lg font-semibold text-gray-900">
                                {{ inProgressCount }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from "vue";
import Chart from "chart.js/auto";

export default {
    name: "DashboardOverview",

    props: {
        statistics: {
            type: Object,
            required: true,
            default: () => ({}),
        },
        period: {
            type: Object,
            required: false,
            default: () => ({ type: "all", start: null, end: null }),
        },
    },

    setup(props) {
        const timelineCanvas = ref(null);
        const completionCanvas = ref(null);
        const timelineView = ref("daily");
        let timelineChart = null;
        let completionChart = null;

        // Computed properties
        const timelineData = computed(() => {
            return props.statistics.responses_per_day || {};
        });

        const hasTimelineData = computed(() => {
            return Object.keys(timelineData.value).length > 0;
        });

        const hasCompletionData = computed(() => {
            return props.statistics.total_responses > 0;
        });

        const completionPercentage = computed(() => {
            return Math.round(props.statistics.completion_rate || 0);
        });

        const inProgressCount = computed(() => {
            const total = props.statistics.total_responses || 0;
            const completed = props.statistics.completed_responses || 0;
            return total - completed;
        });

        // Methods for chart creation
        const createTimelineChart = () => {
            if (!timelineCanvas.value || !hasTimelineData.value) return;

            const ctx = timelineCanvas.value.getContext("2d");

            // Process data based on the selected view
            const { labels, data } = processTimelineData(
                timelineData.value,
                timelineView.value
            );

            // Destroy existing chart if it exists
            if (timelineChart) {
                timelineChart.destroy();
            }

            timelineChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels,
                    datasets: [
                        {
                            label: "Responses",
                            data,
                            backgroundColor: "rgba(59, 130, 246, 0.2)",
                            borderColor: "rgba(59, 130, 246, 1)",
                            borderWidth: 2,
                            pointBackgroundColor: "rgba(59, 130, 246, 1)",
                            pointBorderColor: "#fff",
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.3,
                            fill: true,
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
                            backgroundColor: "rgba(0, 0, 0, 0.8)",
                            titleColor: "#fff",
                            bodyColor: "#fff",
                            borderColor: "rgba(255, 255, 255, 0.1)",
                            borderWidth: 1,
                            displayColors: false,
                            caretSize: 6,
                            callbacks: {
                                label: (context) => {
                                    return `${context.parsed.y} responses`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                maxTicksLimit: 8,
                                font: {
                                    size: 10,
                                },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                font: {
                                    size: 10,
                                },
                            },
                        },
                    },
                },
            });
        };

        const createCompletionChart = () => {
            if (!completionCanvas.value || !hasCompletionData.value) return;

            const ctx = completionCanvas.value.getContext("2d");

            // Get completion data
            const completed = props.statistics.completed_responses || 0;
            const inProgress =
                props.statistics.total_responses - completed || 0;

            // Destroy existing chart if it exists
            if (completionChart) {
                completionChart.destroy();
            }

            completionChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Completed", "In Progress"],
                    datasets: [
                        {
                            data: [completed, inProgress],
                            backgroundColor: [
                                "#10B981", // green for completed
                                "#9CA3AF", // gray for in-progress
                            ],
                            borderWidth: 0,
                            hoverOffset: 4,
                        },
                    ],
                },
                options: {
                    cutout: "75%",
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: "rgba(0, 0, 0, 0.8)",
                            titleColor: "#fff",
                            bodyColor: "#fff",
                            displayColors: false,
                            callbacks: {
                                label: (context) => {
                                    return `${context.label}: ${
                                        context.parsed
                                    } (${Math.round(
                                        (context.parsed * 100) /
                                            (completed + inProgress)
                                    )}%)`;
                                },
                            },
                        },
                    },
                },
            });
        };

        const processTimelineData = (responseData, view) => {
            // Default data for empty results
            if (!responseData || Object.keys(responseData).length === 0) {
                return { labels: [], data: [] };
            }

            // Sort dates
            const sortedDates = Object.keys(responseData).sort();

            // For daily view - just use the raw data
            if (view === "daily") {
                return {
                    labels: sortedDates.map((date) => formatDate(date)),
                    data: sortedDates.map((date) => responseData[date]),
                };
            }

            // For weekly or monthly, we need to aggregate
            const aggregatedData = {};

            if (view === "weekly") {
                sortedDates.forEach((date) => {
                    const weekKey = getWeekKey(date);
                    if (!aggregatedData[weekKey]) {
                        aggregatedData[weekKey] = 0;
                    }
                    aggregatedData[weekKey] += responseData[date];
                });
            } else if (view === "monthly") {
                sortedDates.forEach((date) => {
                    const monthKey = getMonthKey(date);
                    if (!aggregatedData[monthKey]) {
                        aggregatedData[monthKey] = 0;
                    }
                    aggregatedData[monthKey] += responseData[date];
                });
            }

            const aggKeys = Object.keys(aggregatedData).sort();

            return {
                labels: aggKeys,
                data: aggKeys.map((key) => aggregatedData[key]),
            };
        };

        const formatDate = (dateString) => {
            // Format date for display in chart (YYYY-MM-DD to DD MMM)
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString("en-GB", {
                    day: "2-digit",
                    month: "short",
                });
            } catch (e) {
                return dateString;
            }
        };

        const getWeekKey = (dateString) => {
            // Get week identifier (e.g. "2023-W01")
            try {
                const date = new Date(dateString);
                const firstDay = new Date(date.getFullYear(), 0, 1);
                const pastDays = Math.floor((date - firstDay) / 86400000);
                const weekNum = Math.ceil(
                    (pastDays + firstDay.getDay() + 1) / 7
                );
                return `${date.getFullYear()}-W${weekNum
                    .toString()
                    .padStart(2, "0")}`;
            } catch (e) {
                return dateString;
            }
        };

        const getMonthKey = (dateString) => {
            // Get month identifier (e.g. "Jan 2023")
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString("en-GB", {
                    month: "short",
                    year: "numeric",
                });
            } catch (e) {
                return dateString;
            }
        };

        // Lifecycle hooks and watchers
        onMounted(() => {
            createTimelineChart();
            createCompletionChart();
        });

        watch(
            () => props.statistics,
            () => {
                createTimelineChart();
                createCompletionChart();
            },
            { deep: true }
        );

        watch(timelineView, () => {
            createTimelineChart();
        });

        return {
            timelineCanvas,
            completionCanvas,
            timelineView,
            timelineData,
            hasTimelineData,
            hasCompletionData,
            completionPercentage,
            inProgressCount,
        };
    },
};
</script>

<style scoped>
.dashboard-overview {
    /* Additional styling if needed */
}

.chart-container {
    position: relative;
}

/* Chart animations */
.donut-chart-container {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
