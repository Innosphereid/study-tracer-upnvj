<template>
    <component
        v-if="previewComponent"
        :is="previewComponent"
        :question="question"
        @add-options="(count) => $emit('add-options', count)"
    />
    <div
        v-else
        class="mt-1 py-2 px-3 text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-md"
    >
        [Preview untuk tipe pertanyaan {{ question.type }}]
    </div>
</template>

<script setup>
import { computed } from "vue";
import { getPreviewComponent } from "./QuestionPreviewRegistry";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["add-options"]);

// Mendapatkan komponen yang sesuai dengan tipe pertanyaan
const previewComponent = computed(() => {
    return getPreviewComponent(props.question.type);
});
</script>
