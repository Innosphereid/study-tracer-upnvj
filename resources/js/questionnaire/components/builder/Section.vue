<template>
    <div
        class="section bg-white rounded-lg shadow"
        :class="{ 'border-2 border-indigo-500': isSelected }"
        @click.stop="selectSection"
    >
        <!-- Section Header -->
        <div class="section-header p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ section.title || "Seksi Tanpa Judul" }}
                </h3>
                <div class="flex items-center space-x-2">
                    <button
                        type="button"
                        class="p-1 text-gray-400 hover:text-indigo-500 focus:outline-none"
                        @click.stop="$emit('add-question', section.id)"
                        title="Tambah Pertanyaan"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="p-1 text-gray-400 hover:text-indigo-500 focus:outline-none"
                        @click.stop="$emit('duplicate', section.id)"
                        title="Duplikasi Seksi"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"
                            />
                            <path
                                d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z"
                            />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="p-1 text-gray-400 hover:text-red-500 focus:outline-none"
                        @click.stop="$emit('delete', section.id)"
                        title="Hapus Seksi"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>
            <p v-if="section.description" class="mt-1 text-sm text-gray-500">
                {{ section.description }}
            </p>
        </div>

        <!-- Questions List -->
        <div class="questions-container p-4 space-y-4">
            <template v-if="section.questions.length > 0">
                <QuestionWrapper
                    v-for="(question, qIndex) in section.questions"
                    :key="question.id"
                    :question="question"
                    :section-id="section.id"
                    :index="qIndex"
                    :is-selected="selectedQuestionId === question.id"
                    @select="selectQuestion"
                    @duplicate="$emit('duplicate-question', $event)"
                    @delete="$emit('delete-question', $event)"
                />
            </template>

            <div
                v-else
                class="empty-section flex items-center justify-center py-6 bg-gray-50 border-2 border-dashed border-gray-300 rounded-md"
            >
                <DropZone
                    target-type="section"
                    :target-id="section.id"
                    :accept-types="['component', 'question']"
                    @drop="handleSectionDrop"
                    zone-class="w-full h-full absolute inset-0"
                >
                    <template v-slot="{ isOver, isValidTarget }">
                        <div
                            class="text-center relative z-10"
                            :class="{ 'opacity-50': isOver }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="mx-auto h-10 w-10"
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
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                Belum ada pertanyaan
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{
                                    isOver && isValidTarget
                                        ? "Lepas untuk menambahkan komponen"
                                        : "Tambahkan pertanyaan untuk seksi ini."
                                }}
                            </p>
                            <div class="mt-4" v-if="!isOver">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    @click.stop="
                                        $emit('add-question', section.id)
                                    "
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="-ml-0.5 mr-2 h-4 w-4"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    Tambah Pertanyaan
                                </button>
                            </div>
                        </div>
                    </template>
                </DropZone>
            </div>
        </div>

        <!-- Drop Zone for components -->
        <DropZone
            target-type="section"
            :target-id="section.id"
            :accept-types="['component', 'question']"
            @drop="handleSectionDrop"
            zone-class="py-4 px-2 border-t border-gray-200 rounded-b-lg"
        >
            <template v-slot="{ isOver, isValidTarget }">
                <div
                    class="text-center py-2 rounded transition-colors"
                    :class="{
                        'bg-indigo-50 border border-dashed border-indigo-300':
                            isOver && isValidTarget,
                        'bg-red-50 border border-dashed border-red-300':
                            isOver && !isValidTarget,
                    }"
                >
                    <p class="text-sm text-gray-500">
                        {{
                            isOver && isValidTarget
                                ? "Lepas untuk menambahkan komponen"
                                : "Tambahkan atau drop komponen di sini"
                        }}
                    </p>
                </div>
            </template>
        </DropZone>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from "vue";
import QuestionWrapper from "./QuestionWrapper.vue";
import DropZone from "../shared/DropZone.vue";
import { useDragDrop } from "../../composables/useDragDrop";

const { handleDrop } = useDragDrop();

const props = defineProps({
    section: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
        required: true,
    },
    isSelected: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "select",
    "add-question",
    "duplicate",
    "delete",
    "duplicate-question",
    "delete-question",
]);

const selectedQuestionId = ref(null);

// Handle drag-drop events for this section
const handleSectionDrop = (dropData) => {
    console.log("Section drop handler called:", {
        dropData,
        sectionId: props.section.id,
    });

    // Pastikan target ID adalah ID seksi ini
    if (dropData.targetType === "section") {
        if (dropData.targetId !== props.section.id) {
            console.log("Target ID mismatch:", {
                expected: props.section.id,
                received: dropData.targetId,
            });
            // Update targetId jika tidak cocok
            dropData.targetId = props.section.id;
        }

        const result = handleDrop(dropData);
        console.log("Section drop result:", result);
        return result;
    }

    console.log("Drop ignored: not for section");
    return false;
};

const selectSection = () => {
    selectedQuestionId.value = null;
    emit("select", { type: "section", id: props.section.id });
};

const selectQuestion = (questionId) => {
    selectedQuestionId.value = questionId;
    emit("select", { type: "question", id: questionId });
};
</script>

<style scoped>
.section {
    transition: border-color 0.15s ease-in-out;
}
</style>
