<!-- 
* @component MultiChoiceVisualizer
* @description Visualizer untuk pertanyaan tipe pilihan ganda (radio, checkbox, dropdown).
* Menampilkan diagram batang horizontal yang menunjukkan distribusi jawaban.
-->
<template>
    <div class="multi-choice-visualizer" :data-question-id="question.id">
        <!-- Visualization Controls -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-2">
                <button
                    @click="viewType = 'bar'"
                    :class="[
                        'px-2 py-1 text-xs rounded',
                        viewType === 'bar'
                            ? 'bg-blue-100 text-blue-800 font-medium'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    Bar Chart
                </button>
                <button
                    @click="viewType = 'pie'"
                    :class="[
                        'px-2 py-1 text-xs rounded',
                        viewType === 'pie'
                            ? 'bg-blue-100 text-blue-800 font-medium'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    Pie Chart
                </button>
            </div>

            <div class="text-xs text-gray-500">
                {{ totalResponses }} total responses
            </div>
        </div>

        <!-- Bar Chart View -->
        <div v-if="viewType === 'bar'" class="bar-chart-container">
            <div
                v-for="(option, index) in sortedOptions"
                :key="index"
                class="bar-item mb-3"
            >
                <div class="flex justify-between mb-1">
                    <span
                        class="text-sm font-medium text-gray-700 truncate max-w-[70%]"
                        :title="option.label"
                    >
                        {{ option.label }}
                    </span>
                    <div class="text-sm text-gray-500">
                        <span class="font-medium text-gray-700">{{
                            option.count
                        }}</span>
                        <span class="ml-1 text-gray-500"
                            >({{ option.percentage }}%)</span
                        >
                    </div>
                </div>

                <div
                    class="w-full bg-gray-200 rounded-full h-4 overflow-hidden"
                >
                    <div
                        class="h-full rounded-full"
                        :style="{
                            width: `${option.percentage}%`,
                            backgroundColor: option.color,
                        }"
                    ></div>
                </div>
            </div>

            <!-- Other responses section if present -->
            <div
                v-if="hasOtherResponses"
                class="mt-6 p-4 bg-gray-50 rounded-lg"
            >
                <h4 class="text-sm font-medium text-gray-700 mb-2">
                    Other responses ({{ otherResponses.length }})
                </h4>
                <ul class="max-h-40 overflow-y-auto">
                    <li
                        v-for="(response, index) in otherResponses.slice(0, 10)"
                        :key="index"
                        class="text-sm text-gray-600 py-1 border-b border-gray-200 last:border-0"
                    >
                        {{ response }}
                    </li>
                </ul>
                <div
                    v-if="otherResponses.length > 10"
                    class="text-xs text-gray-500 mt-2"
                >
                    And {{ otherResponses.length - 10 }} more...
                </div>
            </div>
        </div>

        <!-- Pie Chart View -->
        <div v-else-if="viewType === 'pie'" class="pie-chart-container">
            <div class="flex justify-center mb-4">
                <div class="w-64 h-64 relative">
                    <canvas ref="pieCanvas"></canvas>
                </div>
            </div>

            <!-- Legend -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-4">
                <div
                    v-for="(option, index) in sortedOptions.slice(0, 8)"
                    :key="index"
                    class="flex items-center"
                >
                    <div
                        class="w-3 h-3 mr-2 rounded-sm"
                        :style="{ backgroundColor: option.color }"
                    ></div>
                    <span class="text-sm truncate" :title="option.label">
                        {{ option.label }} ({{ option.percentage }}%)
                    </span>
                </div>
                <div v-if="sortedOptions.length > 8" class="flex items-center">
                    <div class="w-3 h-3 mr-2 rounded-sm bg-gray-300"></div>
                    <span class="text-sm">
                        Others ({{ calculateOthersPercentage() }}%)
                    </span>
                </div>
            </div>
        </div>

        <!-- No data view -->
        <div v-else class="text-center py-12 text-gray-500">
            No visualization available for this question type.
        </div>
    </div>
</template>

<script>
import { ref, computed, watch, onMounted } from "vue";
import Chart from "chart.js/auto";

