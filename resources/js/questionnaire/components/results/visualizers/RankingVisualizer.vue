<!-- 
* @component RankingVisualizer
* @description Component for visualizing ranking question responses
-->
<template>
    <div data-question-id="ranking-visualizer-{{ question.id }}">
        <!-- Average ranking visualization -->
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-500 mb-3">
                Average Rankings
            </h4>
            <div class="rounded-lg border border-gray-200">
                <div
                    v-for="(item, index) in sortedItems"
                    :key="item.value"
                    class="flex items-center p-4 border-b last:border-b-0"
                >
                    <div
                        class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-800 rounded-full mr-4 font-bold"
                    >
                        {{ index + 1 }}
                    </div>
                    <div class="flex-grow">
                        <div class="flex items-center">
                            <h5 class="text-gray-900 font-medium">
                                {{ item.label }}
                            </h5>
                            <span class="ml-2 text-gray-500 text-sm">
                                (avg: {{ item.averageRank.toFixed(1) }})
                            </span>
                        </div>
                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                            <div
                                class="bg-blue-600 h-2.5 rounded-full"
                                :style="`width: ${getScorePercentage(
                                    item.averageRank
                                )}%`"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed rankings chart -->
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-500 mb-3">
                Detailed Rankings Distribution
            </h4>
            <div class="h-80">
                <canvas ref="distributionCanvas"></canvas>
            </div>
        </div>

        <!-- Top-ranked items -->
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-500 mb-3">
                Most Frequently Ranked #1
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div
                    v-for="(item, index) in topRankedItems.slice(0, 4)"
                    :key="`top-${index}`"
                    class="bg-gray-50 p-4 rounded-lg"
                >
                    <div class="flex items-center">
                        <div
                            class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3"
                        >
                            <svg
                                v-if="index === 0"
                                class="h-6 w-6 text-yellow-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                                />
                            </svg>
                            <span v-else class="font-bold text-blue-800">{{
                                index + 1
                            }}</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ item.label }}
                            </div>
                            <div class="text-sm text-gray-500">
                                Ranked #1 by {{ item.firstRankCount }}
                                <span v-if="responses.length > 0">
                                    ({{
                                        Math.round(
                                            (item.firstRankCount /
                                                responses.length) *
                                                100
                                        )
                                    }}%)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed responses -->
        <div class="mt-6" v-if="responses.length > 0">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-sm font-medium text-gray-500">All Rankings</h4>
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
                <div
                    v-for="(response, responseIndex) in responses"
                    :key="`response-${responseIndex}`"
                    class="mb-4 bg-white shadow-sm rounded-lg overflow-hidden"
                >
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h5 class="text-sm font-medium text-gray-700">
                                Response #{{
                                    response.response_id || responseIndex + 1
                                }}
                            </h5>
                            <span class="text-xs text-gray-500">{{
                                formatTimestamp(response.created_at)
                            }}</span>
                        </div>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-2">
                            <li
                                v-for="(
                                    rank, rankIndex
                                ) in formatResponseRanking(
                                    response.answer_value
                                )"
                                :key="`rank-${rankIndex}`"
                                class="flex items-center"
                            >
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 font-medium text-xs mr-3"
                                >
                                    {{ rankIndex + 1 }}
                                </span>
                                <span class="text-sm text-gray-900">{{
                                    getLabelForOption(rank)
                                }}</span>
                            </li>
                        </ul>
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
    name: "RankingVisualizer",

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
        const distributionCanvas = ref(null);
        const chart = ref(null);
        const showResponses = ref(false);

        // Get available options from question settings
        const rankingOptions = computed(() => {
            try {
                // Try to get options from question settings
                if (
                    props.question.settings &&
                    props.question.settings.options
                ) {
                    return props.question.settings.options;
                }

                // Fallback - extract options from responses
                const optionsSet = new Set();

                props.responses.forEach((response) => {
                    const ranking = formatResponseRanking(
                        response.answer_value
                    );
                    if (Array.isArray(ranking)) {
                        ranking.forEach((option) => {
                            if (option) optionsSet.add(option);
                        });
                    }
                });

                return Array.from(optionsSet).map((option) => ({
                    value: option,
                    label: option,
                }));
            } catch (e) {
                console.error("Error parsing ranking options:", e);
                return [];
            }
        });

        // Process response data for analysis
        const processedData = computed(() => {
            const optionsMap = {};
            const firstRankCounts = {};
            const distributionData = {};

            // Initialize maps
            rankingOptions.value.forEach((option) => {
                const value = option.value || option;
                optionsMap[value] = {
                    label: option.label || option,
                    value: value,
                    totalRank: 0,
                    responseCount: 0,
                    firstRankCount: 0,
                };

                firstRankCounts[value] = 0;
                distributionData[value] = Array(
                    rankingOptions.value.length
                ).fill(0);
            });

            // Process each response
            props.responses.forEach((response) => {
                const ranking = formatResponseRanking(response.answer_value);

                if (Array.isArray(ranking)) {
                    // Track first rank counts
                    if (ranking.length > 0 && ranking[0] in firstRankCounts) {
                        firstRankCounts[ranking[0]]++;
                    }

                    // Process each ranked option
                    ranking.forEach((value, rank) => {
                        if (value in optionsMap) {
                            optionsMap[value].totalRank += rank + 1;
                            optionsMap[value].responseCount++;

                            // Update distribution data
                            if (
                                distributionData[value] &&
                                distributionData[value][rank] !== undefined
                            ) {
                                distributionData[value][rank]++;
                            }
                        }
                    });
                }
            });

            // Calculate average rankings
            Object.keys(optionsMap).forEach((key) => {
                const option = optionsMap[key];
                option.averageRank =
                    option.responseCount > 0
                        ? option.totalRank / option.responseCount
                        : rankingOptions.value.length;
                option.firstRankCount = firstRankCounts[key] || 0;
            });

            return {
                options: optionsMap,
                distribution: distributionData,
            };
        });

        // Get sorted items by average rank
        const sortedItems = computed(() => {
            return Object.values(processedData.value.options).sort(
                (a, b) => a.averageRank - b.averageRank
            );
        });

        // Get items sorted by frequency of being ranked #1
        const topRankedItems = computed(() => {
            return Object.values(processedData.value.options).sort(
                (a, b) => b.firstRankCount - a.firstRankCount
            );
        });

        // Format ranking data from response
        const formatResponseRanking = (answerValue) => {
            if (!answerValue) return [];

            try {
                // If it's already an array
                if (Array.isArray(answerValue)) {
                    return answerValue;
                }

                // If it's a string, try to parse as JSON
                if (typeof answerValue === "string") {
                    try {
                        const parsed = JSON.parse(answerValue);
                        if (Array.isArray(parsed)) {
                            return parsed;
                        }
                    } catch (e) {
                        // Not JSON, might be comma-separated
                        return answerValue
                            .split(",")
                            .map((item) => item.trim());
                    }
                }

                // If it's an object with numbered keys
                if (typeof answerValue === "object") {
                    const result = [];
                    const keys = Object.keys(answerValue).sort(
                        (a, b) => parseInt(a) - parseInt(b)
                    );

                    keys.forEach((key) => {
                        result.push(answerValue[key]);
                    });

                    return result;
                }
            } catch (e) {
                console.error("Error formatting ranking:", e);
            }

            return [];
        };

        // Get label for an option value
        const getLabelForOption = (value) => {
            const option = rankingOptions.value.find(
                (opt) => (opt.value || opt) === value
            );

            return option ? option.label || option : value;
        };

        // Get percentage for progress bar based on average rank
        const getScorePercentage = (averageRank) => {
            const maxRank = rankingOptions.value.length;
            // Invert so lower rank = higher percentage
            const invertedPercentage =
                ((maxRank - averageRank + 1) / maxRank) * 100;
            return Math.max(5, Math.min(100, invertedPercentage));
        };

        // Format timestamp for display
        const formatTimestamp = (timestamp) => {
            if (!timestamp) return "N/A";
            return new Date(timestamp).toLocaleString();
        };

        // Initialize distribution chart
        const initDistributionChart = () => {
            if (!distributionCanvas.value) return;

            const ctx = distributionCanvas.value.getContext("2d");

            // Destroy existing chart if it exists
            if (chart.value) {
                chart.value.destroy();
            }

            const distribution = processedData.value.distribution;
            if (!distribution || Object.keys(distribution).length === 0) return;

            // Prepare data for stacked bar chart
            const labels = rankingOptions.value.map(
                (option, index) => `Rank ${index + 1}`
            );

            const datasets = [];
            const sortedOptions = sortedItems.value;

            // Colors for datasets
            const colors = [
                "rgba(59, 130, 246, 0.7)", // Blue
                "rgba(16, 185, 129, 0.7)", // Green
                "rgba(239, 68, 68, 0.7)", // Red
                "rgba(245, 158, 11, 0.7)", // Yellow
                "rgba(139, 92, 246, 0.7)", // Purple
                "rgba(236, 72, 153, 0.7)", // Pink
                "rgba(6, 182, 212, 0.7)", // Cyan
                "rgba(75, 85, 99, 0.7)", // Gray
            ];

            // Create dataset for each option
            sortedOptions.forEach((item, index) => {
                const colorIndex = index % colors.length;

                datasets.push({
                    label: item.label,
                    data:
                        distribution[item.value] ||
                        Array(labels.length).fill(0),
                    backgroundColor: colors[colorIndex],
                    borderColor: colors[colorIndex].replace("0.7", "1"),
                    borderWidth: 1,
                });
            });

            // Create new chart
            chart.value = new Chart(ctx, {
                type: "bar",
                data: {
                    labels,
                    datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: "Rank Position",
                            },
                        },
                        y: {
                            stacked: true,
                            title: {
                                display: true,
                                text: "Number of Responses",
                            },
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.dataset.label || "";
                                    const value = context.raw || 0;
                                    const total = responses.length;
                                    const percentage = total
                                        ? Math.round((value / total) * 100)
                                        : 0;
                                    return `${label}: ${value} (${percentage}% of responses)`;
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
                initDistributionChart();
            },
            { deep: true }
        );

        // Initialize on mount
        onMounted(() => {
            emit("loading", true);
            setTimeout(() => {
                initDistributionChart();
                emit("loading", false);
            }, 200);
        });

        return {
            distributionCanvas,
            showResponses,
            rankingOptions,
            sortedItems,
            topRankedItems,
            formatResponseRanking,
            getLabelForOption,
            getScorePercentage,
            formatTimestamp,
            toggleResponses,
        };
    },
};
</script>

<style scoped>
/* Component-specific styles */
</style>
