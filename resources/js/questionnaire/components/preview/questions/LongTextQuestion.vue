<template>
    <div class="long-text-question">
        <textarea
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
            :class="{
                'border-red-300': error,
                'hover:border-indigo-300': !error,
            }"
            :placeholder="
                question.placeholder || 'Ketik jawaban Anda di sini...'
            "
            :value="modelValue"
            rows="5"
            @input="$emit('update:modelValue', $event.target.value)"
            @focus="inputFocused = true"
            @blur="inputFocused = false"
        ></textarea>
        <transition name="fade">
            <p v-if="error" class="mt-1 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
import { ref } from "vue";

defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});

defineEmits(["update:modelValue"]);

const inputFocused = ref(false);
</script>

<style scoped>
.long-text-question {
    position: relative;
}

textarea {
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    min-height: 120px;
    resize: vertical;
}

textarea:focus {
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
