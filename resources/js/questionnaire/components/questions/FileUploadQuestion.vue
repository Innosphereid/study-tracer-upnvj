<template>
    <div class="question-component">
        <div>
            <label class="block text-sm font-medium text-gray-700">
                {{ question.text }}
                <span v-if="question.required" class="text-red-500">*</span>
            </label>

            <p v-if="question.helpText" class="mt-1 text-sm text-gray-500">
                {{ question.helpText }}
            </p>

            <div class="mt-2">
                <div
                    class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                    :class="{
                        'bg-gray-50': !isDragging && !isBuilder,
                        'bg-indigo-50 border-indigo-300':
                            isDragging && !isBuilder,
                        'opacity-60 cursor-not-allowed': isBuilder,
                    }"
                    @dragover.prevent="onDragOver"
                    @dragleave.prevent="onDragLeave"
                    @drop.prevent="onDrop"
                >
                    <div class="space-y-1 text-center">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 48 48"
                            aria-hidden="true"
                        >
                            <path
                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        <div
                            v-if="files.length === 0"
                            class="flex text-sm text-gray-600"
                        >
                            <label
                                :for="`file-upload-${question.id}`"
                                class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                                :class="{
                                    'opacity-60 cursor-not-allowed': isBuilder,
                                }"
                            >
                                <span>Upload a file</span>
                                <input
                                    :id="`file-upload-${question.id}`"
                                    :name="`file-upload-${question.id}`"
                                    type="file"
                                    class="sr-only"
                                    @change="onFileSelect"
                                    :accept="acceptedFileTypes"
                                    :multiple="question.maxFiles > 1"
                                    :disabled="isBuilder"
                                />
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>

                        <p class="text-xs text-gray-500">
                            {{ fileTypeText }} up to {{ question.maxSize }}MB
                            <span v-if="question.maxFiles > 1"
                                >(max {{ question.maxFiles }} files)</span
                            >
                        </p>
                    </div>
                </div>

                <!-- File Preview -->
                <div v-if="files.length > 0" class="mt-4 space-y-2">
                    <div class="text-sm font-medium text-gray-700">
                        Uploaded Files:
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li
                            v-for="(file, index) in files"
                            :key="index"
                            class="py-2 flex justify-between items-center"
                        >
                            <div class="flex items-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-gray-400 mr-2"
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
                                <span
                                    class="text-sm text-gray-700 truncate"
                                    :title="file.name"
                                >
                                    {{ file.name }} ({{
                                        formatFileSize(file.size)
                                    }})
                                </span>
                            </div>
                            <button
                                type="button"
                                @click="removeFile(index)"
                                class="ml-2 text-sm text-red-600 hover:text-red-800 focus:outline-none"
                                :disabled="isBuilder"
                            >
                                Remove
                            </button>
                        </li>
                    </ul>

                    <!-- Add more files button if maxFiles > 1 -->
                    <div
                        v-if="
                            question.maxFiles > 1 &&
                            files.length < question.maxFiles
                        "
                        class="mt-2"
                    >
                        <label
                            :for="`file-upload-more-${question.id}`"
                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer"
                            :class="{
                                'opacity-60 cursor-not-allowed': isBuilder,
                            }"
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
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Add another file
                            <input
                                :id="`file-upload-more-${question.id}`"
                                :name="`file-upload-more-${question.id}`"
                                type="file"
                                class="sr-only"
                                @change="onFileSelect"
                                :accept="acceptedFileTypes"
                                :disabled="isBuilder"
                            />
                        </label>
                    </div>
                </div>
            </div>

            <div v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, computed, watch } from "vue";

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
    isBuilder: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Internal state
const files = ref([]);
const isDragging = ref(false);

// Setup accepted file types string for input element
const acceptedFileTypes = computed(() => props.question.allowedTypes.join(","));

// Human-readable file types
const fileTypeText = computed(() => {
    if (props.question.allowedTypes.includes("*/*")) {
        return "Any file type";
    }

    const typeMap = {
        "image/*": "Images",
        "application/pdf": "PDFs",
        "text/*": "Text files",
        "application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            "Word documents",
        "application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
            "Excel files",
    };

    const typeNames = props.question.allowedTypes.map(
        (type) => typeMap[type] || type
    );

    if (typeNames.length === 1) {
        return typeNames[0];
    } else if (typeNames.length === 2) {
        return `${typeNames[0]} or ${typeNames[1]}`;
    } else {
        const lastType = typeNames.pop();
        return `${typeNames.join(", ")}, or ${lastType}`;
    }
});

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal && Array.isArray(newVal)) {
            files.value = [...newVal];
        }
    },
    { deep: true }
);

// Format file size to human-readable format
const formatFileSize = (sizeInBytes) => {
    if (sizeInBytes < 1024) {
        return sizeInBytes + " B";
    } else if (sizeInBytes < 1024 * 1024) {
        return (sizeInBytes / 1024).toFixed(1) + " KB";
    } else {
        return (sizeInBytes / (1024 * 1024)).toFixed(1) + " MB";
    }
};

// Event handlers
const onDragOver = () => {
    if (!props.isBuilder) {
        isDragging.value = true;
    }
};

const onDragLeave = () => {
    isDragging.value = false;
};

const onDrop = (event) => {
    isDragging.value = false;

    if (props.isBuilder) return;

    const droppedFiles = event.dataTransfer.files;
    handleFiles(droppedFiles);
};

const onFileSelect = (event) => {
    if (props.isBuilder) return;

    const selectedFiles = event.target.files;
    handleFiles(selectedFiles);

    // Reset the input to allow selecting the same file again
    event.target.value = "";
};

const handleFiles = (fileList) => {
    // Check max files limit
    if (files.value.length + fileList.length > props.question.maxFiles) {
        emit("validate", {
            isValid: false,
            errorMessage: `You can upload a maximum of ${
                props.question.maxFiles
            } file${props.question.maxFiles > 1 ? "s" : ""}.`,
        });
        return;
    }

    // Process each file
    for (let i = 0; i < fileList.length; i++) {
        const file = fileList[i];

        // Check file type
        const isValidType = validateFileType(file);
        if (!isValidType) {
            emit("validate", {
                isValid: false,
                errorMessage: `File type not allowed: ${file.name}. Please upload ${fileTypeText.value}.`,
            });
            continue;
        }

        // Check file size
        if (file.size > props.question.maxSize * 1024 * 1024) {
            emit("validate", {
                isValid: false,
                errorMessage: `File too large: ${file.name}. Maximum size is ${props.question.maxSize}MB.`,
            });
            continue;
        }

        // Add valid file to the list
        files.value.push(file);
    }

    // Update model value
    updateValue();
};

const validateFileType = (file) => {
    // If all types are allowed
    if (props.question.allowedTypes.includes("*/*")) {
        return true;
    }

    // Check file MIME type against allowed types
    return props.question.allowedTypes.some((type) => {
        if (type.endsWith("/*")) {
            // Handle wildcard types like "image/*"
            const category = type.split("/")[0];
            return file.type.startsWith(category + "/");
        } else {
            // Exact type match
            return file.type === type;
        }
    });
};

const removeFile = (index) => {
    files.value.splice(index, 1);
    updateValue();
};

const updateValue = () => {
    emit("update:modelValue", [...files.value]);
    validate();
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && files.value.length === 0) {
        isValid = false;
        errorMessage = "Please upload at least one file.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>
