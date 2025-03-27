<template>
    <div class="dropdown-question">
        <div class="relative">
            <select
                :id="`dropdown-${question.id}`"
                class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                :class="{ 'border-red-300': error }"
                :value="modelValue?.value || ''"
                @change="handleChange"
            >
                <option value="" disabled selected>Pilih jawaban...</option>
                <option
                    v-for="(option, index) in question.options"
                    :key="option.id || index"
                    :value="option.value || option.text"
                >
                    {{ option.text }}
                </option>
                <option v-if="question.hasOtherOption" value="other">
                    Lainnya
                </option>
            </select>
            <div
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
            >
                <svg
                    class="h-4 w-4 fill-current"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                >
                    <path
                        d="M5.516 7.548c.436-.446 1.043-.481 1.576 0L10 10.405l2.908-2.857c.533-.481 1.141-.446 1.576 0 .436.445.408 1.197 0 1.642l-3.996 3.93c-.533.481-1.142.446-1.576 0L5.516 9.19c-.408-.445-.436-1.197 0-1.642z"
                    />
                </svg>
            </div>
        </div>

        <!-- "Other" input form -->
        <div v-if="showOtherInput" class="mt-2">
            <input
                type="text"
                v-model="otherText"
                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                placeholder="Masukkan jawaban lainnya..."
                @input="updateOtherText"
            />
        </div>

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Object,
        default: () => ({ value: "", otherText: "" }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// Local state
const otherText = ref(props.modelValue?.otherText || "");

// Computed property to check if we should show the "other" input
const showOtherInput = computed(() => {
    return props.modelValue?.value === "other";
});

// Method to handle dropdown change
const handleChange = (event) => {
    const value = event.target.value;
    emit("update:modelValue", {
        value,
        otherText: value === "other" ? otherText.value : "",
    });
};

// Method to update the "other" text
const updateOtherText = () => {
    emit("update:modelValue", {
        value: "other",
        otherText: otherText.value,
    });
};

// Watch for external changes to modelValue
watch(
    () => props.modelValue?.otherText,
    (newValue) => {
        if (newValue !== undefined) {
            otherText.value = newValue;
        }
    }
);

// Add subtle animation on focus
const selectElement = ref(null);
</script>

<style scoped>
.dropdown-question select {
    appearance: none;
    transition: all 0.2s ease;
}

.dropdown-question select:focus {
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Enhance the select dropdown with custom styling */
select option {
    padding: 8px;
    font-size: 0.95rem;
}

select option:checked {
    background-color: #4f46e5;
    color: white;
}
</style>
