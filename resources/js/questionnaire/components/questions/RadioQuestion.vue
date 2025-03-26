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

            <div class="mt-4 space-y-3">
                <div
                    v-for="option in sortedOptions"
                    :key="option.id"
                    class="flex items-start"
                >
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-${option.id}`"
                            :name="`question_${question.id}`"
                            type="radio"
                            :value="option.value"
                            v-model="internalValue"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                            @change="updateValue"
                            :disabled="isBuilder"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label
                            :for="`option-${question.id}-${option.id}`"
                            class="font-medium text-gray-700"
                        >
                            {{ option.text }}
                        </label>
                    </div>
                </div>

                <!-- "None" option -->
                <div v-if="question.allowNone" class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-none`"
                            :name="`question_${question.id}`"
                            type="radio"
                            value="none"
                            v-model="internalValue"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                            @change="updateValue"
                            :disabled="isBuilder"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label
                            :for="`option-${question.id}-none`"
                            class="font-medium text-gray-700"
                        >
                            Tidak Ada
                        </label>
                    </div>
                </div>

                <!-- "Other" option -->
                <div v-if="question.allowOther" class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-other`"
                            :name="`question_${question.id}`"
                            type="radio"
                            value="other"
                            v-model="internalValue"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                            @change="updateValue"
                            :disabled="isBuilder"
                        />
                    </div>
                    <div class="ml-3 text-sm flex items-center">
                        <label
                            :for="`option-${question.id}-other`"
                            class="font-medium text-gray-700 mr-2"
                        >
                            Lainnya:
                        </label>
                        <input
                            type="text"
                            v-model="otherText"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block sm:text-sm border-gray-300 rounded-md"
                            :disabled="internalValue !== 'other' || isBuilder"
                            @input="updateOtherValue"
                            placeholder="Sebutkan..."
                        />
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
        type: Object,
        default: () => ({ value: "", otherText: "" }),
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
const internalValue = ref(props.modelValue.value || "");
const otherText = ref(props.modelValue.otherText || "");

// Computed property to sort options based on optionsOrder
const sortedOptions = computed(() => {
    if (!props.question.options || !props.question.options.length) {
        return [];
    }

    // Create a copy to avoid mutating original data
    const options = [...props.question.options];

    // Apply sorting based on optionsOrder
    if (props.question.optionsOrder === "asc") {
        return options.sort((a, b) => a.text.localeCompare(b.text));
    } else if (props.question.optionsOrder === "desc") {
        return options.sort((a, b) => b.text.localeCompare(a.text));
    }

    // Default: return in original order
    return options;
});

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        internalValue.value = newVal.value || "";
        otherText.value = newVal.otherText || "";
    },
    { deep: true }
);

const updateValue = () => {
    emit("update:modelValue", {
        value: internalValue.value,
        otherText: internalValue.value === "other" ? otherText.value : "",
    });

    validate();
};

const updateOtherValue = () => {
    if (internalValue.value === "other") {
        emit("update:modelValue", {
            value: "other",
            otherText: otherText.value,
        });

        validate();
    }
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !internalValue.value) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    // "Other" validation
    if (
        internalValue.value === "other" &&
        !otherText.value.trim() &&
        props.question.required
    ) {
        isValid = false;
        errorMessage = 'Harap isi opsi "Lainnya".';
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>
