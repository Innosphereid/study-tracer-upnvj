<template>
    <div class="space-y-6">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700"
                >Judul Kuesioner</label
            >
            <input
                type="text"
                id="title"
                v-model="settings.title"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateSettings"
            />
        </div>

        <div>
            <label
                for="description"
                class="block text-sm font-medium text-gray-700"
                >Deskripsi</label
            >
            <textarea
                id="description"
                v-model="settings.description"
                rows="3"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateSettings"
            ></textarea>
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700"
                >Slug URL</label
            >
            <div class="mt-1 flex rounded-md shadow-sm">
                <span
                    class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm"
                >
                    /kuesioner/
                </span>
                <input
                    type="text"
                    id="slug"
                    v-model="settings.slug"
                    class="flex-1 block w-full rounded-none rounded-r-md border border-gray-300 py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    @change="updateSettings"
                />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label
                    for="start-date"
                    class="block text-sm font-medium text-gray-700"
                    >Tanggal Mulai</label
                >
                <input
                    type="date"
                    id="start-date"
                    v-model="settings.startDate"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    @change="updateSettings"
                />
            </div>

            <div>
                <label
                    for="end-date"
                    class="block text-sm font-medium text-gray-700"
                    >Tanggal Selesai</label
                >
                <input
                    type="date"
                    id="end-date"
                    v-model="settings.endDate"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    @change="updateSettings"
                />
            </div>
        </div>

        <div class="space-y-3">
            <h3 class="text-sm font-medium text-gray-700">Opsi Tampilan</h3>

            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="show-progress-bar"
                    v-model="settings.showProgressBar"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    @change="updateSettings"
                />
                <label
                    for="show-progress-bar"
                    class="ml-2 block text-sm text-gray-700"
                >
                    Tampilkan Progress Bar
                </label>
            </div>

            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="show-page-numbers"
                    v-model="settings.showPageNumbers"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    @change="updateSettings"
                />
                <label
                    for="show-page-numbers"
                    class="ml-2 block text-sm text-gray-700"
                >
                    Tampilkan Nomor Halaman
                </label>
            </div>

            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="requires-login"
                    v-model="settings.requiresLogin"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    @change="updateSettings"
                />
                <label
                    for="requires-login"
                    class="ml-2 block text-sm text-gray-700"
                >
                    Wajib Login Alumni
                </label>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from "vue";

const props = defineProps({
    questionnaire: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update:questionnaire"]);

// Create a deep copy of the settings to avoid direct mutation
const settings = ref({
    title: props.questionnaire.title,
    description: props.questionnaire.description,
    slug: props.questionnaire.slug,
    startDate: props.questionnaire.startDate,
    endDate: props.questionnaire.endDate,
    showProgressBar: props.questionnaire.showProgressBar,
    showPageNumbers: props.questionnaire.showPageNumbers,
    requiresLogin: props.questionnaire.requiresLogin,
});

// Watch for changes in the questionnaire prop
watch(
    () => props.questionnaire,
    (newQuestionnaire) => {
        settings.value = {
            title: newQuestionnaire.title,
            description: newQuestionnaire.description,
            slug: newQuestionnaire.slug,
            startDate: newQuestionnaire.startDate,
            endDate: newQuestionnaire.endDate,
            showProgressBar: newQuestionnaire.showProgressBar,
            showPageNumbers: newQuestionnaire.showPageNumbers,
            requiresLogin: newQuestionnaire.requiresLogin,
        };
    },
    { deep: true }
);

// Update settings
const updateSettings = () => {
    emit("update:questionnaire", settings.value);
};
</script>
