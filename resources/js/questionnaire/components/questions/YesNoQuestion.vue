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
                <div class="flex items-center space-x-4">
                    <!-- Yes option -->
                    <div class="flex items-center">
                        <input
                            :id="`option-${question.id}-yes`"
                            :name="`question_${question.id}`"
                            type="radio"
                            value="yes"
                            v-model="internalValue"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                            @change="updateValue"
                            :disabled="isBuilder"
                        />
                        <label
                            :for="`option-${question.id}-yes`"
                            class="ml-3 block text-sm font-medium text-gray-700"
                        >
                            {{ question.yesLabel || "Ya" }}
                        </label>
                    </div>

                    <!-- No option -->
                    <div class="flex items-center">
                        <input
                            :id="`option-${question.id}-no`"
                            :name="`question_${question.id}`"
                            type="radio"
                            value="no"
                            v-model="internalValue"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                            @change="updateValue"
                            :disabled="isBuilder"
                        />
                        <label
                            :for="`option-${question.id}-no`"
                            class="ml-3 block text-sm font-medium text-gray-700"
                        >
                            {{ question.noLabel || "Tidak" }}
                        </label>
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
import { ref, defineProps, defineEmits, watch } from "vue";

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
    isBuilder: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Internal state
const internalValue = ref(props.modelValue || "");

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        internalValue.value = newVal || "";
    }
);

const updateValue = () => {
    emit("update:modelValue", internalValue.value);
    validate();
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !internalValue.value) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>
