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

        // Computed properties
        const currentSection = computed(() => {
            if (
                !props.questionnaire.sections ||
                props.questionnaire.sections.length === 0
            ) {
                return null;
            }
            return props.questionnaire.sections[currentSectionIndex.value];
        });

        // Methods
        const fetchQuestionResponses = async (questionIds) => {
            if (!questionIds || questionIds.length === 0) return;

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
            if (currentSection.value && currentSection.value.questions) {
                const questionIds = currentSection.value.questions.map(
                    (q) => q.id
                );
                fetchQuestionResponses(questionIds);
            } else {
                loading.value = false;
            }
        });

        return {
            loading,
            error,
            questionResponses,
            currentSection,
            currentSectionIndex,
            selectedPeriod,
            changeSection,
            handlePeriodChange,
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
