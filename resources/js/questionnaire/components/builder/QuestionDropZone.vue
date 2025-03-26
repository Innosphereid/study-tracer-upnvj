<template>
    <div
        class="question-drop-zone transition-all duration-150"
        :class="{
            'drop-zone-expanded': isOverFromComponent || isDraggingComponent,
            'drop-zone-normal': !isOverFromComponent && !isDraggingComponent,
        }"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
    >
        <div
            class="w-full h-full rounded-md transition-colors"
            :class="{
                'bg-indigo-200 border-2 border-dashed border-indigo-400 shadow-md':
                    isOver && isValidTarget,
                'bg-red-200 border-2 border-dashed border-red-400':
                    isOver && !isValidTarget,
                'border border-gray-300 border-dashed':
                    !isOver && isDraggingComponent,
                'bg-gray-200': !isOver && !isDraggingComponent,
            }"
        >
            <div
                v-if="isOver && isValidTarget"
                class="flex items-center justify-center h-full"
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
                        d="M5 10l7-7m0 0l7 7m-7-7v18"
                    />
                </svg>
                <span class="ml-2 text-sm font-medium text-indigo-600"
                    >Letakkan di sini</span
                >
            </div>
            <div
                v-else-if="!isOver && isDraggingComponent"
                class="flex items-center justify-center h-full opacity-50"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-gray-500"
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
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, inject, watch } from "vue";

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

// Inject the global dragging state
const isDraggingComponent = inject("isDraggingComponent", ref(false));
const isDraggingQuestion = inject("isDraggingQuestion", ref(false));

const onDragOver = (event) => {
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
}

.drop-zone-expanded {
    height: 40px; /* Expanded when user is dragging a component or hovering over the drop zone */
}

.question-drop-zone:hover {
    height: 20px; /* Subtle expansion on hover to make it easier to target */
}
</style>
