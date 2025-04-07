<!-- 
* @component ResultsApp
* @description Komponen utama untuk menampilkan hasil kuesioner, termasuk overview statistik, 
* navigasi antar section, dan visualisasi untuk setiap tipe pertanyaan.
-->
<template>
    <div class="questionnaire-results">
        <!-- Header Section -->
        <header-section
            :questionnaire-title="questionnaire.title"
            :statistics="statistics"
            @periodChanged="handlePeriodChange"
        />

        <!-- Dashboard Overview -->
        <dashboard-overview :statistics="statistics" :period="selectedPeriod" />

        <!-- Section Navigation -->
        <section-navigator
            :sections="questionnaire.sections"
            :current-section="currentSection"
            @change-section="changeSection"
        />

        <!-- Results Content Area -->
        <div class="results-content mt-6">
            <template v-if="loading">
                <div class="flex justify-center items-center py-12">
                    <div class="spinner"></div>
                </div>
            </template>

            <template v-else-if="error">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 my-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-red-500"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ error }}</p>
                        </div>
                    </div>
                </div>
            </template>

            <template
                v-else-if="
                    currentSection &&
                    currentSection.questions &&
                    currentSection.questions.length > 0
                "
            >
                <question-result-card
                    v-for="question in currentSection.questions"
                    :key="question.id"
                    :question="question"
                    :responses="questionResponses[question.id] || []"
                />
            </template>

            <template v-else>
                <div class="bg-gray-50 rounded-lg p-8 text-center">
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
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        Tidak ada data
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Tidak ada pertanyaan yang tersedia untuk section ini.
                    </p>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from "vue";
import HeaderSection from "../components/results/HeaderSection.vue";
import DashboardOverview from "../components/results/DashboardOverview.vue";
import SectionNavigator from "../components/results/SectionNavigator.vue";
import QuestionResultCard from "../components/results/QuestionResultCard.vue";

