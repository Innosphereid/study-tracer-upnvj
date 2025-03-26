<template>
    <div
        class="question-wrapper p-4 border rounded-md transition-all"
        :class="{
            'border-indigo-500 bg-indigo-50': isSelected,
            'border-gray-200 hover:border-indigo-300 hover:shadow-sm':
                !isSelected,
            dragging: isDragging,
        }"
        @click.stop="selectQuestion"
        draggable="true"
        @dragstart="onDragStart"
        @dragend="onDragEnd"
    >
        <!-- Question Type Badge -->
        <div class="flex justify-between items-start mb-3">
            <div class="flex items-center">
                <div class="drag-handle mr-2 text-gray-400 cursor-move">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 8h16M4 16h16"
                        />
                    </svg>
                </div>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getTypeClass(question.type)"
                >
                    {{ getTypeLabel(question.type) }}
                </span>
                <span v-if="question.required" class="ml-2 text-red-500 text-xs"
                    >*Wajib</span
                >
            </div>

            <!-- Actions -->
            <div class="flex">
                <button
                    type="button"
                    class="p-1 text-gray-400 hover:text-indigo-500 focus:outline-none"
                    @click.stop="$emit('duplicate', question.id)"
                    title="Duplikasi Pertanyaan"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
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
                    @click.stop="$emit('delete', question.id)"
                    title="Hapus Pertanyaan"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
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

        <!-- Question Text -->
        <div class="mb-2">
            <h4 class="text-sm font-medium text-gray-900">
                {{ question.text }}
            </h4>
            <p v-if="question.helpText" class="mt-1 text-xs text-gray-500">
                {{ question.helpText }}
            </p>
        </div>

        <!-- Question Preview -->
        <DynamicQuestionPreview
            :question="question"
            @add-options="(count) => $emit('add-options', count)"
        />
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from "vue";
import DynamicQuestionPreview from "./preview/DynamicQuestionPreview.vue";
import { getTypeLabel, getTypeClass } from "./preview/QuestionPreviewRegistry";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    sectionId: {
        type: String,
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
    "duplicate",
    "delete",
    "dragstart",
    "dragend",
    "add-options",
]);

const isDragging = ref(false);

const selectQuestion = () => {
    emit("select", props.question.id);
};

const onDragStart = (event) => {
    isDragging.value = true;

    // Add visual feedback for drag operation
    event.target.classList.add("dragging");

    // Set data untuk transfer
    event.dataTransfer.effectAllowed = "move";

    try {
        // Create drag ghost image to show while dragging
        const ghostElement = event.target.cloneNode(true);
        ghostElement.style.width = `${event.target.offsetWidth}px`;
        ghostElement.classList.add("drag-ghost");
        ghostElement.style.opacity = "0.8";
        document.body.appendChild(ghostElement);

        // Set custom drag image with offset
        const rect = event.target.getBoundingClientRect();
        const offsetX = event.clientX - rect.left;
        const offsetY = event.clientY - rect.top;
        event.dataTransfer.setDragImage(ghostElement, offsetX, offsetY);

        // Remove ghost element after it's been used
        setTimeout(() => {
            document.body.removeChild(ghostElement);
        }, 0);

        const dragData = {
            item: props.question,
            sourceType: "question",
            sourceIndex: props.index,
            sectionId: props.sectionId,
        };

        const jsonData = JSON.stringify(dragData);
        event.dataTransfer.setData("application/json", jsonData);
        event.dataTransfer.setData("text/plain", jsonData);

        emit("dragstart", dragData);
    } catch (error) {
        console.error("Error setting drag data:", error);
    }
};

const onDragEnd = () => {
    isDragging.value = false;

    // Remove visual feedback
    event.target.classList.remove("dragging");

    emit("dragend");
};
</script>

<style scoped>
.question-wrapper {
    cursor: pointer;
    transition: all 0.2s ease;
    transform-origin: center center;
    will-change: transform, box-shadow, opacity;
}

.question-wrapper.dragging {
    opacity: 0.7;
    transform: scale(0.98);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: #6366f1;
    background-color: #eef2ff;
    z-index: 10;
    position: relative;
}

.drag-ghost {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
    z-index: -1;
}

.drag-handle {
    opacity: 0.5;
    transition: opacity 0.2s;
}

.question-wrapper:hover .drag-handle {
    opacity: 1;
}
</style>
