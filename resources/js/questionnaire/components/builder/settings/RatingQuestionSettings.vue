<template>
    <div>
        <QuestionSettingsPanel
            :question="question"
            @update:question="updateQuestionBase"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        >
            <template #type-specific-settings>
                <!-- Rating settings -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Pengaturan Rating
                    </h3>

                    <!-- Rating Count -->
                    <div class="mb-4">
                        <label
                            for="rating-count"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Jumlah Bintang
                        </label>
                        <input
                            id="rating-count"
                            type="number"
                            v-model.number="localMaxRating"
                            min="1"
                            max="10"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Jumlah bintang yang akan ditampilkan (1-10)
                        </p>
                    </div>

                    <!-- Min Rating Value -->
                    <div class="mb-4">
                        <label
                            for="min-rating"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Nilai Minimum
                        </label>
                        <input
                            id="min-rating"
                            type="number"
                            v-model.number="localMinRating"
                            min="0"
                            :max="localMaxRating - 1"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Nilai terendah yang dapat dipilih
                        </p>
                    </div>

                    <!-- Max Rating Value -->
                    <div class="mb-4">
                        <label
                            for="max-rating-value"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Nilai Maksimum
                        </label>
                        <input
                            id="max-rating-value"
                            type="number"
                            v-model.number="localMaxRatingValue"
                            :min="localMinRating + 1"
                            max="10"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Nilai tertinggi yang dapat dipilih
                        </p>
                    </div>

                    <!-- Step Value -->
                    <div class="mb-4">
                        <label
                            for="step-value"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Nilai Langkah
                        </label>
                        <input
                            id="step-value"
                            type="number"
                            v-model.number="localStepValue"
                            min="0.1"
                            step="0.1"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Peningkatan nilai antar bintang (misalnya 0.5 untuk
                            setengah bintang)
                        </p>
                    </div>

                    <!-- Rating labels -->
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">
                            Label Rating
                        </h4>

                        <!-- Min value label -->
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
                                v-model="localLabels[localMinRating]"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Contoh: Sangat Buruk"
                                @change="updateQuestion"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Label untuk nilai {{ localMinRating }} (paling
                                kiri)
                            </p>
                        </div>

                        <!-- Max value label -->
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
                                v-model="localLabels[localMaxRatingValue]"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Contoh: Sangat Baik"
                                @change="updateQuestion"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Label untuk nilai
                                {{ localMaxRatingValue }} (paling kanan)
                            </p>
                        </div>
                    </div>

                    <!-- Rating Preview -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">
                            Preview Rating
                        </h4>
                        <div
                            class="flex items-center justify-between text-xs text-gray-500"
                        >
                            <span>{{
                                localLabels[localMinRating] ||
                                `${localMinRating} bintang`
                            }}</span>
                            <span>{{
                                localLabels[localMaxRatingValue] ||
                                `${localMaxRatingValue} bintang`
                            }}</span>
                        </div>
                        <div
                            class="mt-2 flex items-center justify-center space-x-1"
                        >
                            <template v-for="n in localMaxRating" :key="n">
                                <span
                                    :class="
                                        n <= Math.ceil(localMaxRating / 2)
                                            ? 'text-yellow-400'
                                            : 'text-gray-300'
                                    "
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                        />
                                    </svg>
                                </span>
                            </template>
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

// Local state for rating-specific settings
const localMaxRating = ref(props.question.maxRating || 5);
const localMinRating = ref(props.question.minRating || 1);
const localMaxRatingValue = ref(props.question.maxRatingValue || 5);
const localStepValue = ref(props.question.stepValue || 1);
const localLabels = ref(
    props.question.labels ? { ...props.question.labels } : {}
);

// Initialize default labels if not present
const initializeLabels = () => {
    if (!localLabels.value[localMinRating.value]) {
        localLabels.value[localMinRating.value] = "Sangat Buruk";
    }

    if (!localLabels.value[localMaxRatingValue.value]) {
        localLabels.value[localMaxRatingValue.value] = "Sangat Baik";
    }
};

// Initialize labels on component creation
initializeLabels();

// Watch for changes in the question prop
watch(
    () => props.question,
    (newQuestion) => {
        localMaxRating.value = newQuestion.maxRating || 5;
        localMinRating.value = newQuestion.minRating || 1;
        localMaxRatingValue.value = newQuestion.maxRatingValue || 5;
        localStepValue.value = newQuestion.stepValue || 1;

        if (newQuestion.labels) {
            localLabels.value = { ...newQuestion.labels };
        } else {
            localLabels.value = {};
            initializeLabels();
        }
    },
    { deep: true }
);

// Watch for changes in min/max rating values to update labels
watch([() => localMinRating.value, () => localMaxRatingValue.value], () => {
    // Ensure we have labels for min and max ratings
    initializeLabels();
    updateQuestion();
});

// Update base question properties
const updateQuestionBase = (updatedBaseQuestion) => {
    emit("update:question", {
        ...updatedBaseQuestion,
        maxRating: localMaxRating.value,
        minRating: localMinRating.value,
        maxRatingValue: localMaxRatingValue.value,
        stepValue: localStepValue.value,
        labels: localLabels.value,
    });
};

// Update rating-specific settings
const updateQuestion = () => {
    // Ensure min is not greater than max
    if (localMinRating.value >= localMaxRatingValue.value) {
        localMinRating.value = localMaxRatingValue.value - 1;
    }

    // Ensure max rating value doesn't exceed the max rating count
    if (localMaxRatingValue.value > localMaxRating.value) {
        localMaxRatingValue.value = localMaxRating.value;
    }

    // Ensure we have labels for the current min/max values
    initializeLabels();

    emit("update:question", {
        ...props.question,
        maxRating: localMaxRating.value,
        minRating: localMinRating.value,
        maxRatingValue: localMaxRatingValue.value,
        stepValue: localStepValue.value,
        labels: localLabels.value,
    });
};
</script>
