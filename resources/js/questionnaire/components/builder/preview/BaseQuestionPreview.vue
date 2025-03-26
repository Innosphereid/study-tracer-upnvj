<template>
    <div class="question-preview">
        <slot></slot>
    </div>
</template>

<script setup>
defineProps({
    question: {
        type: Object,
        required: true,
    },
});
</script>
