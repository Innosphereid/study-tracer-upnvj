<template>
    <div
        class="question-wrapper p-4 border rounded-md transition-all"
        :class="{
            'border-indigo-500 bg-indigo-50': isSelected,
            'border-gray-200 hover:border-indigo-300 hover:shadow-sm':
                !isSelected,
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
        <div class="question-preview">
            <!-- Short Text -->
            <div v-if="question.type === 'short-text'" class="mt-1">
                <input
                    type="text"
                    disabled
                    :placeholder="question.placeholder || 'Jawaban singkat'"
                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                />
            </div>

            <!-- Long Text -->
            <div v-else-if="question.type === 'long-text'" class="mt-1">
                <textarea
                    disabled
                    :placeholder="question.placeholder || 'Jawaban panjang'"
                    :rows="question.rows || 3"
                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                ></textarea>
            </div>

            <!-- Radio options -->
            <div v-else-if="question.type === 'radio'" class="mt-2 space-y-2">
                <div
                    v-for="option in question.options"
                    :key="option.id"
                    class="flex items-center"
                >
                    <div class="flex items-center h-5">
                        <input
                            type="radio"
                            disabled
                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label class="font-medium text-gray-700">{{
                            option.text
                        }}</label>
                    </div>
                </div>
                <div v-if="question.allowOther" class="flex items-center">
                    <div class="flex items-center h-5">
                        <input
                            type="radio"
                            disabled
                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
                        />
                    </div>
                    <div class="ml-3 text-sm flex items-center">
                        <label class="font-medium text-gray-700 mr-2"
                            >Lainnya:</label
                        >
                        <input
                            type="text"
                            disabled
                            class="shadow-sm border border-gray-300 rounded-md py-1 px-2 bg-gray-50 text-gray-500 cursor-not-allowed text-xs"
                        />
                    </div>
                </div>
            </div>

            <!-- Checkbox options -->
            <div
                v-else-if="question.type === 'checkbox'"
                class="mt-2 space-y-2"
            >
                <div
                    v-for="option in question.options"
                    :key="option.id"
                    class="flex items-center"
                >
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            disabled
                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 rounded"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label class="font-medium text-gray-700">{{
                            option.text
                        }}</label>
                    </div>
                </div>
                <div v-if="question.allowOther" class="flex items-center">
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            disabled
                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 rounded"
                        />
                    </div>
                    <div class="ml-3 text-sm flex items-center">
                        <label class="font-medium text-gray-700 mr-2"
                            >Lainnya:</label
                        >
                        <input
                            type="text"
                            disabled
                            class="shadow-sm border border-gray-300 rounded-md py-1 px-2 bg-gray-50 text-gray-500 cursor-not-allowed text-xs"
                        />
                    </div>
                </div>
            </div>

            <!-- Dropdown -->
            <div v-else-if="question.type === 'dropdown'" class="mt-1">
                <select
                    disabled
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 bg-gray-50 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md cursor-not-allowed"
                >
                    <option>-- Pilih Opsi --</option>
                    <option v-for="option in question.options" :key="option.id">
                        {{ option.text }}
                    </option>
                    <option v-if="question.allowOther">Lainnya...</option>
                </select>
            </div>

            <!-- Rating -->
            <div v-else-if="question.type === 'rating'" class="mt-2">
                <div class="flex items-center justify-center space-x-1">
                    <template v-for="n in question.maxRating || 5" :key="n">
                        <span class="text-gray-300">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                        </span>
                    </template>
                </div>
            </div>

            <!-- File Upload -->
            <div v-else-if="question.type === 'file-upload'" class="mt-1">
                <div
                    class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50"
                >
                    <div class="space-y-1 text-center">
                        <svg
                            class="mx-auto h-8 w-8 text-gray-400"
                            xmlns="http://www.w3.org/2000/svg"
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
                        <p class="text-xs text-gray-500">
                            Upload file
                            {{ question.allowedTypes?.join(", ") || "" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Default/Not Implemented -->
            <div
                v-else
                class="mt-1 py-2 px-3 text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-md"
            >
                [Preview untuk tipe pertanyaan {{ question.type }}]
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from "vue";

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
]);

const isDragging = ref(false);

const selectQuestion = () => {
    emit("select", props.question.id);
};

const onDragStart = (event) => {
    isDragging.value = true;

    // Set data untuk transfer
    event.dataTransfer.effectAllowed = "move";

    try {
        const dragData = {
            item: props.question,
            sourceType: "question",
            sourceIndex: props.index,
            sectionId: props.sectionId,
        };

        const jsonData = JSON.stringify(dragData);
        event.dataTransfer.setData("application/json", jsonData);
        event.dataTransfer.setData("text/plain", jsonData);

        // Tambahkan kelas visual saat dragging
        event.target.classList.add("opacity-50");

        emit("dragstart", dragData);
    } catch (error) {
        console.error("Error setting drag data:", error);
    }
};

const onDragEnd = () => {
    isDragging.value = false;
    // Hapus kelas visual
    event.target.classList.remove("opacity-50");
    emit("dragend");
};

// Helper methods for displaying type information
const getTypeLabel = (type) => {
    const labels = {
        "short-text": "Teks Pendek",
        "long-text": "Teks Panjang",
        email: "Email",
        phone: "Nomor Telepon",
        number: "Angka",
        date: "Tanggal",
        radio: "Pilihan Ganda",
        checkbox: "Kotak Centang",
        dropdown: "Dropdown",
        rating: "Rating Bintang",
        likert: "Skala Likert",
        "yes-no": "Ya/Tidak",
        "file-upload": "Upload File",
        matrix: "Matriks",
        slider: "Slider",
        ranking: "Rangking",
    };

    return labels[type] || type;
};

const getTypeClass = (type) => {
    const typeCategories = {
        dasar: ["short-text", "long-text", "email", "phone", "number", "date"],
        pilihan: [
            "radio",
            "checkbox",
            "dropdown",
            "rating",
            "likert",
            "yes-no",
        ],
        lanjutan: ["file-upload", "matrix", "slider", "ranking"],
    };

    // Determine category based on type
    let category = "dasar";
    Object.entries(typeCategories).forEach(([cat, types]) => {
        if (types.includes(type)) category = cat;
    });

    // Return appropriate class based on category
    switch (category) {
        case "dasar":
            return "bg-blue-100 text-blue-800";
        case "pilihan":
            return "bg-green-100 text-green-800";
        case "lanjutan":
            return "bg-purple-100 text-purple-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};
</script>

<style scoped>
.question-wrapper {
    cursor: pointer;
    transition: all 0.2s ease;
}

.question-wrapper.dragging {
    opacity: 0.5;
    transform: scale(0.98);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.drag-handle {
    opacity: 0.5;
    transition: opacity 0.2s;
}

.question-wrapper:hover .drag-handle {
    opacity: 1;
}
</style>
