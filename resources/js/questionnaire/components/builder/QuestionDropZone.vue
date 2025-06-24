<template>
    <div
        class="question-drop-zone transition-all duration-150"
        :class="{
            'drop-zone-expanded':
                isOver || (isDraggingQuestion && !isRestricted),
            'drop-zone-normal':
                !isOver && (!isDraggingQuestion || isRestricted),
            'drop-zone-restricted': isDraggingQuestion && isRestricted,
        }"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
    >
        <div
            class="w-full h-full rounded-md transition-colors flex items-center justify-center"
            :class="{
                'bg-indigo-200 border-2 border-dashed border-indigo-500 shadow-md':
                    isOver && isValidTarget,
                'bg-red-200 border-2 border-dashed border-red-400':
                    isOver && !isValidTarget,
                'border border-indigo-300 border-dashed bg-indigo-50 bg-opacity-50':
                    !isOver && isDraggingQuestion && !isRestricted,
                'bg-gray-200': !isOver && !isDraggingQuestion,
                hidden: isDraggingQuestion && isRestricted,
            }"
        >
            <div
                v-if="isOver && isValidTarget"
                class="flex items-center justify-center h-full w-full"
            >
                <div class="flex flex-row items-center animate-pulse">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 text-indigo-600 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 10l7-7m0 0l7 7m-7-7v18"
                        />
                    </svg>
                    <span class="text-sm font-medium text-indigo-600"
                        >Letakkan di sini</span
                    >
                </div>
            </div>
            <div
                v-else-if="!isOver && isDraggingQuestion && !isRestricted"
                class="flex items-center justify-center h-full w-full px-2"
            >
                <div class="flex flex-row items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-indigo-400 mr-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                        />
                    </svg>
                    <span
                        class="text-xs text-indigo-400 whitespace-nowrap truncate"
                    >
                        {{
                            isFirstPosition
                                ? "Pindahkan ke awal"
                                : isLastPosition
                                ? "Pindahkan ke akhir"
                                : "Pindahkan di antara"
                        }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, inject, watch, computed } from "vue";

const props = defineProps({
    sectionId: {
        type: String,
        required: true,
    },
    targetIndex: {
        type: Number,
        required: true,
    },
    acceptTypes: {
        type: Array,
        default: () => ["question", "component"],
    },
    isFirstPosition: {
        type: Boolean,
        default: false,
    },
    isLastPosition: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["drop"]);

const isOver = ref(false);
const isValidTarget = ref(false);
const isOverFromComponent = ref(false);
const draggedQuestionIndex = ref(null);

// Inject the global dragging state
const isDraggingComponent = inject("isDraggingComponent", ref(false));
const isDraggingQuestion = inject("isDraggingQuestion", ref(false));
const currentDraggedQuestionIndex = inject("draggedQuestionIndex", ref(null));

// Compute if this drop zone should be restricted based on position
const isRestricted = computed(() => {
    if (!isDraggingQuestion.value || currentDraggedQuestionIndex.value === null)
        return false;

    // Prevent moving a question before itself or after itself (no change)
    if (
        currentDraggedQuestionIndex.value === props.targetIndex ||
        currentDraggedQuestionIndex.value === props.targetIndex - 1
    ) {
        return true;
    }

    // First position restrictions - can't move items to before the first item
    if (
        props.isFirstPosition &&
        currentDraggedQuestionIndex.value < props.targetIndex
    ) {
        return true;
    }

    // Last position restrictions - can't move items to after the last item
    if (
        props.isLastPosition &&
        currentDraggedQuestionIndex.value > props.targetIndex - 1
    ) {
        return true;
    }

    return false;
});

const onDragOver = (event) => {
    // Don't allow drop if restricted
    if (isRestricted.value) {
        event.preventDefault();
        return;
    }

    event.preventDefault();
    event.dataTransfer.dropEffect = "move";

    if (!isOver.value) {
        isOver.value = true;

        // Validasi untuk pembatasan drag di posisi pertama/terakhir
        isValidTarget.value = true;

        try {
            // Mencoba membaca data untuk validasi
            const dataString = event.dataTransfer.getData("text/plain");
            if (dataString) {
                const data = JSON.parse(dataString);

                // Tentukan apakah drag berasal dari component atau question
                isOverFromComponent.value = data.sourceType === "component";
                draggedQuestionIndex.value = data.sourceIndex;

                if (data.sourceType === "question") {
                    // Jika ini posisi pertama dan pertanyaan tidak boleh ke atas,
                    // atau jika ini posisi terakhir dan pertanyaan tidak boleh ke bawah
                    if (
                        (props.isFirstPosition &&
                            data.sourceIndex > props.targetIndex) ||
                        (props.isLastPosition &&
                            data.sourceIndex < props.targetIndex)
                    ) {
                        isValidTarget.value = false;
                    }

                    // Prevent dropping a question at its own position or right after itself
                    if (
                        data.sourceIndex === props.targetIndex ||
                        data.sourceIndex === props.targetIndex - 1
                    ) {
                        isValidTarget.value = false;
                    }
                } else if (data.sourceType === "component") {
                    // Komponen dari sidebar selalu valid untuk drop di mana saja
                    isValidTarget.value = true;
                } else {
                    isValidTarget.value = props.acceptTypes.includes(
                        data.sourceType
                    );
                }
            }
        } catch (error) {
            // Jika tidak bisa baca data, tetap anggap valid (akan divalidasi saat drop)
            console.log("Cannot read dragover data:", error);
            // Asumsi ini adalah component jika kita tidak bisa menentukan
            isOverFromComponent.value = true;
        }
    }
};

const onDragLeave = (event) => {
    // Pastikan kita tidak trigger dragleave saat pointer ke element anak
    if (!event.currentTarget.contains(event.relatedTarget)) {
        isOver.value = false;
        isValidTarget.value = false;
        isOverFromComponent.value = false;
    }
};

const onDrop = (event) => {
    // Don't allow drop if restricted
    if (isRestricted.value) {
        event.preventDefault();
        return;
    }

    event.preventDefault();
    isOver.value = false;
    isOverFromComponent.value = false;

    try {
        let dataString = event.dataTransfer.getData("application/json");
        if (!dataString) {
            dataString = event.dataTransfer.getData("text/plain");
        }

        if (!dataString) {
            console.log("No valid data format found in drop event");
            return;
        }

        const data = JSON.parse(dataString);
        if (data && data.item) {
            // Tambahkan targetIndex ke data drop
            emit("drop", {
                item: data.item,
                sourceType: data.sourceType,
                sourceIndex: data.sourceIndex,
                sectionId: props.sectionId,
                targetIndex: props.targetIndex,
            });
        }
    } catch (error) {
        console.error("Error parsing drop data:", error);
    }
};
</script>

<style scoped>
.question-drop-zone {
    width: 100%;
    transition: all 0.2s ease;
}

.drop-zone-normal {
    height: 2px;
    background: linear-gradient(
        to right,
        transparent 0%,
        rgba(209, 213, 219, 0.5) 5%,
        rgba(209, 213, 219, 0.5) 95%,
        transparent 100%
    );
    margin: 8px 0;
}

.drop-zone-expanded {
    height: 40px; /* Expanded when user is dragging a component or hovering over the drop zone */
    margin: 4px 0;
    border-radius: 6px;
}

.drop-zone-restricted {
    height: 0px;
    overflow: hidden;
    opacity: 0;
    margin: 0;
}

.question-drop-zone:hover {
    height: 20px; /* Subtle expansion on hover to make it easier to target */
    background: rgba(79, 70, 229, 0.1); /* Light indigo color */
    border-radius: 4px;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-pulse {
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
