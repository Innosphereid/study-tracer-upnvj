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
                <!-- Back Button -->
                <BackButton to="/kuesioner" text="Kembali" />

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
                                <img
                                    src="/images/logo/pdf-file.svg"
                                    alt="PDF"
                                    class="mr-3 h-5 w-5 text-gray-500"
                                />
                                PDF
                            </a>
                            <a
                                @click="handleExport('excel')"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                            >
                                <img
                                    src="/images/logo/excel.svg"
                                    alt="Excel"
                                    class="mr-3 h-5 w-5 text-gray-500"
                                />
                                Excel
                            </a>
                            <a
                                @click="handleExport('csv')"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                            >
                                <img
                                    src="/images/logo/csv-file-format-extension.svg"
                                    alt="CSV"
                                    class="mr-3 h-5 w-5 text-gray-500"
                                />
                                CSV
                            </a>
                        </div>
                    </div>
                </div>
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

        <!-- Export Notification Modal -->
        <ExportNotificationModal
            v-model:show="showExportModal"
            :export-type="currentExportType"
            @feedback="handleExportFeedback"
        />
    </div>
</template>

<script>
import { ref, computed } from "vue";
import ExportNotificationModal from "../../components/ui/ExportNotificationModal.vue";
import BackButton from "../../components/ui/BackButton.vue";

export default {
    name: "HeaderSection",

    components: {
        ExportNotificationModal,
        BackButton,
    },

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
        const showExportModal = ref(false);
        const currentExportType = ref("pdf");

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

        /**
         * Handle export request for different formats
         * @param {string} format - The export format (pdf, excel, csv)
         */
        const handleExport = (format) => {
            // In a real app, you would trigger an export based on the format
            console.log(`Exporting in ${format} format`);

            // Check if we have a valid questionnaire_id
            const questionnaireId = props.statistics?.questionnaire_id;

            if (!questionnaireId) {
                console.error(
                    "Questionnaire ID is undefined. Cannot export results."
                );
                alert(
                    "Error: Unable to identify the questionnaire for export. Please try again later."
                );
                return;
            }

            // Generate the export URL
            const exportUrl = `/kuesioner/${questionnaireId}/results/export/${format}`;

            // Redirect for any export format since all are now supported
            window.location.href = exportUrl;

            // Hide dropdown after export selection
            if (this.$refs && this.$refs.exportMenu) {
                this.$refs.exportMenu.classList.add("hidden");
            }
        };

        /**
         * Handle feedback request for export feature
         * @param {string} exportType - The type of export requested
         */
        const handleExportFeedback = (exportType) => {
            console.log(`User requested ${exportType} export feature`);
            // Here you could implement logic to track feature requests
            // or redirect to a feedback form
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
            handleExportFeedback,
            handlePrint,
            handleShare,
            showExportModal,
            currentExportType,
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
