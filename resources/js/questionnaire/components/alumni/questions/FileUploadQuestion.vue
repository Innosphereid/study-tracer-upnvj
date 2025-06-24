<template>
    <div class="file-upload-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <!-- File upload area -->
                <div
                    class="file-upload-area flex flex-col items-center justify-center p-6 border-2 border-dashed rounded-lg cursor-pointer"
                    :class="{
                        'border-indigo-300 bg-indigo-50': isDragging,
                        'border-gray-300 hover:border-indigo-300 hover:bg-indigo-50':
                            !isDragging && !error,
                        'border-red-300': error,
                    }"
                    @dragenter.prevent="isDragging = true"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleFileDrop"
                    @click="$refs.fileInput.click()"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        class="hidden"
                        :multiple="allowMultiple"
                        :accept="acceptedFileTypes"
                        @change="handleFileSelection"
                    />

                    <svg
                        class="w-10 h-10 mb-3 text-gray-400"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                        />
                    </svg>

                    <p class="mb-2 text-sm text-gray-600">
                        <span class="font-semibold">Klik untuk upload</span>
                        atau drag &amp; drop
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ fileTypeMessage }}
                    </p>
                </div>

                <!-- File list -->
                <div v-if="filesPreview.length > 0" class="mt-4 space-y-3">
                    <div
                        v-for="(file, index) in filesPreview"
                        :key="index"
                        class="flex items-center justify-between p-3 border rounded-lg bg-gray-50"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg
                                    class="w-6 h-6 text-gray-500"
                                    xmlns="http://www.w3.org/2000/svg"
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
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="text-sm font-medium text-gray-900 truncate"
                                    :title="file.name"
                                >
                                    {{ file.name }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ formatFileSize(file.size) }}
                                </span>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="p-1 text-gray-500 hover:text-red-500"
                            @click.stop="removeFile(index)"
                        >
                            <svg
                                class="w-5 h-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <transition name="fade">
                <p v-if="error" class="mt-2 text-sm text-red-600">
                    {{ error }}
                </p>
            </transition>
        </question-container>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed } from "vue";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

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

const emit = defineEmits(["update:modelValue", "validate"]);

const fileInput = ref(null);
const isDragging = ref(false);
const filesPreview = ref([]);

// Parse settings
const settings = computed(() => {
    if (!props.question.settings) return {};

    if (typeof props.question.settings === "string") {
        try {
            return JSON.parse(props.question.settings);
        } catch (e) {
            console.error("Failed to parse settings:", e);
            return {};
        }
    }

    return props.question.settings;
});

// Check if multiple files are allowed
const allowMultiple = computed(() => {
    return settings.value.allowMultiple || false;
});

// Get max file size (in bytes)
const maxFileSize = computed(() => {
    return (settings.value.maxFileSize || 5) * 1024 * 1024; // Default to 5MB
});

// Get accepted file types
const acceptedFileTypes = computed(() => {
    const types = settings.value.acceptedFileTypes || [
        "image/*",
        ".pdf",
        ".doc",
        ".docx",
    ];
    return types.join(",");
});

// Human-readable file type message
const fileTypeMessage = computed(() => {
    const types = settings.value.acceptedFileTypes || [
        "image/*",
        ".pdf",
        ".doc",
        ".docx",
    ];
    const maxSize = settings.value.maxFileSize || 5;

    let message = `${types.join(", ")} (max. ${maxSize}MB`;
    if (allowMultiple.value) {
        message += ` per file)`;
    } else {
        message += `)`;
    }

    return message;
});

// Handle file selection from input
const handleFileSelection = (event) => {
    const files = event.target.files;
    if (!files || files.length === 0) return;

    processFiles(files);
};

// Handle file drop
const handleFileDrop = (event) => {
    isDragging.value = false;
    const files = event.dataTransfer.files;
    if (!files || files.length === 0) return;

    processFiles(files);
};

// Process files
const processFiles = (fileList) => {
    let validFiles = [];

    for (let i = 0; i < fileList.length; i++) {
        const file = fileList[i];

        // Skip if file is too large
        if (file.size > maxFileSize.value) {
            alert(
                `File "${file.name}" terlalu besar (maksimal ${
                    settings.value.maxFileSize || 5
                }MB).`
            );
            continue;
        }

        // If we don't allow multiple files, just use the first valid file
        if (!allowMultiple.value) {
            filesPreview.value = [file];
            validFiles = [file];
            break;
        }

        validFiles.push(file);
    }

    // If multiple files are allowed, append to existing files
    if (allowMultiple.value) {
        filesPreview.value = [...filesPreview.value, ...validFiles];
    }

    emit("update:modelValue", [...filesPreview.value]);
    validate();

    // Reset the file input
    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

// Remove a file
const removeFile = (index) => {
    filesPreview.value.splice(index, 1);
    emit("update:modelValue", [...filesPreview.value]);
    validate();
};

// Format file size to human-readable format
const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";

    const sizes = ["Bytes", "KB", "MB", "GB", "TB"];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));

    return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + " " + sizes[i];
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && filesPreview.value.length === 0) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
.file-upload-area {
    transition: all 0.2s ease;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
