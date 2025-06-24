<template>
    <div
        class="question-container bg-white rounded-lg p-6 shadow-sm mb-6 transition-all duration-200 hover:shadow-md"
    >
        <slot></slot>
    </div>
</template>

<script setup>
// This component serves as a container for question components
// Using slots to accept any question component as its child
</script>

<style scoped>
.question-container {
    border: 1px solid rgba(229, 231, 235, 0.5);
}
</style>
