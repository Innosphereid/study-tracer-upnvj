<!-- 
* @component FileUploadVisualizer
* @description Component for visualizing file upload response data
-->
<template>
    <div data-question-id="file-visualizer-{{ question.id }}">
        <!-- Summary statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Total Files
                </h4>
                <p class="text-xl font-bold">{{ stats.totalFiles }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Most Common Type
                </h4>
                <p class="text-xl font-bold">
                    {{ stats.mostCommonType || "N/A" }}
                </p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-1">
                    Average Size
                </h4>
                <p class="text-xl font-bold">{{ stats.averageSize }}</p>
            </div>
        </div>

        <!-- Type distribution chart -->
        <div class="mb-6" v-if="Object.keys(fileTypes).length > 0">
            <h4 class="text-sm font-medium text-gray-500 mb-3">File Types</h4>
            <div class="h-60">
                <canvas ref="typeCanvas"></canvas>
            </div>
        </div>

        <!-- File list -->
        <div class="mt-4">
            <h4 class="text-sm font-medium text-gray-500 mb-3">
                Uploaded Files
            </h4>

            <div v-if="isLoading" class="flex justify-center items-center py-8">
                <div class="spinner"></div>
            </div>

            <div
                v-else-if="files.length === 0"
                class="bg-gray-50 rounded-lg p-8 text-center"
            >
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    No files uploaded
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    No one has uploaded files for this question yet.
                </p>
            </div>

            <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <li
                        v-for="(file, index) in files"
                        :key="index"
                        class="px-4 py-4 sm:px-6"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span
                                        class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-blue-100 text-blue-600"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ file.filename }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ formatFileType(file.type) }} ·
                                        {{ formatFileSize(file.size) }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <a
                                    v-if="file.url"
                                    :href="file.url"
                                    target="_blank"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg
                                        class="-ml-0.5 mr-2 h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                        />
                                    </svg>
                                    Download
                                </a>
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            <span
                                >Uploaded by Response #{{
                                    file.response_id
                                }}</span
                            >
                            <span class="ml-4">{{
                                formatTimestamp(file.uploaded_at)
                            }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from "vue";
import Chart from "chart.js/auto";

export default {
    name: "FileUploadVisualizer",

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
        const typeCanvas = ref(null);
        const chart = ref(null);
        const isLoading = ref(true);

        // Parse file information from responses
        const files = computed(() => {
            const fileList = [];

            props.responses.forEach((response) => {
                try {
                    // Check if answer_value contains file information
                    if (!response.answer_value) return;

                    // Try to parse as JSON if it's a string
                    let fileData = response.answer_value;
                    if (typeof fileData === "string") {
                        try {
                            fileData = JSON.parse(fileData);
                        } catch (e) {
                            // If not JSON, try to extract file information from string
                            fileData = {
                                filename: fileData,
                                type: guessFileType(fileData),
                                size: null,
                                url: null,
                            };
                        }
                    }

                    // If we have a file object or array
                    if (Array.isArray(fileData)) {
                        // Handle multiple files
                        fileData.forEach((file) => {
                            fileList.push({
                                ...file,
                                response_id: response.response_id,
                                uploaded_at: response.created_at,
                            });
                        });
                    } else if (fileData && typeof fileData === "object") {
                        // Handle single file
                        fileList.push({
                            ...fileData,
                            response_id: response.response_id,
                            uploaded_at: response.created_at,
                        });
                    }
                } catch (e) {
                    console.error("Error parsing file data:", e);
                }
            });

            return fileList;
        });

        // Group files by type
        const fileTypes = computed(() => {
            const types = {};

            files.value.forEach((file) => {
                const type =
                    file.type || guessFileType(file.filename) || "unknown";
                types[type] = (types[type] || 0) + 1;
            });

            return types;
        });

        // Calculate statistics
        const stats = computed(() => {
            const totalFiles = files.value.length;

            if (totalFiles === 0) {
                return {
                    totalFiles: 0,
                    mostCommonType: null,
                    averageSize: "N/A",
                };
            }

            // Find most common type
            let maxCount = 0;
            let mostCommonType = null;

            Object.entries(fileTypes.value).forEach(([type, count]) => {
                if (count > maxCount) {
                    maxCount = count;
                    mostCommonType = formatFileType(type);
                }
            });

            // Calculate average size if available
            let totalSize = 0;
            let filesWithSize = 0;

            files.value.forEach((file) => {
                if (file.size && !isNaN(parseInt(file.size, 10))) {
                    totalSize += parseInt(file.size, 10);
                    filesWithSize++;
                }
            });

            const averageSize =
                filesWithSize > 0
                    ? formatFileSize(totalSize / filesWithSize)
                    : "N/A";

            return {
                totalFiles,
                mostCommonType,
                averageSize,
            };
        });

        // Guess file type from filename
        const guessFileType = (filename) => {
            if (!filename) return "unknown";

            const extension = filename.split(".").pop().toLowerCase();

            // Map common extensions to general types
            const typeMap = {
                pdf: "pdf",
                doc: "word",
                docx: "word",
                xls: "excel",
                xlsx: "excel",
                ppt: "powerpoint",
                pptx: "powerpoint",
                jpg: "image",
                jpeg: "image",
                png: "image",
                gif: "image",
                svg: "image",
                mp3: "audio",
                wav: "audio",
                mp4: "video",
                mov: "video",
                avi: "video",
                zip: "archive",
                rar: "archive",
                "7z": "archive",
                txt: "text",
                csv: "csv",
            };

            return typeMap[extension] || extension || "unknown";
        };

        // Format file type for display
        const formatFileType = (type) => {
            if (!type) return "Unknown Type";

            const displayNames = {
                pdf: "PDF Document",
                word: "Word Document",
                excel: "Excel Spreadsheet",
                powerpoint: "PowerPoint Presentation",
                image: "Image",
                audio: "Audio",
                video: "Video",
                archive: "Archive",
                text: "Text File",
                csv: "CSV File",
            };

            return displayNames[type.toLowerCase()] || type.toUpperCase();
        };

        // Format file size for display
        const formatFileSize = (size) => {
            if (!size || isNaN(size)) return "Unknown Size";

            const units = ["B", "KB", "MB", "GB", "TB"];
            let fileSize = size;
            let unitIndex = 0;

            while (fileSize >= 1024 && unitIndex < units.length - 1) {
                fileSize /= 1024;
                unitIndex++;
            }

            return `${fileSize.toFixed(1)} ${units[unitIndex]}`;
        };

        // Format timestamp for display
        const formatTimestamp = (timestamp) => {
            if (!timestamp) return "N/A";
            return new Date(timestamp).toLocaleString();
        };

        // Initialize type chart
        const initTypeChart = () => {
            if (!typeCanvas.value) return;

            const ctx = typeCanvas.value.getContext("2d");

            // Destroy existing chart if it exists
            if (chart.value) {
                chart.value.destroy();
            }

            // Prepare data for pie chart
            const types = Object.keys(fileTypes.value);
            const counts = Object.values(fileTypes.value);

            if (types.length === 0) return;

            // Color mapping for file types
            const colorMap = {
                pdf: "rgba(239, 68, 68, 0.7)", // Red
                word: "rgba(59, 130, 246, 0.7)", // Blue
                excel: "rgba(16, 185, 129, 0.7)", // Green
                powerpoint: "rgba(245, 158, 11, 0.7)", // Yellow
                image: "rgba(139, 92, 246, 0.7)", // Purple
                audio: "rgba(236, 72, 153, 0.7)", // Pink
                video: "rgba(6, 182, 212, 0.7)", // Cyan
                archive: "rgba(75, 85, 99, 0.7)", // Gray
                text: "rgba(249, 115, 22, 0.7)", // Orange
                csv: "rgba(168, 85, 247, 0.7)", // Violet
            };

            const colors = types.map(
                (type) =>
                    colorMap[type.toLowerCase()] || "rgba(209, 213, 219, 0.7)"
            );

            // Create new chart
            chart.value = new Chart(ctx, {
                type: "pie",
                data: {
                    labels: types.map(formatFileType),
                    datasets: [
                        {
                            data: counts,
                            backgroundColor: colors,
                            borderColor: colors.map((color) =>
                                color.replace("0.7", "1")
                            ),
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                boxWidth: 15,
                                padding: 15,
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || "";
                                    const value = context.raw || 0;
                                    const total = counts.reduce(
                                        (a, b) => a + b,
                                        0
                                    );
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

        // Watch for changes in responses
        watch(
            () => props.responses,
            () => {
                initTypeChart();
            },
            { deep: true }
        );

        // Initialize on mount
        onMounted(() => {
            emit("loading", true);
            setTimeout(() => {
                initTypeChart();
                isLoading.value = false;
                emit("loading", false);
            }, 300);
        });

        return {
            typeCanvas,
            isLoading,
            files,
            fileTypes,
            stats,
            formatFileType,
            formatFileSize,
            formatTimestamp,
        };
    },
};
</script>

<style scoped>
.spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border-left-color: #3b82f6;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
