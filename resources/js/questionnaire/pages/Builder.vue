<template>
    <div class="questionnaire-builder-page h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left: Title and Save Status -->
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900 mr-4">
                            {{ questionnaire.title || "Kuesioner Baru" }}
                        </h1>

                        <div
                            v-if="saveStatus === 'saving'"
                            class="text-sm text-gray-500 flex items-center"
                        >
                            <svg
                                class="animate-spin mr-2 h-4 w-4 text-indigo-500"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Menyimpan...
                        </div>

                        <div
                            v-else-if="saveStatus === 'saved' && lastSaved"
                            class="text-sm text-gray-500"
                        >
                            Tersimpan {{ lastSaved }}
                        </div>

                        <div
                            v-else-if="saveStatus === 'error'"
                            class="text-sm text-red-500"
                        >
                            Gagal menyimpan. Coba lagi.
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center space-x-3">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="previewQuestionnaire"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5 text-gray-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                            Pratinjau
                        </button>

                        <button
                            v-if="questionnaire.id"
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="saveAsDraft"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5 text-gray-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
                                />
                            </svg>
                            Simpan
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :class="{
                                'opacity-75 cursor-not-allowed': isPublishing,
                            }"
                            :disabled="isPublishing"
                            @click="publishQuestionnaire"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            {{ isPublishing ? "Menerbitkan..." : "Terbitkan" }}
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Component Sidebar -->
            <ComponentSidebar
                :is-dragging="isDragging"
                @dragstart="startDrag"
                @dragend="endDrag"
            />

            <!-- Main Canvas -->
            <main class="flex-1 overflow-auto">
                <BuilderCanvas
                    :questionnaire="questionnaire"
                    :selected-component="selectedComponent"
                    @select-component="selectComponent"
                    @add-section="addSection"
                    @edit-welcome="editWelcomeScreen"
                    @edit-thank-you="editThankYouScreen"
                    @add-question="addQuestion"
                    @duplicate-section="duplicateSection"
                    @delete-section="deleteSection"
                    @duplicate-question="duplicateQuestion"
                    @delete-question="deleteQuestion"
                    @add-options="handleAddOptions"
                />
            </main>

            <!-- Settings Panel (conditionaly rendered) -->
            <SettingsPanel
                v-if="selectedComponent"
                :selected-component="selectedComponent"
                :questionnaire="questionnaire"
                @update:questionnaire="updateQuestionnaire"
                @duplicate-section="duplicateSection"
                @delete-section="deleteSection"
                @duplicate-question="duplicateQuestion"
                @delete-question="deleteQuestion"
                @close="selectedComponent = null"
                ref="settingsPanelRef"
            />
        </div>

        <!-- Publish Confirmation Modal -->
        <div
            v-if="showPublishModal"
            class="fixed z-10 inset-0 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="showPublishModal = false"
                ></div>

                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-indigo-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                            </div>
                            <div
                                class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left"
                            >
                                <h3
                                    class="text-lg leading-6 font-medium text-gray-900"
                                    id="modal-title"
                                >
                                    Terbitkan Kuesioner
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Kuesioner yang telah diterbitkan akan
                                        tersedia untuk diisi oleh alumni. Anda
                                        masih dapat mengedit kuesioner ini
                                        setelah diterbitkan.
                                    </p>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label
                                            for="publish-title"
                                            class="block text-sm font-medium text-gray-700"
                                            >Judul Kuesioner</label
                                        >
                                        <input
                                            type="text"
                                            id="publish-title"
                                            v-model="questionnaire.title"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            for="publish-slug"
                                            class="block text-sm font-medium text-gray-700"
                                            >URL Kuesioner</label
                                        >
                                        <div
                                            class="mt-1 flex rounded-md shadow-sm"
                                        >
                                            <span
                                                class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm"
                                            >
                                                /kuesioner/
                                            </span>
                                            <input
                                                type="text"
                                                id="publish-slug"
                                                v-model="questionnaire.slug"
                                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                for="publish-start-date"
                                                class="block text-sm font-medium text-gray-700"
                                                >Tanggal Mulai</label
                                            >
                                            <input
                                                type="date"
                                                id="publish-start-date"
                                                v-model="
                                                    questionnaire.startDate
                                                "
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            />
                                        </div>

                                        <div>
                                            <label
                                                for="publish-end-date"
                                                class="block text-sm font-medium text-gray-700"
                                                >Tanggal Selesai</label
                                            >
                                            <input
                                                type="date"
                                                id="publish-end-date"
                                                v-model="questionnaire.endDate"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
                    >
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="confirmPublish"
                        >
                            Terbitkan
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="showPublishModal = false"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useQuestionnaireStore } from "../store/questionnaire";
import ComponentSidebar from "../components/builder/ComponentSidebar.vue";
import BuilderCanvas from "../components/builder/BuilderCanvas.vue";
import SettingsPanel from "../components/builder/SettingsPanel.vue";
import { useQuestionnaire } from "../composables/useQuestionnaire";
import { useDragDrop } from "../composables/useDragDrop";
import { v4 as uuidv4 } from "uuid";
import { slugify } from "../utils/helpers";

