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
                <!-- Drop zone at the beginning of the list -->
                <QuestionDropZone
                    :section-id="section.id"
                    :target-index="0"
                    :is-first-position="true"
                    @drop="handleQuestionDrop"
                />

                <div
                    v-for="(question, qIndex) in section.questions"
                    :key="question.id"
                    class="question-item"
                >
                    <QuestionWrapper
                        :question="question"
                        :section-id="section.id"
                        :index="qIndex"
                        :is-selected="selectedQuestionId === question.id"
                        @select="selectQuestion"
                        @duplicate="$emit('duplicate-question', $event)"
                        @delete="$emit('delete-question', $event)"
                        @dragstart="onQuestionDragStart"
                        @dragend="onQuestionDragEnd"
                        @add-options="
                            (count) =>
                                $emit('add-options', {
                                    questionId: question.id,
                                    count,
                                })
                        "
                    />

                    <!-- Drop zone after each question except the last one -->
                    <QuestionDropZone
                        :section-id="section.id"
                        :target-index="qIndex + 1"
                        :is-last-position="
                            qIndex === section.questions.length - 1
                        "
                        @drop="handleQuestionDrop"
                    />
                </div>
            </template>

            <div
                v-else
                class="empty-section bg-gray-50 border-2 border-dashed border-gray-300 rounded-md overflow-hidden relative"
            >
                <!-- Improved empty section with enhanced drop zone UI -->
                <div
                    class="p-8 flex flex-col items-center justify-center transition-all duration-300"
                    @dragover.prevent="onEmptySectionDragOver"
                    @dragleave.prevent="onEmptySectionDragLeave"
                    @drop.prevent="onEmptySectionDrop"
                    :class="{
                        'bg-indigo-50 border-indigo-300':
                            isEmptySectionDragOver && isValidTargetForDrop,
                        'bg-red-50 border-red-300':
                            isEmptySectionDragOver && !isValidTargetForDrop,
                    }"
                >
                    <div
                        v-if="isEmptySectionDragOver && isValidTargetForDrop"
                        class="mb-4"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-12 w-12 text-indigo-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <div
                        v-else-if="
                            isEmptySectionDragOver && !isValidTargetForDrop
                        "
                        class="mb-4"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-12 w-12 text-red-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <div v-else class="mb-4">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-12 w-12 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                            />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-center">
                        <span class="font-medium">Belum ada pertanyaan</span
                        ><br />
                        <span class="text-sm">{{
                            isEmptySectionDragOver && isValidTargetForDrop
                                ? "Lepas untuk menambahkan pertanyaan ini"
                                : "Tambahkan pertanyaan untuk seksi ini"
                        }}</span>
                    </p>

                    <div class="mt-4" v-if="!isEmptySectionDragOver">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click.stop="$emit('add-question', section.id)"
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
            </div>
        </div>

        <!-- Drop Zone for components -->
        <DropZone
            target-type="section"
            :target-id="section.id"
            :accept-types="['component', 'question']"
            @drop="handleSectionDrop"
            zone-class="py-4 px-2 border-t border-gray-200 rounded-b-lg"
            v-if="section.questions.length > 0"
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
import { ref, defineProps, defineEmits, provide } from "vue";
import QuestionWrapper from "./QuestionWrapper.vue";
import DropZone from "../shared/DropZone.vue";
import QuestionDropZone from "./QuestionDropZone.vue";
import { useDragDrop } from "../../composables/useDragDrop";

const { handleDrop, handleQuestionReorder } = useDragDrop();

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
    "reorder-questions",
    "add-options",
]);

const selectedQuestionId = ref(null);
const isDraggingQuestion = ref(false);
const draggedQuestionIndex = ref(null);
const isEmptySectionDragOver = ref(false);
const isValidTargetForDrop = ref(false);

// Provide dragging state and index to child components
provide("isDraggingQuestion", isDraggingQuestion);
provide("draggedQuestionIndex", draggedQuestionIndex);

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

