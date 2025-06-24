<template>
    <div class="number-question">
        <input
            type="number"
            class="block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200"
            :class="error ? 'border-red-300' : 'border-gray-300'"
            :value="modelValue"
            @input="updateValue($event.target.value)"
            :placeholder="question.placeholder || 'Masukkan angka'"
            :min="question.min"
            :max="question.max"
            :step="question.step || 1"
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
        type: [String, Number],
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const updateValue = (value) => {
    // Convert empty string to empty value
    if (value === "") {
        emit("update:modelValue", "");
        return;
    }

    // Convert to number and validate
    const numberValue = parseFloat(value);

    // Check if within min/max bounds if they exist
    if (props.question.min !== undefined && numberValue < props.question.min) {
        emit("update:modelValue", props.question.min.toString());
        return;
    }

    if (props.question.max !== undefined && numberValue > props.question.max) {
        emit("update:modelValue", props.question.max.toString());
        return;
    }

    emit("update:modelValue", value);
};
</script>

<style scoped>
.number-question input::-webkit-outer-spin-button,
.number-question input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.number-question input[type="number"] {
    -moz-appearance: textfield;
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