const props = defineProps({
    initialQuestionnaire: {
        type: Object,
        default: () => ({}),
    },
});

const store = useQuestionnaireStore();
const {
    questionnaire,
    saveStatus,
    lastSaved,
    saveQuestionnaire,
    previewQuestionnaire,
    publishQuestionnaire: publishQuestionnaireAction,
    addSection: addSectionAction,
    duplicateSection: duplicateSectionAction,
    deleteSection: deleteSectionAction,
    duplicateQuestion: duplicateQuestionAction,
    deleteQuestion: deleteQuestionAction,
    updateWelcomeScreen: updateWelcomeScreenAction,
    updateThankYouScreen: updateThankYouScreenAction,
    unsavedChanges,
} = useQuestionnaire(props.initialQuestionnaire);

const { isDragging, startDrag, endDrag } = useDragDrop();

// UI state
const selectedComponent = ref(null);
const showPublishModal = ref(false);
const isPublishing = ref(false);

// Referensi ke komponen SettingsPanel
const settingsPanelRef = ref(null);

// Watch for window close with unsaved changes
const handleBeforeUnload = (e) => {
    if (unsavedChanges.value) {
        e.preventDefault();
        e.returnValue =
            "Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?";
        return e.returnValue;
    }
};

onMounted(() => {
    window.addEventListener("beforeunload", handleBeforeUnload);
});

onBeforeUnmount(() => {
    window.removeEventListener("beforeunload", handleBeforeUnload);
});

// Component selection
const selectComponent = (component) => {
    selectedComponent.value = component;
};

// Section management
const addSection = () => {
    addSectionAction();
};

const duplicateSection = (sectionId) => {
    duplicateSectionAction(sectionId);
};

const deleteSection = (sectionId) => {
    deleteSectionAction(sectionId);
};

// Question management
const addQuestion = (sectionId) => {
    // Find section index
    const sectionIndex = questionnaire.value.sections.findIndex(
        (s) => s.id === sectionId
    );

    if (sectionIndex >= 0) {
        store.currentSectionIndex = sectionIndex;

        // Open modal or show sidebar for selecting question type
        // For now, let's add a text question by default
        store.addQuestion("short-text");
    }
};

const duplicateQuestion = (questionId) => {
    duplicateQuestionAction(questionId);
};

const deleteQuestion = (questionId) => {
    deleteQuestionAction(questionId);
};

// Screen management
const editWelcomeScreen = () => {
    selectComponent({ type: "welcome" });
};

const editThankYouScreen = () => {
    selectComponent({ type: "thankYou" });
};

// Update questionnaire data
const updateQuestionnaire = (updatedQuestionnaire) => {
    // Update store
    Object.assign(questionnaire.value, updatedQuestionnaire);

    // Auto-save
    saveQuestionnaire();
};

// Save operations
const saveAsDraft = () => {
    saveQuestionnaire();
};

// Publishing
const openPublishModal = () => {
    // Make sure slug is filled if empty
    if (!questionnaire.value.slug) {
        questionnaire.value.slug = slugify(
            questionnaire.value.title || "kuesioner"
        );
    }

    showPublishModal.value = true;
};

const confirmPublish = async () => {
    isPublishing.value = true;

    try {
        const result = await publishQuestionnaireAction();

        if (result.success) {
            showPublishModal.value = false;

            // Show success message or redirect
            alert(`Kuesioner berhasil diterbitkan! URL: ${result.url}`);

            // Optional: redirect to the questionnaire list or preview
            // window.location.href = '/kuesioner';
        } else {
            // Handle error
            alert("Gagal menerbitkan kuesioner. Silakan coba lagi.");
        }
    } catch (error) {
        console.error("Error publishing questionnaire:", error);
        alert("Terjadi kesalahan saat menerbitkan kuesioner.");
    } finally {
        isPublishing.value = false;
    }
};

const publishQuestionnaire = () => {
    openPublishModal();
};

const handleAddOptions = (payload) => {
    // Ekstrak questionId dan count dari payload
    const questionId = payload.questionId || payload;
    const count = payload.count || 1;

    // Jika ada questionId, pilih pertanyaan tersebut terlebih dahulu
    if (questionId) {
        selectComponent({ type: "question", id: questionId });

        // Berikan sedikit waktu untuk komponen dirender
        setTimeout(() => {
            // Panggil metode addRankingOptions di SettingsPanel dengan jumlah yang sesuai
            if (settingsPanelRef.value) {
                settingsPanelRef.value.addRankingOptions(count);
            }
        }, 50);
    }
};
</script>

<style scoped>
/* Additional component-specific styles can be added here */
</style>
