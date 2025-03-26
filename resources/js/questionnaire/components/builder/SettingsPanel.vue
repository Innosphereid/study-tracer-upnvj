<template>
    <div
        class="settings-panel bg-white border-l border-gray-200 w-80 flex flex-col h-full"
    >
        <div
            class="p-4 border-b border-gray-200 flex items-center justify-between"
        >
            <h2 class="text-lg font-medium text-gray-900">{{ panelTitle }}</h2>
            <button
                type="button"
                class="text-gray-400 hover:text-gray-500"
                @click="$emit('close')"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <!-- Questionnaire Settings -->
            <QuestionnaireSettings
                v-if="selectedComponent?.type === 'questionnaire'"
                :questionnaire="questionnaire"
                @update:questionnaire="updateQuestionnaire"
            />

            <!-- Welcome Screen Settings -->
            <WelcomeScreenSettings
                v-else-if="selectedComponent?.type === 'welcome'"
                :welcome-screen="questionnaire.welcomeScreen"
                @update="updateWelcomeScreen"
            />

            <!-- Thank You Screen Settings -->
            <ThankYouScreenSettings
                v-else-if="selectedComponent?.type === 'thankYou'"
                :thank-you-screen="questionnaire.thankYouScreen"
                @update="updateThankYouScreen"
            />

            <!-- Section Settings -->
            <SectionSettings
                v-else-if="selectedComponent?.type === 'section'"
                :section="getSelectedSection"
                @update="updateSection"
                @duplicate="duplicateSection"
                @delete="deleteSection"
            />

            <!-- Question Settings -->
            <QuestionTypeSettings
                v-else-if="selectedComponent?.type === 'question'"
                ref="questionTypeSettingsRef"
                :question="currentQuestion"
                @update:question="updateQuestionFromComponent"
                @duplicate-question="duplicateQuestion"
                @delete-question="deleteQuestion"
            />

            <!-- No component selected -->
            <div v-else>
                <div
                    class="py-6 flex flex-col items-center justify-center text-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-12 w-12 text-gray-400 mb-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"
                        />
                    </svg>
                    <h3 class="text-sm font-medium text-gray-900">
                        Pilih komponen untuk mengedit
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Klik pada seksi, pertanyaan, atau elemen lainnya untuk
                        menyunting.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, watch } from "vue";
import { useQuestionnaireStore } from "../../store/questionnaire";
import { v4 as uuidv4 } from "uuid";
import QuestionTypeSettings from "./settings/QuestionTypeSettings.vue";
import QuestionnaireSettings from "./settings/QuestionnaireSettings.vue";
import WelcomeScreenSettings from "./settings/WelcomeScreenSettings.vue";
import ThankYouScreenSettings from "./settings/ThankYouScreenSettings.vue";
import SectionSettings from "./settings/SectionSettings.vue";

const props = defineProps({
    selectedComponent: {
        type: Object,
        default: null,
    },
    questionnaire: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "update:questionnaire",
    "duplicate-section",
    "delete-section",
    "duplicate-question",
    "delete-question",
    "close",
]);

const store = useQuestionnaireStore();

// Computed panel title based on selected component
const panelTitle = computed(() => {
    if (!props.selectedComponent) return "Pengaturan";

    switch (props.selectedComponent.type) {
        case "questionnaire":
            return "Pengaturan Kuesioner";
        case "welcome":
            return "Halaman Pembuka";
        case "thankYou":
            return "Halaman Terima Kasih";
        case "section":
            return "Pengaturan Seksi";
        case "question":
            return "Pengaturan Pertanyaan";
        default:
            return "Pengaturan";
    }
});

// Current question reference (if question is selected)
const currentQuestion = computed(() => {
    if (props.selectedComponent?.type !== "question") return null;

    // Find question in questionnaire
    for (const section of props.questionnaire.sections) {
        const question = section.questions.find(
            (q) => q.id === props.selectedComponent.id
        );
        if (question) return question;
    }

    return null;
});

// Get selected section
const getSelectedSection = computed(() => {
    if (props.selectedComponent?.type !== "section") return null;
    return props.questionnaire.sections.find(
        (s) => s.id === props.selectedComponent.id
    );
});

// Update methods
const updateQuestionnaire = (settings) => {
    emit("update:questionnaire", {
        ...props.questionnaire,
        ...settings,
    });
};

const updateWelcomeScreen = (welcomeScreen) => {
    emit("update:questionnaire", {
        ...props.questionnaire,
        welcomeScreen,
    });
};

const updateThankYouScreen = (thankYouScreen) => {
    emit("update:questionnaire", {
        ...props.questionnaire,
        thankYouScreen,
    });
};

const updateSection = (updatedSection) => {
    if (!props.selectedComponent || props.selectedComponent.type !== "section")
        return;

    const sectionId = props.selectedComponent.id;
    const sectionIndex = props.questionnaire.sections.findIndex(
        (s) => s.id === sectionId
    );

    if (sectionIndex === -1) return;

    const updatedSections = [...props.questionnaire.sections];
    updatedSections[sectionIndex] = {
        ...updatedSections[sectionIndex],
        ...updatedSection,
    };

    emit("update:questionnaire", {
        ...props.questionnaire,
        sections: updatedSections,
    });
};

const updateQuestionFromComponent = (updatedQuestion) => {
    if (!props.selectedComponent || props.selectedComponent.type !== "question")
        return;

    const questionId = props.selectedComponent.id;
    let sectionIndex = -1;
    let questionIndex = -1;

    // Find question position
    props.questionnaire.sections.forEach((section, secIdx) => {
        const qIdx = section.questions.findIndex((q) => q.id === questionId);
        if (qIdx !== -1) {
            sectionIndex = secIdx;
            questionIndex = qIdx;
        }
    });

    if (sectionIndex === -1 || questionIndex === -1) return;

    // Create a copy of the questionnaire structure
    const updatedSections = [...props.questionnaire.sections];

    // Apply the update
    updatedSections[sectionIndex].questions[questionIndex] = updatedQuestion;

    emit("update:questionnaire", {
        ...props.questionnaire,
        sections: updatedSections,
    });
};

// Section management
const duplicateSection = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "section")
        return;

    emit("duplicate-section", props.selectedComponent.id);
};

const deleteSection = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "section")
        return;

    emit("delete-section", props.selectedComponent.id);
};

// Question management
const duplicateQuestion = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "question")
        return;

    emit("duplicate-question", props.selectedComponent.id);
};

const deleteQuestion = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "question")
        return;

    emit("delete-question", props.selectedComponent.id);
};

// Referensi ke komponen QuestionTypeSettings
const questionTypeSettingsRef = ref(null);

// Expose method for ranking options
function addRankingOptions(count = 1) {
    if (
        props.selectedComponent?.type === "question" &&
        currentQuestion.value?.type === "ranking" &&
        questionTypeSettingsRef.value
    ) {
        questionTypeSettingsRef.value.addRankingOptions(count);
    }
}

// Expose fungsi untuk dipanggil dari komponen lain
defineExpose({
    addRankingOptions,
});
</script>

<style scoped>
.settings-panel {
    min-width: 20rem;
}
</style>
