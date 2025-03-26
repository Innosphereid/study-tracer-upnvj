<template>
    <div class="question-component">
        <fieldset>
            <legend class="text-sm font-medium text-gray-700">
                {{ question.text }}
                <span v-if="question.required" class="text-red-500">*</span>
            </legend>

            <p v-if="question.helpText" class="mt-1 text-sm text-gray-500">
                {{ question.helpText }}
            </p>

            <div class="mt-4">
                <!-- Scale labels -->
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">
                        {{ minLabel }}
                    </span>
                    <span class="text-sm text-gray-500">
                        {{ maxLabel }}
                    </span>
                </div>

                <!-- Scale options -->
                <div class="flex items-center justify-between w-full">
                    <div class="flex flex-1 justify-between overflow-x-auto">
                        <div
                            v-for="option in question.scale"
                            :key="option.value"
                            class="flex flex-col items-center mx-1"
                        >
                            <input
                                type="radio"
                                :name="`likert-${question.id}`"
                                :value="option.value"
                                :id="`likert-${question.id}-${option.value}`"
                                v-model="selectedValue"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                @change="updateValue"
                                :disabled="isBuilder"
                            />
                            <label
                                :for="`likert-${question.id}-${option.value}`"
                                class="text-xs text-gray-600 mt-1 text-center whitespace-nowrap"
                                style="min-width: 20px"
                            >
                                {{ option.value }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <div v-if="error" class="mt-2 text-sm text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Number,
        default: 0,
    },
    error: {
        type: String,
        default: "",
    },
    isBuilder: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Internal state
const selectedValue = ref(props.modelValue || 0);

// Computed properties for labels
const minLabel = computed(() => {
    if (props.question.scale && props.question.scale.length > 0) {
        return props.question.scale[0].label;
    }
    return "Sangat Tidak Setuju";
});

const maxLabel = computed(() => {
    if (props.question.scale && props.question.scale.length > 0) {
        return props.question.scale[props.question.scale.length - 1].label;
    }
    return "Sangat Setuju";
});

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        selectedValue.value = newVal || 0;
    }
);

const updateValue = () => {
    emit("update:modelValue", selectedValue.value);
    validate();
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !selectedValue.value) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
/* Responsive styling */
@media (max-width: 640px) {
    .flex-1 {
        overflow-x: auto;
    }
}
</style>
