<template>
    <div>
        <QuestionSettingsPanel
            :question="question"
            @update:question="updateQuestionBase"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        >
            <template #type-specific-settings>
                <!-- File Upload specific settings -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Pengaturan Upload File
                    </h3>

                    <!-- File Type Selection -->
                    <div class="mb-4">
                        <label
                            for="file-type"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Jenis File
                        </label>
                        <select
                            id="file-type"
                            v-model="fileCategory"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="onFileCategoryChange"
                        >
                            <option value="image">Gambar</option>
                            <option value="document">Dokumen</option>
                            <option value="all">Semua File</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Tentukan jenis file yang dapat diunggah
                        </p>
                    </div>

                    <!-- File Extensions Checkboxes -->
                    <div v-if="fileCategory !== 'all'" class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Ekstensi File yang Diizinkan
                        </label>
                        <div class="space-y-2">
                            <div
                                v-for="(option, index) in availableFileTypes"
                                :key="index"
                                class="flex items-center"
                            >
                                <input
                                    type="checkbox"
                                    :id="`file-type-${index}`"
                                    v-model="selectedFileTypes"
                                    :value="option.value"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    @change="updateAllowedTypes"
                                />
                                <label
                                    :for="`file-type-${index}`"
                                    class="ml-2 block text-sm text-gray-700"
                                >
                                    {{ option.label }}
                                </label>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Centang jenis file yang dapat diunggah pengguna
                        </p>
                    </div>

                    <!-- Max Files Setting -->
                    <div class="mb-4">
                        <label
                            for="max-files"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Jumlah File Maksimal
                        </label>
                        <input
                            id="max-files"
                            type="number"
                            v-model.number="maxFiles"
                            min="1"
                            max="10"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Jumlah maksimal file yang dapat diunggah pengguna
                        </p>
                    </div>

                    <!-- Info File Size -->
                    <div
                        class="p-3 bg-gray-50 rounded border border-gray-200 text-sm text-gray-600"
                    >
                        <div class="flex items-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-blue-500 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <span
                                >Ukuran file maksimal:
                                <strong>5 MB</strong></span
                            >
                        </div>
                    </div>
                </div>
            </template>
        </QuestionSettingsPanel>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";
import QuestionSettingsPanel from "./QuestionSettingsPanel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "update:question",
    "duplicate-question",
    "delete-question",
]);

// Default file types
const imageTypes = [
    { label: "JPEG/JPG (.jpg, .jpeg)", value: "image/jpeg" },
    { label: "PNG (.png)", value: "image/png" },
    { label: "GIF (.gif)", value: "image/gif" },
    { label: "WEBP (.webp)", value: "image/webp" },
    { label: "SVG (.svg)", value: "image/svg+xml" },
];

const documentTypes = [
    { label: "PDF (.pdf)", value: "application/pdf" },
    {
        label: "Microsoft Word (.doc, .docx)",
        value: "application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    },
    {
        label: "Microsoft Excel (.xls, .xlsx)",
        value: "application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    },
    {
        label: "Microsoft PowerPoint (.ppt, .pptx)",
        value: "application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation",
    },
    { label: "Text Files (.txt)", value: "text/plain" },
];

// Local state
const fileCategory = ref(detectFileCategory());
const selectedFileTypes = ref([]);
const maxFiles = ref(props.question.maxFiles || 1);

// Computed property to show available file types based on the selected category
const availableFileTypes = computed(() => {
    return fileCategory.value === "image" ? imageTypes : documentTypes;
});

// Helpers
function detectFileCategory() {
    if (
        !props.question.allowedTypes ||
        props.question.allowedTypes.length === 0
    ) {
        return "image"; // Default
    }

    // Check if includes */* (all files)
    if (props.question.allowedTypes.includes("*/*")) {
        return "all";
    }

    // Check if includes any image type
    const hasImage = props.question.allowedTypes.some((type) =>
        type.startsWith("image/")
    );

    // Check if includes any document type
    const hasDocument = props.question.allowedTypes.some(
        (type) => type.startsWith("application/") || type.startsWith("text/")
    );

    if (hasImage && !hasDocument) return "image";
    if (hasDocument && !hasImage) return "document";

    return "all";
}

// Initialize selected file types based on question's allowed types
function initializeSelectedFileTypes() {
    if (
        !props.question.allowedTypes ||
        props.question.allowedTypes.length === 0
    ) {
        // Set default values
        selectedFileTypes.value =
            fileCategory.value === "image"
                ? [imageTypes[0].value, imageTypes[1].value]
                : [documentTypes[0].value];
        return;
    }

    if (fileCategory.value === "all") {
        return;
    }

    const typeList =
        fileCategory.value === "image" ? imageTypes : documentTypes;

    selectedFileTypes.value = typeList
        .filter((type) => {
            // Handle compound types (like Word docs)
            const values = type.value.split(",");
            return values.some((v) => props.question.allowedTypes.includes(v));
        })
        .map((type) => type.value);
}

// Initialize the component
initializeSelectedFileTypes();

// Handle file category change
function onFileCategoryChange() {
    console.log("File category changed to:", fileCategory.value);

    // Reset selected file types when category changes
    selectedFileTypes.value = [];

    // If "All files" is selected
    if (fileCategory.value === "all") {
        console.log("All files selected, setting allowedTypes to ['*/*']");
        updateAllowedTypes();
        return;
    }

    // Select first option by default
    if (fileCategory.value === "image") {
        selectedFileTypes.value = [imageTypes[0].value];
    } else {
        selectedFileTypes.value = [documentTypes[0].value];
    }

    updateAllowedTypes();
}

// Update allowed types based on selection
function updateAllowedTypes() {
    let allowedTypes = [];

    if (fileCategory.value === "all") {
        allowedTypes = ["*/*"];
        console.log("Setting allowedTypes to ['*/*'] for 'all' file category");
    } else {
        // Handle compound types (comma-separated values)
        selectedFileTypes.value.forEach((type) => {
            if (type.includes(",")) {
                allowedTypes = [...allowedTypes, ...type.split(",")];
            } else {
                allowedTypes.push(type);
            }
        });
    }

    console.log("Final allowedTypes:", allowedTypes);

    const updatedQuestion = {
        ...props.question,
        allowedTypes: allowedTypes,
    };

    console.log(
        "Emitting updated question with allowedTypes:",
        updatedQuestion.allowedTypes
    );
    emit("update:question", updatedQuestion);
}

// Watch for changes in the question prop
watch(
    () => props.question,
    (newQuestion) => {
        if (newQuestion.maxFiles !== undefined) {
            maxFiles.value = newQuestion.maxFiles;
        }

        if (newQuestion.allowedTypes) {
            fileCategory.value = detectFileCategory();
            initializeSelectedFileTypes();
        }
    },
    { deep: true }
);

// Update base question properties
const updateQuestionBase = (updatedBaseQuestion) => {
    emit("update:question", {
        ...updatedBaseQuestion,
        maxSize: 5, // Tetapkan maxSize = 5 MB secara hardcoded
        maxFiles: maxFiles.value,
        allowedTypes: props.question.allowedTypes || [
            "image/jpeg",
            "image/png",
        ],
    });
};

// Update file upload-specific settings
const updateQuestion = () => {
    emit("update:question", {
        ...props.question,
        maxSize: 5, // Tetapkan maxSize = 5 MB secara hardcoded
        maxFiles: maxFiles.value,
    });
};
</script>
