<template>
    <div class="checkbox-question">
        <div class="space-y-2">
            <div
                v-for="(option, index) in question.options"
                :key="option.id || index"
                class="relative flex items-start transition-transform hover:translate-x-1"
            >
                <div class="flex items-center h-6">
                    <input
                        type="checkbox"
                        :id="`option-${option.id || index}`"
                        :value="option.value || option.text"
                        :checked="isOptionSelected(option)"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer transition-all duration-200"
                        @change="toggleOption(option)"
                    />
                </div>
                <div class="ml-3 text-sm">
                    <label
                        :for="`option-${option.id || index}`"
                        class="font-medium text-gray-700 cursor-pointer hover:text-indigo-600 transition-colors duration-200"
                    >
                        {{ option.text }}
                    </label>
                </div>
            </div>

            <!-- "Other" option if enabled -->
            <div
                v-if="question.hasOtherOption"
                class="mt-3 relative flex items-start transition-transform hover:translate-x-1"
            >
                <div class="flex items-center h-6">
                    <input
                        type="checkbox"
                        :id="`option-other-${question.id}`"
                        value="other"
                        :checked="otherSelected"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer transition-all duration-200"
                        @change="toggleOtherOption"
                    />
                </div>
                <div class="ml-3 text-sm flex-1">
                    <label
                        :for="`option-other-${question.id}`"
                        class="font-medium text-gray-700 cursor-pointer hover:text-indigo-600 transition-colors duration-200"
                    >
                        Lainnya
                    </label>
                    <input
                        v-if="otherSelected"
                        v-model="otherText"
                        type="text"
                        class="mt-2 w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                        placeholder="Masukkan pilihan lainnya..."
                        @input="updateOtherText"
                    />
                </div>
            </div>
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
        default: () => ({ values: [], otherText: "" }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// Local state for "other" option
const otherText = ref(props.modelValue?.otherText || "");
const selectedValues = ref(props.modelValue?.values || []);

// Computed property to check if "other" option is selected
const otherSelected = computed(() => {
    return props.modelValue?.values?.includes("other");
});

// Helper to check if an option is selected
const isOptionSelected = (option) => {
    return props.modelValue?.values?.includes(option.value || option.text);
};

// Method to toggle option selection
const toggleOption = (option) => {
    const value = option.value || option.text;
    const values = [...(props.modelValue?.values || [])];

    const index = values.indexOf(value);
    if (index > -1) {
        values.splice(index, 1);
    } else {
        values.push(value);
    }

    emit("update:modelValue", {
        values,
        otherText: props.modelValue?.otherText || "",
    });
};

// Method to toggle "other" option
const toggleOtherOption = () => {
    const values = [...(props.modelValue?.values || [])];

    const index = values.indexOf("other");
    if (index > -1) {
        values.splice(index, 1);
        emit("update:modelValue", {
            values,
            otherText: "",
        });
    } else {
        values.push("other");
        emit("update:modelValue", {
            values,
            otherText: otherText.value,
        });
    }
};

// Method to update the "other" text
const updateOtherText = () => {
    emit("update:modelValue", {
        values: props.modelValue?.values || [],
        otherText: otherText.value,
    });
};

// Watch for external changes to model value
watch(
    () => props.modelValue?.otherText,
    (newValue) => {
        if (newValue !== undefined) {
            otherText.value = newValue;
        }
    }
);

watch(
    () => props.modelValue?.values,
    (newValues) => {
        if (newValues) {
            selectedValues.value = newValues;
        }
    }
);
</script>

<style scoped>
.checkbox-question input[type="checkbox"] {
    position: relative;
    transition: all 0.2s ease;
}

.checkbox-question input[type="checkbox"]:checked {
    transform: scale(1.2);
}

.checkbox-question input[type="checkbox"]:checked + div label {
    color: #4f46e5;
    font-weight: 600;
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
