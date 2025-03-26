<template>
    <div class="question-component">
        <label :for="inputId" class="block text-sm font-medium text-gray-700">
            {{ question.text }}
            <span v-if="question.required" class="text-red-500">*</span>
        </label>

        <p v-if="question.helpText" class="mt-1 text-sm text-gray-500">
            {{ question.helpText }}
        </p>

        <div class="mt-2">
            <select
                :id="inputId"
                :name="inputName"
                v-model="internalValue"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                :required="question.required"
                @change="updateValue"
                :disabled="isBuilder"
            >
                <option value="" disabled selected>-- Pilih Opsi --</option>
                <option
                    v-for="option in question.options"
                    :key="option.id"
                    :value="option.value"
                >
                    {{ option.text }}
                </option>
                <option v-if="question.allowOther" value="other">
                    Lainnya...
                </option>
            </select>

            <!-- Text input for "Other" option -->
            <div
                v-if="question.allowOther && internalValue === 'other'"
                class="mt-2"
            >
                <input
                    type="text"
                    v-model="otherText"
                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    placeholder="Sebutkan..."
                    @input="updateOtherValue"
                    :disabled="isBuilder"
                />
            </div>
        </div>

        <div v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";
import { v4 as uuidv4 } from "uuid";

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

// Unique ID for select field
const inputId = computed(() => `q-${props.question.id || uuidv4()}`);
const inputName = computed(() => `question_${props.question.id || ""}`);

// Internal state
const internalValue = ref(props.modelValue.value || "");
const otherText = ref(props.modelValue.otherText || "");

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) {
            internalValue.value = newVal.value || "";
            otherText.value = newVal.otherText || "";
        }
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
