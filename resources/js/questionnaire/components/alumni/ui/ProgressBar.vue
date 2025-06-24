<template>
    <div class="progress-container mb-8">
        <div
            class="flex items-center justify-between text-sm text-gray-500 mb-2"
        >
            <span>{{ Math.round(progress) }}% Selesai</span>
            <span v-if="showSectionNumbers">
                Halaman {{ currentSection }} dari {{ totalSections }}
            </span>
        </div>
        <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
            <div
                class="h-full bg-indigo-600 rounded-full transition-all duration-300 ease-out"
                :style="{ width: `${progress}%` }"
            ></div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    progress: {
        type: Number,
        required: true,
        validator: (value) => value >= 0 && value <= 100,
    },
    currentSection: {
        type: Number,
        default: 1,
    },
    totalSections: {
        type: Number,
        default: 1,
    },
    showSectionNumbers: {
        type: Boolean,
        default: true,
    },
});
</script>

<style scoped>
.progress-container {
    max-width: 100%;
}
</style>
