<template>
    <div class="space-y-6">
        <div>
            <label
                for="section-title"
                class="block text-sm font-medium text-gray-700"
                >Judul Seksi</label
            >
            <input
                type="text"
                id="section-title"
                v-model="settings.title"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateSettings"
            />
        </div>

        <div>
            <label
                for="section-description"
                class="block text-sm font-medium text-gray-700"
                >Deskripsi</label
            >
            <textarea
                id="section-description"
                v-model="settings.description"
                rows="3"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateSettings"
            ></textarea>
        </div>

        <div>
            <label
                for="questions-per-page"
                class="block text-sm font-medium text-gray-700"
                >Jumlah Pertanyaan Per Halaman</label
            >
            <select
                id="questions-per-page"
                v-model="settings.questionsPerPage"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                @change="updateSettings"
            >
                <option value="all">Semua (Satu Halaman)</option>
                <option :value="1">1 Pertanyaan</option>
                <option :value="2">2 Pertanyaan</option>
                <option :value="3">3 Pertanyaan</option>
                <option :value="5">5 Pertanyaan</option>
                <option :value="10">10 Pertanyaan</option>
            </select>
        </div>

        <div class="flex space-x-3 pt-4">
            <button
                type="button"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                @click="$emit('duplicate')"
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
                Duplikasi Seksi
            </button>

            <button
                type="button"
                class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                @click="$emit('delete')"
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
                Hapus Seksi
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from "vue";

const props = defineProps({
    section: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update", "duplicate", "delete"]);

// Create local state for section settings
const settings = ref({
    title: props.section.title,
    description: props.section.description,
    questionsPerPage: props.section.questionsPerPage || "all",
});

// Watch for changes in the section prop
watch(
    () => props.section,
    (newSection) => {
        if (!newSection) return;

        settings.value = {
            title: newSection.title,
            description: newSection.description,
            questionsPerPage: newSection.questionsPerPage || "all",
        };
    },
    { deep: true }
);

// Update settings
const updateSettings = () => {
    emit("update", settings.value);
};
</script>
