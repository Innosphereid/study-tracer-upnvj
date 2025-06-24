<template>
    <div class="email-question">
        <input
            type="email"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
            :class="{
                'border-red-300': error,
                'hover:border-indigo-300': !error,
            }"
            :placeholder="question.placeholder || 'contoh@email.com'"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            @focus="inputFocused = true"
            @blur="inputFocused = false"
        />
        <transition name="fade">
            <p v-if="error" class="mt-1 text-sm text-red-600">
                {{ error }}
            </p>
            <p
                v-else-if="!isValidEmail && modelValue"
                class="mt-1 text-sm text-orange-600"
            >
                Format email tidak valid
            </p>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

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

const inputFocused = ref(false);

// Email validation
const isValidEmail = computed(() => {
    if (!props.modelValue) return true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(props.modelValue);
});
</script>

<style scoped>
.email-question {
    position: relative;
}

input {
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
}

input:focus {
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
}

/* Animasi validasi */
input:valid {
    border-color: #10b981;
}

input:invalid {
    border-color: #ef4444;
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
