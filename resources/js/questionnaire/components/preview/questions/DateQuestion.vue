<template>
    <div class="date-question">
        <input
            type="date"
            class="block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200"
            :class="error ? 'border-red-300' : 'border-gray-300'"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :min="question.min || ''"
            :max="question.max || ''"
        />

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
const props = defineProps({
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
</script>

<style scoped>
.date-question input {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.date-question input::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s ease-in-out;
}

.date-question input::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
