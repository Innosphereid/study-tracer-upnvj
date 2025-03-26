<template>
    <div class="mt-1">
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
                <p class="text-xs text-gray-500">Upload {{ fileTypeText }}</p>
                <p class="text-xs text-gray-500">
                    Ukuran maksimal: {{ maxFileSize }}
                </p>
                <p v-if="maxFilesText" class="text-xs text-gray-500">
                    {{ maxFilesText }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, computed } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

// Human-readable file types
const fileTypeText = computed(() => {
    if (
        !props.question.allowedTypes ||
        props.question.allowedTypes.length === 0
    ) {
        return "file (JPG, PNG)";
    }

    if (props.question.allowedTypes.includes("*/*")) {
        return "file (semua tipe)";
    }

    const typeMap = {
        "image/*": "gambar",
        "image/jpeg": "gambar (JPG, JPEG)",
        "image/png": "gambar (PNG)",
        "image/gif": "gambar (GIF)",
        "image/webp": "gambar (WEBP)",
        "image/svg+xml": "gambar (SVG)",
        "application/pdf": "dokumen PDF",
        "text/plain": "file teks",
        "application/msword": "dokumen Word",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            "dokumen Word",
        "application/vnd.ms-excel": "spreadsheet Excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
            "spreadsheet Excel",
        "application/vnd.ms-powerpoint": "presentasi PowerPoint",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation":
            "presentasi PowerPoint",
    };

    // Check if there are only image types
    const onlyImages = props.question.allowedTypes.every(
        (type) => type.startsWith("image/") || type === "image/*"
    );

    if (onlyImages) {
        if (props.question.allowedTypes.includes("image/*")) {
            return "gambar (semua format)";
        }

        // Get specific image types
        const imageFormats = props.question.allowedTypes
            .map((type) => {
                const format = typeMap[type];
                if (format) return format.replace("gambar ", "");
                return type;
            })
            .filter((format) => format !== "");

        if (imageFormats.length === 0) return "gambar";
        if (imageFormats.length === 1) return `gambar ${imageFormats[0]}`;

        // Format multiple image types nicely
        return `gambar (${imageFormats.join(", ")})`;
    }

    // Check if there are only document types
    const onlyDocs = props.question.allowedTypes.every(
        (type) => type.startsWith("application/") || type.startsWith("text/")
    );

    if (onlyDocs) {
        const docTypes = props.question.allowedTypes
            .map((type) => typeMap[type] || type)
            .filter((type) => type !== "");

        if (docTypes.length === 0) return "dokumen";
        if (docTypes.length === 1) return docTypes[0];

        return `dokumen (${docTypes.join(", ")})`;
    }

    // Mixed types or other
    const allowedExtensions = [];
    props.question.allowedTypes.forEach((type) => {
        // Extract extensions from MIME types
        if (type === "image/jpeg") allowedExtensions.push("JPG/JPEG");
        else if (type === "image/png") allowedExtensions.push("PNG");
        else if (type === "image/gif") allowedExtensions.push("GIF");
        else if (type === "image/webp") allowedExtensions.push("WEBP");
        else if (type === "image/svg+xml") allowedExtensions.push("SVG");
        else if (type === "application/pdf") allowedExtensions.push("PDF");
        else if (type === "text/plain") allowedExtensions.push("TXT");
        else if (
            type === "application/msword" ||
            type ===
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        ) {
            if (!allowedExtensions.includes("DOC/DOCX"))
                allowedExtensions.push("DOC/DOCX");
        } else if (
            type === "application/vnd.ms-excel" ||
            type ===
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        ) {
            if (!allowedExtensions.includes("XLS/XLSX"))
                allowedExtensions.push("XLS/XLSX");
        } else if (
            type === "application/vnd.ms-powerpoint" ||
            type ===
                "application/vnd.openxmlformats-officedocument.presentationml.presentation"
        ) {
            if (!allowedExtensions.includes("PPT/PPTX"))
                allowedExtensions.push("PPT/PPTX");
        }
    });

    if (allowedExtensions.length === 0) return "file";

    return `file (${allowedExtensions.join(", ")})`;
});

// Maximum file size
const maxFileSize = computed(() => {
    const maxSize = props.question.maxSize || 5;
    return `${maxSize} MB`;
});

// Multiple files text
const maxFilesText = computed(() => {
    const maxFiles = props.question.maxFiles || 1;
    if (maxFiles > 1) {
        return `Maksimal ${maxFiles} file`;
    }
    return "";
});
</script>
