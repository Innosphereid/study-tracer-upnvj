<template>
    <div>
        <QuestionSettingsPanel
            :question="question"
            @update:question="updateQuestionBase"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        >
            <template #type-specific-settings>
                <!-- Likert specific settings -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Pengaturan Skala Likert
                    </h3>

                    <!-- Scale Count -->
                    <div class="mb-4">
                        <label
                            for="scale-count"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Jumlah Skala
                        </label>
                        <input
                            id="scale-count"
                            type="number"
                            v-model.number="localScaleCount"
                            min="2"
                            max="5"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateScale"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Jumlah skala dalam pertanyaan (2-5)
                        </p>
                    </div>

                    <!-- Min Label -->
                    <div class="mb-4">
                        <label
                            for="min-label"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Label Nilai Minimum
                        </label>
                        <input
                            id="min-label"
                            type="text"
                            v-model="minLabel"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Contoh: Sangat Tidak Setuju"
                            @change="updateScale"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Label untuk nilai terendah dalam skala
                        </p>
                    </div>

                    <!-- Max Label -->
                    <div class="mb-4">
                        <label
                            for="max-label"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Label Nilai Maksimum
                        </label>
                        <input
                            id="max-label"
                            type="text"
                            v-model="maxLabel"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Contoh: Sangat Setuju"
                            @change="updateScale"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Label untuk nilai tertinggi dalam skala
                        </p>
                    </div>

                    <!-- Preview -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">
                            Preview Skala
                        </h4>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-gray-500">{{
                                minLabel
                            }}</span>
                            <span class="text-xs text-gray-500">{{
                                maxLabel
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span
                                v-for="(option, index) in localScale"
                                :key="index"
                                class="text-xs bg-gray-100 rounded-md px-2 py-1 border border-gray-200"
                            >
                                {{ option.value }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </QuestionSettingsPanel>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";
import QuestionSettingsPanel from "./QuestionSettingsPanel.vue";
import { v4 as uuidv4 } from "uuid";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "update:question",
    "duplicate-question",
    "delete-question",
]);

// Local state for likert-specific settings
const localScaleCount = ref(props.question.scale?.length || 5);
const localScale = ref(
    props.question.scale || [
        { value: 1, label: "Sangat Tidak Setuju" },
        { value: 2, label: "Tidak Setuju" },
        { value: 3, label: "Netral" },
        { value: 4, label: "Setuju" },
        { value: 5, label: "Sangat Setuju" },
    ]
);

// Initialize statements array based on the question or create a default one
const localStatements = ref(
    props.question.statements || [
        {
            id: uuidv4(),
            text: props.question.text || "Pernyataan 1",
        },
    ]
);

// Computed properties for minimum and maximum labels
const minLabel = computed({
    get: () => {
        if (localScale.value.length > 0) {
            return localScale.value[0].label;
        }
        return "Sangat Tidak Setuju";
    },
    set: (value) => {
        if (localScale.value.length > 0) {
            localScale.value[0].label = value;
        }
    },
});

const maxLabel = computed({
    get: () => {
        if (localScale.value.length > 0) {
            return localScale.value[localScale.value.length - 1].label;
        }
        return "Sangat Setuju";
    },
    set: (value) => {
        if (localScale.value.length > 0) {
            localScale.value[localScale.value.length - 1].label = value;
        }
    },
});

// Watch for changes in the question prop
watch(
    () => props.question,
    (newQuestion) => {
        if (newQuestion.scale) {
            localScale.value = [...newQuestion.scale];
            localScaleCount.value = newQuestion.scale.length;
        }

        // Update statements if they exist in the question
        if (newQuestion.statements) {
            localStatements.value = [...newQuestion.statements];
        }
        // If statements don't exist but text changed, update the first statement text
        else if (newQuestion.text && localStatements.value.length > 0) {
            localStatements.value[0].text = newQuestion.text;
        }
    },
    { deep: true }
);

// Generate scale based on count
const updateScale = () => {
    // Ensure scale count is within range
    if (localScaleCount.value < 2) localScaleCount.value = 2;
    if (localScaleCount.value > 5) localScaleCount.value = 5;

    // Save the first and last labels
    const firstLabel =
        localScale.value.length > 0
            ? localScale.value[0].label
            : "Sangat Tidak Setuju";
    const lastLabel =
        localScale.value.length > 0
            ? localScale.value[localScale.value.length - 1].label
            : "Sangat Setuju";

    // Create a new scale with proper values
    localScale.value = Array.from({ length: localScaleCount.value }, (_, i) => {
        const value = i + 1;

        if (i === 0) {
            return { value, label: firstLabel };
        } else if (i === localScaleCount.value - 1) {
            return { value, label: lastLabel };
        } else if (i === Math.floor(localScaleCount.value / 2)) {
            return { value, label: "Netral" };
        } else if (i < Math.floor(localScaleCount.value / 2)) {
            return { value, label: "Tidak Setuju" };
        } else {
            return { value, label: "Setuju" };
        }
    });

    updateQuestion();
};

// Update base question properties
const updateQuestionBase = (updatedBaseQuestion) => {
    // If the text changed, update the first statement's text
    if (
        updatedBaseQuestion.text !== props.question.text &&
        localStatements.value.length > 0
    ) {
        localStatements.value[0].text = updatedBaseQuestion.text;
    }

    emit("update:question", {
        ...updatedBaseQuestion,
        scale: localScale.value,
        statements: localStatements.value,
    });
};

// Update likert-specific settings
const updateQuestion = () => {
    emit("update:question", {
        ...props.question,
        scale: localScale.value,
        statements: localStatements.value,
    });
};
</script>