export default {
    name: "ResultsApp",
    components: {
        HeaderSection,
        DashboardOverview,
        SectionNavigator,
        QuestionResultCard,
    },

    props: {
        questionnaire: {
            type: Object,
            required: true,
        },
        statistics: {
            type: Object,
            required: true,
        },
        questionnaireId: {
            type: [Number, String],
            required: true,
        },
    },

    setup(props) {
        const loading = ref(true);
        const error = ref(null);
        const questionResponses = ref({});
        const currentSectionIndex = ref(0);
        const selectedPeriod = ref({
            start: null,
            end: null,
            type: "all", // 'all', 'week', 'month', 'custom'
        });

        // Move sections from questionnaire_json to sections if needed
        if (
            props.questionnaire &&
            (!props.questionnaire.sections ||
                props.questionnaire.sections.length === 0) &&
            props.questionnaire.questionnaire_json &&
            props.questionnaire.questionnaire_json.sections &&
            props.questionnaire.questionnaire_json.sections.length > 0
        ) {
            console.log(
                "Copying sections from questionnaire_json to sections property"
            );
            props.questionnaire.sections =
                props.questionnaire.questionnaire_json.sections;
        }

        // Computed properties
        const currentSection = computed(() => {
            console.log(
                "Computing current section. Sections:",
                props.questionnaire.sections
            );

            if (
                !props.questionnaire.sections ||
                props.questionnaire.sections.length === 0
            ) {
                console.warn("No sections found in questionnaire data", {
                    questionnaire: props.questionnaire,
                });
                return null;
            }

            const section =
                props.questionnaire.sections[currentSectionIndex.value];
            console.log("Current section:", section);

            // Check if the section has questions properly loaded
            if (
                section &&
                (!section.questions || section.questions.length === 0)
            ) {
                console.warn("Section has no questions:", section);
            }

            return section;
        });

        // Methods
        const fetchQuestionResponses = async (questionIds) => {
            if (!questionIds || questionIds.length === 0) {
                console.warn("No question IDs to fetch responses for", {
                    currentSection: currentSection.value,
                });
                loading.value = false;
                return;
            }

            console.log("Fetching responses for questions:", questionIds);
            loading.value = true;
            error.value = null;

            try {
                const periodParams =
                    selectedPeriod.value.type !== "all"
                        ? `&start=${selectedPeriod.value.start}&end=${selectedPeriod.value.end}`
                        : "";

                const promises = questionIds.map((questionId) =>
                    fetch(
                        `/kuesioner/answer-details/question/${questionId}?questionnaireId=${props.questionnaireId}${periodParams}`
                    ).then((response) => {
                        if (!response.ok) {
                            throw new Error(
                                `Error fetching responses for question ${questionId}`
                            );
                        }
                        return response.json();
                    })
                );

                const responses = await Promise.all(promises);

                // Organize responses by question ID
                const newResponses = {};
                questionIds.forEach((questionId, index) => {
                    newResponses[questionId] = responses[index].data || [];
                });

                questionResponses.value = newResponses;
            } catch (err) {
                console.error("Error fetching question responses:", err);
                error.value = "Gagal memuat data respons: " + err.message;
            } finally {
                loading.value = false;
            }
        };

        const changeSection = (sectionIndex) => {
            if (
                sectionIndex >= 0 &&
                sectionIndex < props.questionnaire.sections.length
            ) {
                currentSectionIndex.value = sectionIndex;
            }
        };

        const handlePeriodChange = (period) => {
            selectedPeriod.value = period;
        };

        // Watchers
        watch(currentSection, (newSection) => {
            if (newSection && newSection.questions) {
                const questionIds = newSection.questions.map((q) => q.id);
                fetchQuestionResponses(questionIds);
            }
        });

        watch(selectedPeriod, () => {
            if (currentSection.value && currentSection.value.questions) {
                const questionIds = currentSection.value.questions.map(
                    (q) => q.id
                );
                fetchQuestionResponses(questionIds);
            }
        });

        // Lifecycle hooks
        onMounted(() => {
            console.log("ResultsApp mounted with data:", {
                questionnaire: props.questionnaire,
                statistics: props.statistics,
                questionnaireId: props.questionnaireId,
            });

            // Check if questionnaire_json has sections but props.questionnaire.sections is empty
            if (
                props.questionnaire &&
                (!props.questionnaire.sections ||
                    props.questionnaire.sections.length === 0) &&
                props.questionnaire.questionnaire_json &&
                props.questionnaire.questionnaire_json.sections &&
                props.questionnaire.questionnaire_json.sections.length > 0
            ) {
                console.log(
                    "Moving sections from questionnaire_json to sections property"
                );
                props.questionnaire.sections =
                    props.questionnaire.questionnaire_json.sections;
            }

            // Ensure sections are properly loaded
            if (
                props.questionnaire &&
                (!props.questionnaire.sections ||
                    props.questionnaire.sections.length === 0 ||
                    (props.questionnaire.sections[0] &&
                        !props.questionnaire.sections[0].questions))
            ) {
                console.log(
                    "Attempting to fetch/fix sections since none were found or they're incomplete"
                );
                fetchSections();
            } else if (currentSection.value && currentSection.value.questions) {
                const questionIds = currentSection.value.questions.map(
                    (q) => q.id
                );
                fetchQuestionResponses(questionIds);
            } else {
                loading.value = false;
            }
        });

        // Add a new method to fetch sections if they aren't loaded
        const fetchSections = async () => {
            try {
                loading.value = true;
                error.value = null;
                console.log("Attempting to fetch sections from API endpoint");

                try {
                    // First try to fetch from our new API endpoint
                    const response = await fetch(
                        `/kuesioner/${props.questionnaireId}/sections`
                    );

                    if (response.ok) {
                        const data = await response.json();

                        if (
                            data.success &&
                            data.sections &&
                            data.sections.length > 0
                        ) {
                            console.log(
                                "Received sections from API:",
                                data.sections
                            );

                            // Update the questionnaire with fetched sections
                            props.questionnaire.sections = data.sections;

                            // Now that we have sections, try to fetch responses for the current section
                            if (
                                currentSection.value &&
                                currentSection.value.questions &&
                                currentSection.value.questions.length > 0
                            ) {
                                const questionIds =
                                    currentSection.value.questions.map(
                                        (q) => q.id
                                    );
                                fetchQuestionResponses(questionIds);
                                return; // Exit early since we successfully got data
                            }
                        }
                    }

                    // If we reach here, the API call didn't succeed, so fall back to the local data approach
                    console.log(
                        "API call didn't return usable data, falling back to local data processing"
                    );
                } catch (apiError) {
                    console.warn(
                        "API call failed, falling back to local data processing:",
                        apiError
                    );
                }

                // Fallback approach - try to work with what we have or use questionnaire_json
                if (
                    props.questionnaire &&
                    typeof props.questionnaire.id !== "undefined"
                ) {
                    // Check if we can use questionnaire_json.sections if available
                    if (
                        (!props.questionnaire.sections ||
                            props.questionnaire.sections.length === 0) &&
                        props.questionnaire.questionnaire_json &&
                        props.questionnaire.questionnaire_json.sections &&
                        props.questionnaire.questionnaire_json.sections.length >
                            0
                    ) {
                        console.log(
                            "Using sections from questionnaire_json",
                            props.questionnaire.questionnaire_json.sections
                        );
                        props.questionnaire.sections =
                            props.questionnaire.questionnaire_json.sections;
                    }

                    // If the questionnaire already has sections but they're not properly structured
                    if (Array.isArray(props.questionnaire.sections)) {
                        const hasSections =
                            props.questionnaire.sections.length > 0;
                        const hasValidSections =
                            hasSections &&
                            props.questionnaire.sections.every(
                                (section) =>
                                    section && typeof section === "object"
                            );

                        if (hasSections && hasValidSections) {
                            // Make sure each section has a questions array
                            props.questionnaire.sections =
                                props.questionnaire.sections.map((section) => {
                                    if (!section.questions) {
                                        section.questions = [];
                                    } else if (
                                        !Array.isArray(section.questions)
                                    ) {
                                        // Try to convert to array if it's not already
                                        section.questions = Object.values(
                                            section.questions
                                        );
                                    }
                                    return section;
                                });

                            console.log(
                                "Updated questionnaire sections structure:",
                                props.questionnaire.sections
                            );

                            if (
                                currentSection.value &&
                                currentSection.value.questions &&
                                currentSection.value.questions.length > 0
                            ) {
                                const questionIds =
                                    currentSection.value.questions.map(
                                        (q) => q.id
                                    );
                                fetchQuestionResponses(questionIds);
                            } else {
                                console.warn(
                                    "Current section has no questions after structure update"
                                );
                                loading.value = false;
                            }
                        } else {
                            console.error(
                                "Questionnaire has invalid sections",
                                props.questionnaire.sections
                            );
                            error.value =
                                "Struktur data seksi kuesioner tidak valid";
                            loading.value = false;
                        }
                    } else {
                        console.error(
                            "Questionnaire has no sections array",
                            props.questionnaire
                        );
                        error.value = "Kuesioner tidak memiliki data seksi";
                        loading.value = false;
                    }
                } else {
                    console.error(
                        "Invalid questionnaire object",
                        props.questionnaire
                    );
                    error.value = "Data kuesioner tidak valid";
                    loading.value = false;
                }
            } catch (err) {
                console.error("Error processing questionnaire sections:", err);
                error.value =
                    "Terjadi kesalahan saat memproses seksi kuesioner: " +
                    err.message;
                loading.value = false;
            }
        };

        return {
            loading,
            error,
            questionResponses,
            currentSection,
            currentSectionIndex,
            selectedPeriod,
            changeSection,
            handlePeriodChange,
            fetchSections,
        };
    },
};
</script>

<style scoped>
.questionnaire-results {
    max-width: 1200px;
    margin: 0 auto;
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
