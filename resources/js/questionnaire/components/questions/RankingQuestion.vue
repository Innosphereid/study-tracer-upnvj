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
                <!-- Ranking explanation -->
                <div class="mb-3 pb-2 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-medium text-gray-500">
                            Urutan peringkat
                        </div>
                        <div
                            class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded-full flex items-center"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-3 w-3 mr-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            Urutkan dengan menarik item
                        </div>
                    </div>
                </div>

                <!-- Sortable ranking items list -->
                <div class="space-y-2">
                    <transition-group name="rank-item">
                        <div
                            v-for="(option, index) in rankingOptions"
                            :key="option.id"
                            class="relative"
                            :data-id="option.id"
                            :class="{ 'cursor-grab': !isBuilder }"
                        >
                            <!-- Connecting line between items except for the last one -->
                            <div
                                v-if="index < rankingOptions.length - 1"
                                class="absolute left-4 top-10 h-4 w-0 border-l-2 border-dashed border-gray-300 z-0"
                            ></div>

                            <!-- Item container with relative z-index to be above connector line -->
                            <div
                                class="flex items-center bg-white border border-gray-200 rounded-md p-2 shadow-sm transition-colors relative z-10"
                                :class="[
                                    index === 0
                                        ? 'bg-indigo-50 border-indigo-200'
                                        : '',
                                    { 'hover:bg-gray-50': !isBuilder },
                                ]"
                            >
                                <!-- Rank Number Badge with custom colors based on rank -->
                                <div
                                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full font-bold mr-3 transition-colors"
                                    :class="[
                                        index === 0
                                            ? 'bg-indigo-100 text-indigo-800'
                                            : index === 1
                                            ? 'bg-blue-100 text-blue-800'
                                            : index === 2
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800',
                                    ]"
                                >
                                    {{ index + 1 }}
                                </div>

                                <!-- Item Content -->
                                <div class="flex-grow">
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >{{ option.text }}</span
                                    >
                                </div>

                                <!-- Drag Handle -->
                                <div
                                    v-if="!isBuilder"
                                    class="flex-shrink-0 px-2 py-1 text-gray-400 cursor-grab"
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
                                <div
                                    v-else
                                    class="flex-shrink-0 px-2 py-1 text-gray-400"
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
                        </div>
                    </transition-group>
                </div>

                <div v-if="error" class="mt-2 text-sm text-red-600">
                    {{ error }}
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script setup>
import {
    ref,
    computed,
    defineProps,
    defineEmits,
    watch,
    onMounted,
    nextTick,
} from "vue";
import { useDefaultConfigs } from "../builder/preview/composables/useDefaultConfigs";
import Sortable from "sortablejs";

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
    isBuilder: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

const { defaultRankingOptions } = useDefaultConfigs();

// Get the options from the question or use the defaults
const rankingOptions = computed(() => {
    // If we're in builder mode, return the options from the question
    if (props.isBuilder) {
        return props.question.options && props.question.options.length > 0
            ? props.question.options
            : defaultRankingOptions;
    }

    // If we have a model value use that order, otherwise use question options
    if (props.modelValue && props.modelValue.length > 0) {
        // Return in the order specified by modelValue
        const optionsMap = {};
        const options = props.question.options || defaultRankingOptions;

        // Create a map of options by id
        options.forEach((option) => {
            optionsMap[option.id] = option;
        });

        // Return options in the order specified by modelValue
        return props.modelValue.map((id) => optionsMap[id]).filter(Boolean);
    }

    // Default: return question options if available
    return props.question.options && props.question.options.length > 0
        ? props.question.options
        : defaultRankingOptions;
});

// Set up sortable if not in builder mode
let sortableInstance = null;

onMounted(() => {
    if (!props.isBuilder) {
        nextTick(() => {
            const container = document.querySelector(".space-y-2");
            if (container) {
                sortableInstance = Sortable.create(container, {
                    animation: 150,
                    ghostClass: "bg-indigo-50",
                    handle: ".cursor-grab",
                    onEnd: updateRanking,
                });
            }
        });
    }
});

// Update ranking when the order changes
function updateRanking(event) {
    // Get the new order from the DOM
    const items = event.from.querySelectorAll("[data-id]");
    const newOrder = Array.from(items).map((item) => item.dataset.id);

    // Emit the new order
    emit("update:modelValue", newOrder);
    validate();
}

// Validation function
function validate() {
    const isValid = props.question.required
        ? rankingOptions.value.length > 0
        : true;

    emit("validate", isValid);
    return isValid;
}

// Watch for changes in the model value
watch(
    () => props.modelValue,
    () => {
        validate();
    },
    { immediate: true }
);
</script>

<style scoped>
.rank-item-move {
    transition: transform 0.5s;
}
.rank-item-enter-active,
.rank-item-leave-active {
    transition: all 0.5s;
}
.rank-item-enter-from,
.rank-item-leave-to {
    opacity: 0;
    transform: translateY(30px);
}
</style>