// New function to handle question drop for reordering or adding new component
const handleQuestionDrop = (dropData) => {
    console.log("Question drop handler called:", dropData);

    // Handle reordering of existing questions
    if (
        dropData.sourceType === "question" &&
        dropData.sectionId === props.section.id
    ) {
        const oldIndex = dropData.sourceIndex;
        const newIndex = dropData.targetIndex;

        if (oldIndex !== newIndex) {
            console.log(`Reordering question from ${oldIndex} to ${newIndex}`);

            // Call reorder function from useDragDrop
            const reorderEvent = { oldIndex, newIndex };
            handleQuestionReorder(reorderEvent, props.section.id);

            return true;
        }
    }
    // Handle adding new component from sidebar at specific position
    else if (dropData.sourceType === "component") {
        // Get the component type and target position
        const componentType = dropData.item.id;
        const targetPosition = dropData.targetIndex;

        console.log(
            `Adding component ${componentType} at position ${targetPosition} in section ${props.section.id}`
        );

        // Create custom drop data for the handleDrop function
        const customDropData = {
            item: dropData.item,
            sourceType: "component",
            targetType: "section",
            targetId: props.section.id,
            targetPosition: targetPosition, // Add target position information
        };

        // Call the drop handler with position information
        const result = handleDrop(customDropData);
        return result;
    }

    return false;
};

const onQuestionDragStart = (dragData) => {
    isDraggingQuestion.value = true;
    draggedQuestionIndex.value = dragData.sourceIndex;
    console.log(`Started dragging question at index ${dragData.sourceIndex}`);

    // Show a quick toast notification
    showDragInstructions();
};

const showDragInstructions = () => {
    // Check if we already have a toast notification
    let toast = document.getElementById("drag-instructions-toast");

    if (!toast) {
        // Create a toast notification
        toast = document.createElement("div");
        toast.id = "drag-instructions-toast";
        toast.className =
            "fixed top-4 left-1/2 transform -translate-x-1/2 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 text-sm flex items-center";
        toast.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
            </svg>
            <span>Seret pertanyaan ke zona drop yang ditandai untuk mengubah urutan</span>
        `;

        // Add to document
        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast && toast.parentNode) {
                toast.classList.add("opacity-0");
                setTimeout(() => {
                    if (toast && toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }, 3000);

        // Add transition
        toast.style.transition = "opacity 0.3s ease-in-out";
    }
};

const onQuestionDragEnd = () => {
    isDraggingQuestion.value = false;
    draggedQuestionIndex.value = null;
};

const selectSection = () => {
    selectedQuestionId.value = null;
    emit("select", { type: "section", id: props.section.id });
};

const selectQuestion = (questionId) => {
    selectedQuestionId.value = questionId;
    emit("select", { type: "question", id: questionId });
};

const onEmptySectionDragOver = (event) => {
    isEmptySectionDragOver.value = true;

    try {
        // Try to validate the dragged item
        const dataString = event.dataTransfer.getData("text/plain");
        if (!dataString) {
            // On Firefox and some browsers, we can't read data on dragover
            // Default to true for better UX
            isValidTargetForDrop.value = true;
            return;
        }

        const data = JSON.parse(dataString);
        // Only components from sidebar are valid, not questions (since section is empty)
        isValidTargetForDrop.value = data.sourceType === "component";
    } catch (error) {
        console.log("Error reading dragover data:", error);
        // Default to true if we can't read the data
        isValidTargetForDrop.value = true;
    }
};

const onEmptySectionDragLeave = (event) => {
    // Make sure we're not triggering when moving to a child element
    if (!event.currentTarget.contains(event.relatedTarget)) {
        isEmptySectionDragOver.value = false;
        isValidTargetForDrop.value = false;
    }
};

const onEmptySectionDrop = (event) => {
    isEmptySectionDragOver.value = false;

    try {
        let dataString = event.dataTransfer.getData("application/json");
        if (!dataString) {
            dataString = event.dataTransfer.getData("text/plain");
        }

        if (!dataString) {
            console.log("No valid data found in drop event");
            return;
        }

        const data = JSON.parse(dataString);
        if (data && data.item && data.sourceType === "component") {
            // Create drop data for position 0 (beginning of empty section)
            const customDropData = {
                item: data.item,
                sourceType: "component",
                targetType: "section",
                targetId: props.section.id,
                targetPosition: 0, // Add at the beginning
            };

            // Handle the drop
            handleDrop(customDropData);
        }
    } catch (error) {
        console.error("Error parsing drop data:", error);
    }
};
</script>

<style scoped>
.section {
    transition: border-color 0.15s ease-in-out;
}

.empty-section {
    min-height: 180px;
    position: relative;
}

.questions-container {
    position: relative;
}

.question-item {
    position: relative;
}
</style>
