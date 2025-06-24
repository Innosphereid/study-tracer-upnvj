<template>
    <div class="ranking-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div v-if="instruction" class="mt-2 mb-4 text-sm text-gray-600">
                {{ instruction }}
            </div>

            <div class="mt-3 space-y-3">
                <draggable
                    v-model="items"
                    :animation="200"
                    handle=".item-handle"
                    item-key="id"
                    class="space-y-2"
                    @end="updateRanking"
                >
                    <template #item="{ element, index }">
                        <div
                            class="flex items-center p-3 bg-white border rounded-lg shadow-sm transition-all duration-200"
                            :class="{
                                'border-indigo-300': isDragging === element.id,
                                'hover:border-indigo-300':
                                    isDragging !== element.id,
                            }"
                            @mousedown="isDragging = element.id"
                            @mouseup="isDragging = null"
                            @touchstart="isDragging = element.id"
                            @touchend="isDragging = null"
                        >
                            <div class="flex items-center flex-1">
                                <div
                                    class="w-8 h-8 flex items-center justify-center bg-indigo-100 text-indigo-700 rounded-full mr-3 font-semibold text-sm"
                                >
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-900">{{
                                        element.label
                                    }}</span>
                                </div>
                            </div>
                            <div
                                class="item-handle flex items-center cursor-move p-2 text-gray-400 hover:text-gray-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 8h16M4 16h16"
                                    />
                                </svg>
                            </div>
                        </div>
                    </template>
                </draggable>
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
import { defineProps, defineEmits, ref, computed, watch } from "vue";
import draggable from "vuedraggable";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Track dragging state
const isDragging = ref(null);

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

// Get instruction text
const instruction = computed(() => {
    return (
        settings.value.instruction ||
        "Urutkan item berikut berdasarkan preferensi Anda dengan menyeret item ke posisi yang diinginkan."
    );
});

// Get options to rank
const options = computed(() => {
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

// Initialize items from options or modelValue
const items = ref([]);

// Initialize items on component creation
const initializeItems = () => {
    if (props.modelValue && props.modelValue.length > 0) {
        // If we have a model value, use that ordering
        items.value = [...props.modelValue];
    } else {
        // Otherwise initialize from options with random order if randomize is true
        let initialItems = [...options.value];

        if (settings.value.randomizeInitialOrder) {
            // Shuffle the array
            for (let i = initialItems.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [initialItems[i], initialItems[j]] = [
                    initialItems[j],
                    initialItems[i],
                ];
            }
        }

        items.value = initialItems;
        emit("update:modelValue", initialItems);
    }
};

// Update ranking after drag ends
const updateRanking = () => {
    emit("update:modelValue", items.value);
    validate();
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && items.value.length === 0) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};

// Watch for changes in options
watch(
    () => options.value,
    () => {
        initializeItems();
    },
    { immediate: true }
);

// Watch for external changes in modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        if (
            newValue &&
            newValue.length > 0 &&
            JSON.stringify(newValue) !== JSON.stringify(items.value)
        ) {
            items.value = [...newValue];
        }
    },
    { deep: true }
);
</script>

<style scoped>
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
