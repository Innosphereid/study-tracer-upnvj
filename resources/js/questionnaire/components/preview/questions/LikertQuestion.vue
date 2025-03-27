<template>
    <div class="likert-question">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th
                            class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3"
                        >
                            {{ question.statement || "Pernyataan" }}
                        </th>
                        <th
                            v-for="(scale, index) in likertScales"
                            :key="index"
                            class="px-2 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            {{ scale.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(
                            statement, statementIndex
                        ) in question.statements"
                        :key="statementIndex"
                        class="hover:bg-gray-50 transition-colors duration-150"
                    >
                        <td
                            class="px-4 py-4 whitespace-normal text-sm text-gray-700 font-medium"
                        >
                            {{ statement.text }}
                        </td>
                        <td
                            v-for="(scale, scaleIndex) in likertScales"
                            :key="scaleIndex"
                            class="px-2 py-4 text-center"
                        >
                            <div class="flex justify-center">
                                <input
                                    type="radio"
                                    :name="`likert-${question.id}-statement-${statementIndex}`"
                                    :value="scale.value"
                                    :checked="
                                        getStatementValue(statementIndex) ===
                                        scale.value
                                    "
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer transition-all duration-150 hover:scale-110"
                                    @change="
                                        updateStatementValue(
                                            statementIndex,
                                            scale.value
                                        )
                                    "
                                />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            class="flex flex-wrap justify-between text-xs text-gray-500 mt-2 px-4"
        >
            <span>{{
                question.leftLabel || likertScales[0]?.label || "Disagree"
            }}</span>
            <span>{{
                question.rightLabel ||
                likertScales[likertScales.length - 1]?.label ||
                "Agree"
            }}</span>
        </div>

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Object,
        default: () => ({ responses: {} }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// Get likert scale from question or use default
const likertScales = computed(() => {
    if (props.question.scales && props.question.scales.length > 0) {
        return props.question.scales;
    }

    // Default 5-point Likert scale
    return [
        { value: 1, label: "Sangat Tidak Setuju" },
        { value: 2, label: "Tidak Setuju" },
        { value: 3, label: "Netral" },
        { value: 4, label: "Setuju" },
        { value: 5, label: "Sangat Setuju" },
    ];
});

// Get the current value for a statement
const getStatementValue = (statementIndex) => {
    if (!props.modelValue || !props.modelValue.responses) return null;
    return props.modelValue.responses[statementIndex];
};

// Update a statement's value
const updateStatementValue = (statementIndex, value) => {
    const newResponses = { ...(props.modelValue?.responses || {}) };
    newResponses[statementIndex] = value;
    emit("update:modelValue", { responses: newResponses });
};
</script>

<style scoped>
.likert-question table {
    border-collapse: separate;
    border-spacing: 0;
}

.likert-question th,
.likert-question td {
    border: 1px solid #e5e7eb;
}

.likert-question th:first-child {
    border-top-left-radius: 0.5rem;
}

.likert-question th:last-child {
    border-top-right-radius: 0.5rem;
}

.likert-question tr:last-child td:first-child {
    border-bottom-left-radius: 0.5rem;
}

.likert-question tr:last-child td:last-child {
    border-bottom-right-radius: 0.5rem;
}

.likert-question input[type="radio"] {
    position: relative;
    cursor: pointer;
}

.likert-question input[type="radio"]:checked {
    transform: scale(1.2);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Responsive styles */
@media (max-width: 640px) {
    .likert-question {
        overflow-x: auto;
    }
}
</style>