export default {
    name: "MultiChoiceVisualizer",

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

    emits: ["loading"],

    setup(props, { emit }) {
        // State
        const viewType = ref("bar");
        const pieCanvas = ref(null);
        let pieChart = null;

        // Emit initial loading state
        emit("loading", true);

        // Computed properties
        const totalResponses = computed(() => props.responses.length);

        const processedResponses = computed(() => {
            const isCheckbox = props.question.question_type === "checkbox";
            const responseCounts = {};
            const otherValues = [];

            props.responses.forEach((response) => {
                let value = response.answer_value;

                // Handle checkbox responses which may be arrays or JSON strings
                if (isCheckbox) {
                    try {
                        if (
                            typeof value === "string" &&
                            (value.startsWith("[") || value.startsWith("{"))
                        ) {
                            value = JSON.parse(value);
                        }

                        // Handle both array formats and individual values
                        if (Array.isArray(value)) {
                            value.forEach((v) => {
                                responseCounts[v] =
                                    (responseCounts[v] || 0) + 1;
                            });
                            return;
                        }
                    } catch (e) {
                        console.error("Error parsing checkbox value:", e);
                    }
                }

                // Handle "other" option in answer_data if present
                if (response.answer_data && response.answer_data.otherText) {
                    if (response.answer_data.value === "other") {
                        otherValues.push(response.answer_data.otherText);
                        responseCounts["Other"] =
                            (responseCounts["Other"] || 0) + 1;
                        return;
                    }
                }

                // Handle regular single-value responses
                responseCounts[value] = (responseCounts[value] || 0) + 1;
            });

            return { responseCounts, otherValues };
        });

        const otherResponses = computed(() => {
            return processedResponses.value.otherValues || [];
        });

        const hasOtherResponses = computed(() => {
            return otherResponses.value.length > 0;
        });

        const sortedOptions = computed(() => {
            const { responseCounts } = processedResponses.value;
            const total = totalResponses.value || 1; // Avoid division by zero

            // Get predefined options from question
            let options = props.question.options || [];

            // Convert to format needed for visualization
            const formattedOptions = options.map((option, index) => {
                const count = responseCounts[option.value] || 0;
                return {
                    label: option.text,
                    value: option.value,
                    count,
                    percentage: Math.round((count / total) * 100),
                    color: getColorForIndex(index),
                    order: index,
                };
            });

            // Add "Other" option if present in responses but not in predefined options
            if (
                responseCounts["Other"] &&
                !options.find((o) => o.value === "Other")
            ) {
                formattedOptions.push({
                    label: "Other",
                    value: "Other",
                    count: responseCounts["Other"],
                    percentage: Math.round(
                        (responseCounts["Other"] / total) * 100
                    ),
                    color: getColorForIndex(formattedOptions.length),
                    order: formattedOptions.length,
                });
            }

            // Look for values in responses that aren't in predefined options
            Object.keys(responseCounts).forEach((value) => {
                if (
                    value !== "Other" &&
                    !options.find((o) => o.value === value)
                ) {
                    formattedOptions.push({
                        label: value, // Use the value as the label
                        value,
                        count: responseCounts[value],
                        percentage: Math.round(
                            (responseCounts[value] / total) * 100
                        ),
                        color: getColorForIndex(formattedOptions.length),
                        order: formattedOptions.length,
                    });
                }
            });

            // Sort by count descending
            return formattedOptions.sort((a, b) => b.count - a.count);
        });

        // Helper functions
        const getColorForIndex = (index) => {
            const colors = [
                "#3B82F6", // blue
                "#10B981", // green
                "#F59E0B", // amber
                "#6366F1", // indigo
                "#EC4899", // pink
                "#8B5CF6", // purple
                "#14B8A6", // teal
                "#F97316", // orange
                "#06B6D4", // cyan
                "#EF4444", // red
            ];

            return colors[index % colors.length];
        };

        const calculateOthersPercentage = () => {
            if (sortedOptions.value.length <= 8) return 0;

            return sortedOptions.value
                .slice(8)
                .reduce((acc, option) => acc + option.percentage, 0);
        };

        const initPieChart = () => {
            if (!pieCanvas.value) return;

            const ctx = pieCanvas.value.getContext("2d");

            // Limit to top 8 options for pie chart readability
            const displayOptions = sortedOptions.value.slice(0, 8);

            // If more than 8 options, group the rest as "Others"
            const hasOthers = sortedOptions.value.length > 8;
            const othersValue = hasOthers
                ? sortedOptions.value
                      .slice(8)
                      .reduce((acc, option) => acc + option.count, 0)
                : 0;

            // Prepare data for chart
            const labels = displayOptions.map((option) => option.label);
            const data = displayOptions.map((option) => option.count);
            const colors = displayOptions.map((option) => option.color);

            if (hasOthers) {
                labels.push("Others");
                data.push(othersValue);
                colors.push("#9CA3AF"); // gray for Others
            }

            // Destroy previous chart if exists
            if (pieChart) {
                pieChart.destroy();
            }

            // Create new chart
            pieChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels,
                    datasets: [
                        {
                            data,
                            backgroundColor: colors,
                            borderWidth: 1,
                            borderColor: "#fff",
                            hoverOffset: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "50%",
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: "rgba(0, 0, 0, 0.8)",
                            titleColor: "#fff",
                            bodyColor: "#fff",
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 },
                            padding: 10,
                            displayColors: true,
                            callbacks: {
                                label: (context) => {
                                    const percentage = Math.round(
                                        (context.raw / totalResponses.value) *
                                            100
                                    );
                                    return `${context.label}: ${context.raw} (${percentage}%)`;
                                },
                            },
                        },
                    },
                },
            });
        };

        // Lifecycle hooks
        onMounted(() => {
            // No longer loading
            emit("loading", false);
        });

        // Watchers
        watch(viewType, (newType) => {
            if (newType === "pie") {
                // Initialize pie chart on next tick to ensure DOM is ready
                setTimeout(() => {
                    initPieChart();
                }, 0);
            }
        });

        watch(
            () => props.responses,
            () => {
                emit("loading", true);

                // Refresh pie chart if that view is active
                if (viewType.value === "pie") {
                    // Initialize pie chart on next tick
                    setTimeout(() => {
                        initPieChart();
                    }, 0);
                }

                emit("loading", false);
            },
            { deep: true }
        );

        return {
            viewType,
            pieCanvas,
            totalResponses,
            sortedOptions,
            otherResponses,
            hasOtherResponses,
            calculateOthersPercentage,
        };
    },
};
</script>

<style scoped>
.bar-chart-container,
.pie-chart-container {
    min-height: 200px;
}

.bar-item {
    animation: fadeInUp 0.5s ease-out;
    animation-fill-mode: both;
}

.bar-item:nth-child(1) {
    animation-delay: 0.05s;
}
.bar-item:nth-child(2) {
    animation-delay: 0.1s;
}
.bar-item:nth-child(3) {
    animation-delay: 0.15s;
}
.bar-item:nth-child(4) {
    animation-delay: 0.2s;
}
.bar-item:nth-child(5) {
    animation-delay: 0.25s;
}
.bar-item:nth-child(6) {
    animation-delay: 0.3s;
}
.bar-item:nth-child(7) {
    animation-delay: 0.35s;
}
.bar-item:nth-child(8) {
    animation-delay: 0.4s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
