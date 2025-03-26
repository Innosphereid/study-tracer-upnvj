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
                    v-for="option in question.options"
                    :key="option.id"
                    class="flex items-start"
                >
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-${option.id}`"
                            :name="`question_${question.id}[]`"
                            type="checkbox"
                            :value="option.value"
                            v-model="selectedValues"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            @change="updateValue"
                            :disabled="isBuilder"
                            :aria-describedby="`option-${question.id}-${option.id}-description`"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label
                            :for="`option-${question.id}-${option.id}`"
                            class="font-medium text-gray-700"
                        >
                            {{ option.text }}
                        </label>
                        <p
                            v-if="option.description"
                            :id="`option-${question.id}-${option.id}-description`"
                            class="text-gray-500"
                        >
                            {{ option.description }}
                        </p>
                    </div>
                </div>

                <!-- "Other" option -->
                <div v-if="question.allowOther" class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            :id="`option-${question.id}-other`"
                            :name="`question_${question.id}[]`"
                            type="checkbox"
                            value="other"
                            v-model="otherSelected"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            @change="updateOtherSelected"
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
                            :disabled="!otherSelected || isBuilder"
                            @input="updateValue"
                            placeholder="Sebutkan..."
                        />
                    </div>
                </div>
            </div>

            <!-- Min/Max help text -->
            <p
                v-if="question.minSelected > 0 || question.maxSelected > 0"
                class="mt-2 text-xs text-gray-500"
            >
                <template
                    v-if="question.minSelected > 0 && question.maxSelected > 0"
                >
                    Pilih {{ question.minSelected }} hingga
                    {{ question.maxSelected }} opsi.
                </template>
                <template v-else-if="question.minSelected > 0">
                    Pilih minimal {{ question.minSelected }} opsi.
                </template>
                <template v-else-if="question.maxSelected > 0">
                    Pilih maksimal {{ question.maxSelected }} opsi.
                </template>
            </p>
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
        default: () => ({ values: [], otherText: "" }),
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
const selectedValues = ref(props.modelValue.values || []);
const otherText = ref(props.modelValue.otherText || "");
const otherSelected = ref(selectedValues.value.includes("other"));

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        selectedValues.value = newVal.values || [];
        otherText.value = newVal.otherText || "";
        otherSelected.value = selectedValues.value.includes("other");
    },
    { deep: true }
);

// Update "other" selection when selectedValues changes
watch(
    () => selectedValues.value,
    (newVal) => {
        otherSelected.value = newVal.includes("other");
    }
);

const updateValue = () => {
    emit("update:modelValue", {
        values: selectedValues.value,
        otherText: otherSelected.value ? otherText.value : "",
    });

    validate();
};

const updateOtherSelected = () => {
    // Sync selectedValues with otherSelected
    if (otherSelected.value && !selectedValues.value.includes("other")) {
        selectedValues.value.push("other");
    } else if (!otherSelected.value && selectedValues.value.includes("other")) {
        selectedValues.value = selectedValues.value.filter(
            (val) => val !== "other"
        );
    }

    updateValue();
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && selectedValues.value.length === 0) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    // Min selected validation
    if (
        props.question.minSelected > 0 &&
        selectedValues.value.length < props.question.minSelected
    ) {
        isValid = false;
        errorMessage = `Pilih minimal ${props.question.minSelected} opsi.`;
    }

    // Max selected validation
    if (
        props.question.maxSelected > 0 &&
        selectedValues.value.length > props.question.maxSelected
    ) {
        isValid = false;
        errorMessage = `Pilih maksimal ${props.question.maxSelected} opsi.`;
    }

    // Other validation
    if (
        otherSelected.value &&
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
