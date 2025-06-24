<template>
    <div class="space-y-6">
        <!-- Common Question Settings -->
        <div>
            <label
                for="question-text"
                class="block text-sm font-medium text-gray-700"
                >Teks Pertanyaan</label
            >
            <input
                type="text"
                id="question-text"
                v-model="questionData.text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateQuestion"
            />
        </div>

        <div>
            <label
                for="help-text"
                class="block text-sm font-medium text-gray-700"
                >Teks Bantuan</label
            >
            <textarea
                id="help-text"
                v-model="questionData.helpText"
                rows="2"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateQuestion"
            ></textarea>
        </div>

        <div class="flex items-center">
            <input
                type="checkbox"
                id="required"
                v-model="questionData.required"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                @change="updateQuestion"
            />
            <label for="required" class="ml-2 block text-sm text-gray-700"
                >Wajib Diisi</label
            >
        </div>

        <!-- Slot for type-specific settings -->
        <div class="border-t border-gray-200 pt-4">
            <!-- Debug untuk memeriksa apakah slot ini dipanggil -->
            <p class="mb-2 text-xs text-gray-500">
                Pengaturan khusus {{ props.question.type }}
            </p>
            <slot name="type-specific-settings">
                <!-- Konten fallback jika slot tidak diisi oleh komponen anak -->
                <div
                    class="py-2 px-3 bg-gray-50 rounded-md text-sm text-gray-500"
                >
                    Tidak ada pengaturan khusus untuk tipe pertanyaan ini
                </div>
            </slot>
        </div>

        <!-- Common action buttons -->
        <div class="pt-4 flex justify-between space-x-3">
            <button
                type="button"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                @click="$emit('duplicate-question')"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="-ml-1 mr-2 h-5 w-5 text-gray-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                </svg>
                Duplikasi Pertanyaan
            </button>

            <button
                type="button"
                class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                @click="$emit('delete-question')"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="-ml-1 mr-2 h-5 w-5"
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
                Hapus Pertanyaan
            </button>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, watch, ref } from "vue";

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

// Create a deep copy of the question to avoid direct mutation
const questionData = ref({
    text: props.question.text || "",
    helpText: props.question.helpText || "",
    required: props.question.required || false,
});

// Watch for changes in the question prop
watch(
    () => props.question,
    (newQuestion) => {
        questionData.value = {
            text: newQuestion.text || "",
            helpText: newQuestion.helpText || "",
            required: newQuestion.required || false,
        };
    },
    { deep: true }
);

// Method to emit changes to parent
const updateQuestion = () => {
    emit("update:question", {
        ...props.question,
        text: questionData.value.text,
        helpText: questionData.value.helpText,
        required: questionData.value.required,
        // Update settings object to make sure it's saved to the database
        settings: {
            ...(props.question.settings || {}),
            text: questionData.value.text,
            helpText: questionData.value.helpText,
            required: questionData.value.required,
        },
        // Also update backend field names for proper database storage
        title: questionData.value.text,
        description: questionData.value.helpText,
        is_required: questionData.value.required,
    });
};
</script>
