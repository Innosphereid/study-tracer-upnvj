<!-- 
* @component QuestionResultCard
* @description Komponen yang menampilkan kartu hasil pertanyaan individual dengan visualisasi
* yang sesuai dengan tipe pertanyaan.
-->
<template>
    <div
        class="question-result-card bg-white rounded-lg shadow-sm mb-6 overflow-hidden"
    >
        <!-- Question Header -->
        <div class="p-5 border-b border-gray-100">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span
                            class="text-xs font-medium bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full"
                        >
                            {{ formatQuestionType }}
                        </span>
                        <span class="text-xs text-gray-500"
                            >{{ responses.length }} responses</span
                        >
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ question.title }}
                    </h3>
                    <p
                        v-if="question.description"
                        class="mt-1 text-sm text-gray-500"
                    >
                        {{ question.description }}
                    </p>
                </div>

                <!-- Action dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button
                        @click="
                            $event.preventDefault();
                            $refs.actionMenu.classList.toggle('hidden');
                        "
                        class="inline-flex items-center p-1.5 rounded-md text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                            />
                        </svg>
                    </button>

                    <div
                        ref="actionMenu"
                        class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10"
                    >
                        <div class="py-1">
                            <a
                                @click="downloadCSV"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                            >
                                <svg
                                    class="mr-3 h-5 w-5 text-gray-500"
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
                                Download CSV
                            </a>
                            <a
                                @click="printChart"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                            >
                                <svg
                                    class="mr-3 h-5 w-5 text-gray-500"
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
                                Print Chart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Visualization -->
        <div class="p-5">
            <!-- Show appropriate visualization based on question type -->
            <component
                :is="visualizationComponent"
                :question="question"
                :responses="responses"
                @loading="updateLoadingState"
                :key="question.id"
            />

            <!-- Loading state -->
            <div
                v-if="isLoading"
                class="flex justify-center items-center py-12"
            >
                <div class="spinner"></div>
            </div>

            <!-- Empty state -->
            <div
                v-if="!isLoading && !hasResponses"
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
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    No responses
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    This question has no responses yet.
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import MultiChoiceVisualizer from "./visualizers/MultiChoiceVisualizer.vue";
import TextResponseVisualizer from "./visualizers/TextResponseVisualizer.vue";
import NumericVisualizer from "./visualizers/NumericVisualizer.vue";
import DateResponseVisualizer from "./visualizers/DateResponseVisualizer.vue";
import YesNoVisualizer from "./visualizers/YesNoVisualizer.vue";
import LikertScaleVisualizer from "./visualizers/LikertScaleVisualizer.vue";
import FileUploadVisualizer from "./visualizers/FileUploadVisualizer.vue";
import MatrixResponseVisualizer from "./visualizers/MatrixResponseVisualizer.vue";
import RankingVisualizer from "./visualizers/RankingVisualizer.vue";

export default {
    name: "QuestionResultCard",

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

    components: {
        MultiChoiceVisualizer,
        TextResponseVisualizer,
        NumericVisualizer,
        DateResponseVisualizer,
        YesNoVisualizer,
        LikertScaleVisualizer,
        FileUploadVisualizer,
        MatrixResponseVisualizer,
        RankingVisualizer,
    },

    setup(props) {
        const isLoading = ref(false);

        // Computed properties
        const hasResponses = computed(() => {
            return props.responses && props.responses.length > 0;
        });

        const formatQuestionType = computed(() => {
            const typeMap = {
                text: "Short Text",
                textarea: "Long Text",
                radio: "Single Choice",
                checkbox: "Multiple Choice",
                dropdown: "Dropdown",
                rating: "Rating",
                date: "Date",
                file: "File Upload",
                matrix: "Matrix",
                likert: "Likert Scale",
                ranking: "Ranking",
                "yes-no": "Yes/No",
            };

            return (
                typeMap[props.question.question_type] ||
                props.question.question_type
            );
        });

        const visualizationComponent = computed(() => {
            const typeComponentMap = {
                text: "TextResponseVisualizer",
                textarea: "TextResponseVisualizer",
                radio: "MultiChoiceVisualizer",
                checkbox: "MultiChoiceVisualizer",
                dropdown: "MultiChoiceVisualizer",
                rating: "NumericVisualizer",
                date: "DateResponseVisualizer",
                file: "FileUploadVisualizer",
                matrix: "MatrixResponseVisualizer",
                likert: "LikertScaleVisualizer",
                ranking: "RankingVisualizer",
                "yes-no": "YesNoVisualizer",
            };

            return (
                typeComponentMap[props.question.question_type] ||
                "TextResponseVisualizer"
            );
        });

        // Methods
        const updateLoadingState = (loading) => {
            isLoading.value = loading;
        };

        const downloadCSV = () => {
            // Prepare data for CSV
            let csvContent = "data:text/csv;charset=utf-8,";

            // Add headers
            csvContent += "Response ID,Answer,Timestamp\n";

            // Add rows
            props.responses.forEach((response) => {
                const responseId = response.response_id || "N/A";
                const answerValue = response.answer_value || "N/A";
                const timestamp = response.created_at
                    ? new Date(response.created_at).toLocaleString()
                    : "N/A";

                csvContent += `${responseId},"${answerValue.replace(
                    /"/g,
                    '""'
                )}",${timestamp}\n`;
            });

            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute(
                "download",
                `question_${props.question.id}_responses.csv`
            );
            document.body.appendChild(link);

            // Trigger download
            link.click();
            document.body.removeChild(link);
        };

        const printChart = () => {
            const cardElement = document.querySelector(
                `[data-question-id="${props.question.id}"]`
            );
            if (cardElement) {
                const printWindow = window.open("", "_blank");

                if (printWindow) {
                    printWindow.document.write(`
            <html>
            <head>
              <title>Chart for ${props.question.title}</title>
              <link rel="stylesheet" href="${
                  window.location.origin
              }/css/app.css">
              <style>
                body { padding: 20px; }
                .print-header { margin-bottom: 20px; }
                .print-header h1 { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
                .print-header p { color: #666; font-size: 14px; }
              </style>
            </head>
            <body>
              <div class="print-header">
                <h1>${props.question.title}</h1>
                <p>${formatQuestionType.value} - ${
                        props.responses.length
                    } responses</p>
              </div>
              ${cardElement.outerHTML}
              <${"script"}>window.onload = function() { window.print(); window.close(); }</${"script"}>
            </body>
            </html>
          `);

                    printWindow.document.close();
                } else {
                    alert("Please enable pop-ups to print the chart.");
                }
            }
        };

        // Lifecycle hooks
        onMounted(() => {
            // Any initialization needed
        });

        return {
            isLoading,
            hasResponses,
            formatQuestionType,
            visualizationComponent,
            updateLoadingState,
            downloadCSV,
            printChart,
        };
    },
};
</script>

<style scoped>
.question-result-card {
    transition: all 0.2s;
}

.question-result-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

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
