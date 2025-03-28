<template>
    <div class="file-upload-question">
        <div
            class="border-2 border-dashed rounded-lg p-6 transition-all duration-200"
            :class="[
                isDragging
                    ? 'border-indigo-400 bg-indigo-50'
                    : 'border-gray-300 hover:border-indigo-300',
                error ? 'border-red-300 bg-red-50' : '',
            ]"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
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
                        stroke-width="1.5"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                    />
                </svg>

                <p class="mt-2 text-sm text-gray-600">
                    <span class="font-medium text-indigo-600"
                        >Klik untuk mengunggah</span
                    >
                    atau seret dan lepas
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    {{ fileTypeText }}
                </p>

                <input
                    type="file"
                    ref="fileInput"
                    class="hidden"
                    :multiple="question.maxFiles > 1"
                    :accept="acceptTypes"
                    @change="handleFileInput"
                />

                <button
                    type="button"
                    class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    @click="$refs.fileInput.click()"
                >
                    Pilih File
                </button>
            </div>

            <!-- Preview of selected files -->
            <div v-if="selectedFiles.length > 0" class="mt-6 space-y-2">
                <h4 class="text-sm font-medium text-gray-700">
                    File terpilih:
                </h4>
                <ul class="divide-y divide-gray-200">
                    <li
                        v-for="(file, index) in selectedFiles"
                        :key="index"
                        class="py-2 flex justify-between items-center"
                    >
                        <div class="flex items-center space-x-3">
                            <span class="flex-shrink-0">
                                <svg
                                    v-if="file.type.includes('image')"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-blue-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <svg
                                    v-else-if="file.type.includes('pdf')"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-red-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                <svg
                                    v-else
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
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                    />
                                </svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-medium text-gray-900 truncate"
                                >
                                    {{ file.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ formatFileSize(file.size) }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="ml-4 text-red-600 hover:text-red-900 focus:outline-none"
                            @click="removeFile(index)"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// File input ref
const fileInput = ref(null);
const isDragging = ref(false);
const selectedFiles = ref([]);

// Compute acceptable file types from question settings
const acceptTypes = computed(() => {
    if (props.question.allowedTypes && props.question.allowedTypes.length > 0) {
        return props.question.allowedTypes.join(",");
    }
    // Default to common image types if nothing specified
    return "image/jpeg,image/png,image/gif,application/pdf";
});

// Display text about accepted file types
const fileTypeText = computed(() => {
    if (props.question.allowedTypes && props.question.allowedTypes.length > 0) {
        // Check if all file types are allowed
        if (props.question.allowedTypes.includes("*/*")) {
            return `Format yang diterima: Semua jenis file | Maks. ${
                props.question.maxFiles || 1
            } file`;
        }

        // Format the allowed types for human reading
        const types = props.question.allowedTypes.map((type) => {
            if (type === "image/jpeg") return "JPG";
            if (type === "image/png") return "PNG";
            if (type === "image/gif") return "GIF";
            if (type === "application/pdf") return "PDF";
            if (type.includes("word")) return "DOC";
            if (type.includes("excel")) return "XLS";
            if (type.includes("powerpoint")) return "PPT";
            if (type === "text/plain") return "TXT";
            return type.split("/")[1].toUpperCase();
        });

        return `Format yang diterima: ${types.join(", ")} | Maks. ${
            props.question.maxFiles || 1
        } file`;
    }

    return `Maks. ${props.question.maxFiles || 1} file`;
});

// Handle file input change
const handleFileInput = (event) => {
    const files = Array.from(event.target.files);
    handleFiles(files);
};

// Handle drop event
const handleDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files);
    handleFiles(files);
};

// Process files for both input and drop
const handleFiles = (files) => {
    // Check max files limit
    const maxFiles = props.question.maxFiles || 1;
    if (selectedFiles.value.length + files.length > maxFiles) {
        // Truncate to max allowed
        files = files.slice(0, maxFiles - selectedFiles.value.length);
    }

    // Add files to selected list
    for (const file of files) {
        selectedFiles.value.push(file);
    }

    // Update model value with File objects
    emit("update:modelValue", selectedFiles.value);

    // Reset file input to allow selecting the same file again
    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

// Remove a file from the selection
const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    emit("update:modelValue", selectedFiles.value);
};

// Format file size for display
const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};
</script>

<style scoped>
.file-upload-question input[type="file"] {
    display: none;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
