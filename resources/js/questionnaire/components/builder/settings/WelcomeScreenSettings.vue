<template>
    <div class="space-y-6">
        <div>
            <label
                for="welcome-title"
                class="block text-sm font-medium text-gray-700"
                >Judul</label
            >
            <input
                type="text"
                id="welcome-title"
                v-model="settings.title"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="updateSettings"
            />
        </div>

        <div>
            <label
                for="welcome-description"
                class="block text-sm font-medium text-gray-700"
                >Deskripsi</label
            >
            <textarea
                id="welcome-description"
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
    welcomeScreen: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update"]);

// Create a deep copy of the welcome screen settings
const settings = ref({
    title: props.welcomeScreen.title,
    description: props.welcomeScreen.description,
});

// Watch for changes in the welcome screen prop
watch(
    () => props.welcomeScreen,
    (newWelcomeScreen) => {
        settings.value = {
            title: newWelcomeScreen.title,
            description: newWelcomeScreen.description,
        };
    },
    { deep: true }
);

// Update settings
const updateSettings = () => {
    emit("update", settings.value);
};
</script>
