<template>
    <div class="space-y-6">
        <div>
            <label
                for="thank-you-title"
                class="block text-sm font-medium text-gray-700"
                >Judul</label
            >
            <input
                type="text"
                id="thank-you-title"
                v-model="settings.title"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateSettings"
            />
        </div>

        <div>
            <label
                for="thank-you-description"
                class="block text-sm font-medium text-gray-700"
                >Deskripsi</label
            >
            <textarea
                id="thank-you-description"
                v-model="settings.description"
                rows="4"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateSettings"
            ></textarea>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from "vue";

const props = defineProps({
    thankYouScreen: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update"]);

// Create a deep copy of the thank you screen settings
const settings = ref({
    title: props.thankYouScreen.title,
    description: props.thankYouScreen.description,
});

// Watch for changes in the thank you screen prop
watch(
    () => props.thankYouScreen,
    (newThankYouScreen) => {
        settings.value = {
            title: newThankYouScreen.title,
            description: newThankYouScreen.description,
        };
    },
    { deep: true }
);

// Update settings
const updateSettings = () => {
    emit("update", settings.value);
};
</script>
