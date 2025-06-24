<template>
    <div class="likert-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-4">
                <div class="likert-scale grid gap-2 py-2">
                    <!-- Scale labels for desktop -->
                    <div
                        class="hidden md:grid grid-cols-likert-no-statement gap-1"
                    >
                        <div
                            v-for="option in likertOptions"
                            :key="option.value"
                            class="col-span-1 text-center text-sm text-gray-600 font-medium px-1"
                        >
                            {{ option.label }}
                        </div>
                    </div>

                    <!-- Scale options -->
                    <div
                        class="grid grid-cols-likert-no-statement gap-1 items-center"
                    >
                        <div
                            v-for="option in likertOptions"
                            :key="option.value"
                            class="col-span-1 flex flex-col items-center"
                        >
                            <input
                                type="radio"
                                :id="`likert-${question.id}-${option.value}`"
                                :name="`question-${question.id}`"
                                :value="option.value"
                                :checked="modelValue === option.value"
                                @change="updateValue(option.value)"
                                class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer"
                            />
                            <!-- Mobile only label -->
                            <label
                                :for="`likert-${question.id}-${option.value}`"
                                class="md:hidden mt-1 text-xs text-center text-gray-600"
                            >
                                {{ option.label }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <transition name="fade">
                <p v-if="error" class="mt-3 text-sm text-red-600">
                    {{ error }}
                </p>
            </transition>
        </question-container>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from "vue";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

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
});

const emit = defineEmits(["update:modelValue", "validate"]);

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

// Get statement text (tidak lagi digunakan di template, tapi disimpan untuk kompatibilitas)
const statement = computed(() => {
    return settings.value.statement || props.question.text;
});

// Get scale options
const likertOptions = computed(() => {
    const options = settings.value.options || [];

    if (options.length === 0) {
        // Default 5-point Likert scale
        return [
            { value: 1, label: "Sangat Tidak Setuju" },
            { value: 2, label: "Tidak Setuju" },
            { value: 3, label: "Netral" },
            { value: 4, label: "Setuju" },
            { value: 5, label: "Sangat Setuju" },
        ];
    }

    return options;
});

// Update the selected value
const updateValue = (value) => {
    emit("update:modelValue", parseInt(value, 10));
    validate();
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (
        props.question.required &&
        (!props.modelValue || props.modelValue === 0)
    ) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
.grid-cols-likert-no-statement {
    grid-template-columns: repeat(5, 1fr);
}

@media (max-width: 768px) {
    .grid-cols-likert-no-statement {
        grid-template-columns: repeat(5, 1fr);
    }
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
