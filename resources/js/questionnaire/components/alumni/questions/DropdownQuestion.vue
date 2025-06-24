<template>
    <div class="dropdown-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <div class="relative">
                    <select
                        class="block w-full px-4 py-3 pr-10 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 appearance-none bg-white"
                        :class="{
                            'border-red-300': error,
                            'hover:border-indigo-300': !error,
                        }"
                        :value="localValue.value"
                        @change="updateValue($event.target.value)"
                    >
                        <option value="" disabled selected>
                            {{ placeholder }}
                        </option>
                        <option
                            v-for="(option, index) in parsedOptions"
                            :key="index"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                        <option v-if="hasOtherOption" value="other">
                            {{ otherOptionLabel }}
                        </option>
                    </select>
                    <div
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                    >
                        <svg
                            class="fill-current h-4 w-4"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
                            />
                        </svg>
                    </div>
                </div>

                <!-- "Other" option input -->
                <div v-if="hasOtherOption && isOtherSelected" class="mt-3">
                    <input
                        type="text"
                        v-model="localValue.otherText"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                        :placeholder="otherPlaceholder || 'Ketik di sini...'"
                        @input="updateOtherText"
                        @blur="validate"
                    />
                </div>
            </div>

            <transition name="fade">
                <p v-if="error" class="mt-1 text-sm text-red-600">
                    {{ error }}
                </p>
            </transition>
        </question-container>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed, watch } from "vue";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

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

const emit = defineEmits(["update:modelValue", "validate"]);

// Local copy of the value to handle the two-way binding
const localValue = ref({ ...props.modelValue });

// Watch for external changes to the modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        localValue.value = { ...newValue };
    },
    { deep: true }
);

// Parse options from the question
const parsedOptions = computed(() => {
    if (!props.question.options) return [];

    // If options is a string, try to parse it as JSON
    if (typeof props.question.options === "string") {
        try {
            return JSON.parse(props.question.options);
        } catch (e) {
            console.error("Failed to parse options:", e);
            return [];
        }
    }

    return props.question.options;
});

// Parse settings
const settings = computed(() => {
    if (!props.question.settings) return {};

    if (typeof props.question.settings === "string") {
        try {
            return JSON.parse(props.question.settings);
        } catch (e) {
            console.error("Failed to parse settings:", e);
            return {};
        }
    }

    return props.question.settings;
});

// Get placeholder text
const placeholder = computed(() => {
    return props.question.placeholder || "Pilih salah satu opsi...";
});

// Check if the question has "other" option
const hasOtherOption = computed(() => {
    return settings.value.allowOther || false;
});

// Label for the "other" option
const otherOptionLabel = computed(() => {
    return settings.value.otherLabel || "Lainnya";
});

// Placeholder for the "other" text input
const otherPlaceholder = computed(() => {
    return settings.value.otherPlaceholder || "Ketik di sini...";
});

// Check if the "other" option is selected
const isOtherSelected = computed(() => {
    return localValue.value.value === "other";
});

// Update the selected value
const updateValue = (value) => {
    localValue.value.value = value;

    // Clear other text if "other" is not selected
    if (value !== "other") {
        localValue.value.otherText = "";
    }

    emit("update:modelValue", { ...localValue.value });
    validate();
};

// Update the "other" text
const updateOtherText = () => {
    emit("update:modelValue", { ...localValue.value });
    validate();
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required) {
        if (!localValue.value.value) {
            isValid = false;
            errorMessage = "Pertanyaan ini wajib dijawab.";
        } else if (
            localValue.value.value === "other" &&
            !localValue.value.otherText.trim()
        ) {
            isValid = false;
            errorMessage = 'Harap isi kolom "Lainnya".';
        }
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
select {
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
}

select:focus {
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
