<template>
    <div class="section-container bg-white rounded-lg p-8 shadow mb-10">
        <div v-if="title || description" class="section-header mb-8">
            <h2 v-if="title" class="text-2xl font-semibold text-gray-800 mb-2">
                {{ title }}
            </h2>
            <p v-if="description" class="text-gray-600">{{ description }}</p>
        </div>

        <div class="section-content space-y-6">
            <slot></slot>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: "",
    },
    description: {
        type: String,
        default: "",
    },
});
</script>

<style scoped>
.section-container {
    border: 1px solid rgba(229, 231, 235, 0.8);
}
</style>
