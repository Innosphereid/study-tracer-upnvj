<template>
    <div class="phone-question">
        <input
            type="tel"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
            :class="{
                'border-red-300': error,
                'hover:border-indigo-300': !error,
            }"
            :placeholder="question.placeholder || '+62 8xx-xxxx-xxxx'"
            :value="modelValue"
            @input="handleInput"
            @focus="inputFocused = true"
            @blur="inputFocused = false"
        />
        <transition name="fade">
            <p v-if="error" class="mt-1 text-sm text-red-600">
                {{ error }}
            </p>
            <p
                v-else-if="!isValidPhone && modelValue"
                class="mt-1 text-sm text-orange-600"
            >
                Format nomor telepon tidak valid
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

const emit = defineEmits(["update:modelValue"]);

const inputFocused = ref(false);

// Phone validation (basic format)
const isValidPhone = computed(() => {
    if (!props.modelValue) return true;
    // Basic phone validation - can be adjusted for Indonesia format
    // This accepts: +62812345678, 081234567890, +62 812-3456-7890, etc.
    const phoneRegex = /^(\+?62|0)[0-9]{9,13}$/;
    return phoneRegex.test(props.modelValue.replace(/[\s-]/g, ""));
});

// Handle input - optional formatting
const handleInput = (event) => {
    const value = event.target.value;

    // Format the phone number (optional)
    // const formattedValue = formatPhoneNumber(value);

    emit("update:modelValue", value);
};

// Optional phone formatting function
const formatPhoneNumber = (value) => {
    // Remove all non-digit characters
    const digits = value.replace(/\D/g, "");

    // Format based on length and country code
    if (digits.startsWith("62") || digits.startsWith("0")) {
        // Indonesia format logic can be implemented here
        // This is just a placeholder for actual formatting logic
        return digits;
    }

    return value;
};
</script>

<style scoped>
.phone-question {
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
