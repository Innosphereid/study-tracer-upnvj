<template>
    <div class="builder-canvas flex-1 overflow-y-auto bg-gray-50 p-4">
        <div class="max-w-4xl mx-auto">
            <!-- Welcome Screen -->
            <div
                class="mb-8 bg-white rounded-lg shadow p-6 border-2"
                :class="[
                    selectedComponent && selectedComponent.type === 'welcome'
                        ? 'border-indigo-500'
                        : 'border-transparent',
                ]"
                @click="selectComponent('welcome')"
            >
                <h2 class="text-xl font-semibold text-gray-800 mb-2">
                    {{ questionnaire.welcomeScreen.title }}
                </h2>
                <p class="text-gray-600">
                    {{ questionnaire.welcomeScreen.description }}
                </p>
                <div
                    v-if="
                        selectedComponent &&
                        selectedComponent.type === 'welcome'
                    "
                    class="mt-4 flex justify-end"
                >
                    <button
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs rounded-md bg-white hover:bg-gray-50"
                        @click.stop="editWelcomeScreen"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1"
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
                        Edit
                    </button>
                </div>
            </div>

            <!-- Main Content Area: Sections or Empty State -->
            <div class="space-y-8 mb-8">
                <!-- Empty State (No Sections) -->
                <div
                    v-if="questionnaire.sections.length === 0"
                    class="flex justify-center py-8"
                >
                    <div class="text-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            Belum ada seksi
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Tambahkan seksi baru untuk memulai pembuatan
                            kuesioner.
                        </p>
                        <div class="mt-4">
                            <button
                                type="button"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                @click="addSection"
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
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                Tambah Seksi
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sections List -->
                <template v-else>
                    <Section
                        v-for="(section, index) in questionnaire.sections"
                        :key="section.id"
                        :section="section"
                        :index="index"
                        :is-selected="isSelectedSection(section.id)"
                        @select="selectSectionComponent"
                        @add-question="addQuestionToSection"
                        @duplicate="duplicateSection(section.id)"
                        @delete="confirmDeleteSection(section.id)"
                    />

                    <!-- Add Section Button (when sections exist) -->
                    <div class="flex justify-center">
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="addSection"
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
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Tambah Seksi
                        </button>
                    </div>
                </template>
            </div>

            <!-- Thank You Screen -->
            <div
                class="mb-8 bg-white rounded-lg shadow p-6 border-2"
                :class="[
                    selectedComponent && selectedComponent.type === 'thankYou'
                        ? 'border-indigo-500'
                        : 'border-transparent',
                ]"
                @click="selectComponent('thankYou')"
            >
                <h2 class="text-xl font-semibold text-gray-800 mb-2">
                    {{ questionnaire.thankYouScreen.title }}
                </h2>
                <p class="text-gray-600">
                    {{ questionnaire.thankYouScreen.description }}
                </p>
                <div
                    v-if="
                        selectedComponent &&
                        selectedComponent.type === 'thankYou'
                    "
                    class="mt-4 flex justify-end"
                >
                    <button
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs rounded-md bg-white hover:bg-gray-50"
                        @click.stop="editThankYouScreen"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1"
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
                        Edit
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty / Section Drop Zone -->
        <DropZone
            target-type="canvas"
            :accept-types="['component', 'section']"
            @drop="handleDrop"
            zone-class="h-24 flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12"
        >
            <template v-slot="{ isOver, isValidTarget }">
                <div class="text-center">
                    <svg
                        class="mx-auto h-12 w-12"
                        :class="
                            isOver && isValidTarget
                                ? 'text-indigo-500'
                                : 'text-gray-400'
                        "
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        />
                    </svg>
                    <h3
                        class="mt-2 text-sm font-medium"
                        :class="
                            isOver && isValidTarget
                                ? 'text-indigo-600'
                                : 'text-gray-900'
                        "
                    >
                        Drop komponen atau seksi di sini
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        atau gunakan tombol "Tambah Seksi" di atas
                    </p>
                </div>
            </template>
        </DropZone>

        <!-- Confirmation Modal for Delete -->
        <div
            v-if="showDeleteConfirm"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Konfirmasi Hapus
                </h3>
                <p class="text-sm text-gray-500 mb-6">
                    Apakah Anda yakin ingin menghapus
                    {{ deleteType === "section" ? "seksi" : "pertanyaan" }} ini?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm rounded-md bg-white hover:bg-gray-50"
                        @click="showDeleteConfirm = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm rounded-md text-white bg-red-600 hover:bg-red-700"
                        @click="confirmDelete"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed } from "vue";
import { useQuestionnaireStore } from "../../store/questionnaire";
import { useDragDrop } from "../../composables/useDragDrop";
import Section from "./Section.vue";
import DropZone from "../shared/DropZone.vue";

const store = useQuestionnaireStore();
const { handleDrop: originalHandleDrop } = useDragDrop();

const props = defineProps({
    questionnaire: {
        type: Object,
        required: true,
    },
    selectedComponent: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits([
    "select-component",
    "add-section",
    "edit-welcome",
    "edit-thank-you",
    "add-question",
    "duplicate-section",
    "delete-section",
    "delete-question",
]);

// Delete confirmation
const showDeleteConfirm = ref(false);
const deleteType = ref("");
const deleteId = ref(null);

const selectComponent = (type, id = null) => {
    emit("select-component", { type, id });
};

const selectSectionComponent = ({ type, id }) => {
    emit("select-component", { type, id });
};

const isSelectedSection = (sectionId) => {
    return (
        props.selectedComponent &&
        props.selectedComponent.type === "section" &&
        props.selectedComponent.id === sectionId
    );
};

const addSection = () => {
    emit("add-section");
};

const editWelcomeScreen = () => {
    emit("edit-welcome");
};

const editThankYouScreen = () => {
    emit("edit-thank-you");
};

const addQuestionToSection = (sectionId) => {
    emit("add-question", sectionId);
};

const duplicateSection = (sectionId) => {
    emit("duplicate-section", sectionId);
};

const confirmDeleteSection = (sectionId) => {
    deleteType.value = "section";
    deleteId.value = sectionId;
    showDeleteConfirm.value = true;
};

const confirmDeleteQuestion = (questionId) => {
    deleteType.value = "question";
    deleteId.value = questionId;
    showDeleteConfirm.value = true;
};

const confirmDelete = () => {
    if (deleteType.value === "section") {
        emit("delete-section", deleteId.value);
    } else if (deleteType.value === "question") {
        emit("delete-question", deleteId.value);
    }
    showDeleteConfirm.value = false;
    deleteId.value = null;
};

// Wrapper untuk handleDrop
const handleDrop = (dropData) => {
    console.log("Canvas handleDrop called with:", dropData);
    const result = originalHandleDrop(dropData);
    console.log("Canvas handleDrop result:", result);
    return result;
};
</script>

<style scoped>
.builder-canvas {
    min-height: calc(100vh - 150px);
}
</style>
