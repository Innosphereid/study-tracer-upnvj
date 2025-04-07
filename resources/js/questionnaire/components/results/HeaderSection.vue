<!-- 
* @component HeaderSection
* @description Komponen header untuk halaman results yang menampilkan judul kuesioner,
* ringkasan respons, metadata, dan tombol-tombol aksi.
-->
<template>
    <div class="header-section bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start">
            <!-- Title and Metadata -->
            <div class="flex-grow">
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ questionnaireTitle }}
                </h1>
                <h2 class="text-lg text-gray-700 mt-1 mb-4">
                    Results Overview
                </h2>

                <!-- Stats Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                    <div class="stat-item">
                        <div class="text-sm font-medium text-gray-500">
                            Total responses
                        </div>
                        <div class="text-xl font-semibold text-gray-900">
                            {{ statistics.total_responses || 0 }}
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="text-sm font-medium text-gray-500">
                            Completion rate
                        </div>
                        <div class="text-xl font-semibold text-gray-900">
                            {{ completionRate }}%
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="text-sm font-medium text-gray-500">
                            Avg. time
                        </div>
                        <div class="text-xl font-semibold text-gray-900">
                            {{ formattedAverageTime }}
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="text-sm font-medium text-gray-500">
                            Response period
                        </div>
                        <div class="text-xl font-semibold text-gray-900">
                            {{ responsePeriod }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons mt-4 md:mt-0 flex gap-3">
                <!-- Export Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button
                        @click="
                            $event.preventDefault();
                            $refs.exportMenu.classList.toggle('hidden');
                        "
                        class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg
                            class="mr-2 h-4 w-4 text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        Export
                        <svg
                            class="ml-1 h-4 w-4 text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>

                    <div
                        ref="exportMenu"
                        class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10"
                    >
                        <div class="py-1">
                            <a
                                @click="handleExport('pdf')"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                            >
                                <svg
                                    class="mr-3 h-5 w-5 text-gray-500"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                PDF
                            </a>
                            <a
                                @click="handleExport('excel')"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                            >
                                <svg
                                    class="mr-3 h-5 w-5 text-gray-500"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 2v8h8V6H6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Excel
                            </a>
                            <a
                                @click="handleExport('csv')"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                            >
                                <svg
                                    class="mr-3 h-5 w-5 text-gray-500"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 2a1 1 0 00-1 1v6a1 1 0 001 1h6a1 1 0 001-1V7a1 1 0 00-1-1H6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                CSV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Print Button -->
                <button
                    @click="handlePrint"
                    class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg
                        class="mr-2 h-4 w-4 text-gray-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                        />
                    </svg>
                    Print
                </button>

                <!-- Share Button -->
                <button
                    @click="handleShare"
                    class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg
                        class="mr-2 h-4 w-4 text-gray-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                        />
                    </svg>
                    Share
                </button>
            </div>
        </div>

        <!-- Date Filter Controls -->
        <div class="date-filter mt-6 flex flex-wrap gap-4">
            <div class="filter-buttons flex gap-2">
                <button
                    @click="setPeriod('all')"
                    :class="[
                        'px-3 py-1 text-sm rounded-md',
                        selectedPeriodType === 'all'
                            ? 'bg-blue-100 text-blue-800 font-medium'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    All time
                </button>
                <button
                    @click="setPeriod('week')"
                    :class="[
                        'px-3 py-1 text-sm rounded-md',
                        selectedPeriodType === 'week'
                            ? 'bg-blue-100 text-blue-800 font-medium'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    Last 7 days
                </button>
                <button
                    @click="setPeriod('month')"
                    :class="[
                        'px-3 py-1 text-sm rounded-md',
                        selectedPeriodType === 'month'
                            ? 'bg-blue-100 text-blue-800 font-medium'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    Last 30 days
                </button>
                <button
                    @click="toggleCustomDate"
                    :class="[
                        'px-3 py-1 text-sm rounded-md',
                        selectedPeriodType === 'custom'
                            ? 'bg-blue-100 text-blue-800 font-medium'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    Custom
                </button>
            </div>

            <!-- Custom Date Range -->
            <div
                v-if="showCustomDate"
                class="custom-date-range flex gap-2 items-center"
            >
                <input
                    type="date"
                    v-model="customStartDate"
                    class="form-input text-sm border-gray-300 rounded-md"
                />
                <span class="text-gray-500">to</span>
                <input
                    type="date"
                    v-model="customEndDate"
                    class="form-input text-sm border-gray-300 rounded-md"
                />
                <button
                    @click="applyCustomDate"
                    class="px-3 py-1 bg-blue-500 text-white text-sm rounded-md hover:bg-blue-600"
                >
                    Apply
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from "vue";

export default {
    name: "HeaderSection",

    props: {
        questionnaireTitle: {
            type: String,
            required: true,
        },
        statistics: {
            type: Object,
            required: true,
            default: () => ({}),
        },
    },

    emits: ["periodChanged"],

    setup(props, { emit }) {
        const showCustomDate = ref(false);
        const customStartDate = ref("");
        const customEndDate = ref("");
        const selectedPeriodType = ref("all");

        // Computed properties
        const completionRate = computed(() => {
            return props.statistics.completion_rate !== undefined
                ? props.statistics.completion_rate.toFixed(1)
                : "0.0";
        });

        const formattedAverageTime = computed(() => {
            const seconds = props.statistics.average_time_seconds || 0;

            if (seconds < 60) {
                return `${seconds}s`;
            }

            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;

            if (minutes < 60) {
                return `${minutes}m ${remainingSeconds}s`;
            }

            const hours = Math.floor(minutes / 60);
            const remainingMinutes = minutes % 60;

            return `${hours}h ${remainingMinutes}m`;
        });

        const responsePeriod = computed(() => {
            // In a real app, you would calculate this from the actual response data
            // For now, we'll return a placeholder
            return "All time";
        });

        // Methods
        const setPeriod = (periodType) => {
            selectedPeriodType.value = periodType;
            showCustomDate.value = periodType === "custom";

            let period = { type: periodType, start: null, end: null };

            if (periodType === "week") {
                const start = new Date();
                start.setDate(start.getDate() - 7);
                period.start = formatDate(start);
                period.end = formatDate(new Date());
            } else if (periodType === "month") {
                const start = new Date();
                start.setDate(start.getDate() - 30);
                period.start = formatDate(start);
                period.end = formatDate(new Date());
            }

            emit("periodChanged", period);
        };

        const toggleCustomDate = () => {
            showCustomDate.value = !showCustomDate.value;
            if (showCustomDate.value) {
                selectedPeriodType.value = "custom";

                // Set default dates for custom range (last 7 days)
                const end = new Date();
                const start = new Date();
                start.setDate(start.getDate() - 7);

                customStartDate.value = formatDate(start);
                customEndDate.value = formatDate(end);
            }
        };

        const applyCustomDate = () => {
            if (!customStartDate.value || !customEndDate.value) {
                return;
            }

            selectedPeriodType.value = "custom";
            emit("periodChanged", {
                type: "custom",
                start: customStartDate.value,
                end: customEndDate.value,
            });
        };

        const formatDate = (date) => {
            return date.toISOString().split("T")[0];
        };

        const handleExport = (format) => {
            // In a real app, you would trigger an export based on the format
            console.log(`Exporting in ${format} format`);

            // Example of redirecting to export route
            if (format === "csv") {
                window.location.href = `/kuesioner/${props.statistics.questionnaire_id}/responses/export`;
            } else {
                alert(
                    `Export to ${format.toUpperCase()} is not implemented yet.`
                );
            }

            // Hide dropdown after export
            if (this.$refs && this.$refs.exportMenu) {
                this.$refs.exportMenu.classList.add("hidden");
            }
        };

        const handlePrint = () => {
            window.print();
        };

        const handleShare = () => {
            const url = window.location.href;

            if (navigator.share) {
                navigator
                    .share({
                        title: `Results for ${props.questionnaireTitle}`,
                        text: `Check out the results for ${props.questionnaireTitle}`,
                        url: url,
                    })
                    .catch((error) => console.log("Error sharing:", error));
            } else if (navigator.clipboard) {
                navigator.clipboard
                    .writeText(url)
                    .then(() => {
                        alert("Link copied to clipboard");
                    })
                    .catch((err) => {
                        console.error("Failed to copy: ", err);
                        alert("Failed to copy link");
                    });
            } else {
                // Fallback for older browsers
                const textarea = document.createElement("textarea");
                textarea.value = url;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand("copy");
                document.body.removeChild(textarea);
                alert("Link copied to clipboard");
            }
        };

        return {
            showCustomDate,
            customStartDate,
            customEndDate,
            selectedPeriodType,
            completionRate,
            formattedAverageTime,
            responsePeriod,
            setPeriod,
            toggleCustomDate,
            applyCustomDate,
            handleExport,
            handlePrint,
            handleShare,
        };
    },
};
</script>

<style scoped>
.header-section {
    border-top: 4px solid #3b82f6;
}

.stat-item {
    padding: 0.75rem;
    border-radius: 0.5rem;
    background-color: #f9fafb;
}

.action-buttons button {
    transition: all 0.2s;
}

.action-buttons button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

@media print {
    .date-filter,
    .action-buttons {
        display: none;
    }
}
</style>
