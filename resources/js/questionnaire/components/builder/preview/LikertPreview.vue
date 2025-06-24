<template>
    <div class="mt-2">
        <div class="flex flex-col">
            <!-- Scale options in horizontal layout -->
            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                <div class="flex items-center justify-between w-full px-2">
                    <div
                        class="flex flex-1 justify-between overflow-x-auto py-1"
                    >
                        <!-- Generate scale options from the scale property -->
                        <template
                            v-for="(option, index) in displayScale"
                            :key="index"
                        >
                            <div class="flex flex-col items-center mx-1">
                                <input
                                    type="radio"
                                    disabled
                                    class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 flex-shrink-0"
                                    :checked="
                                        index ===
                                        Math.floor(displayScale.length / 2)
                                    "
                                />
                                <label
                                    class="text-xs text-gray-600 mt-1 text-center whitespace-nowrap"
                                    style="min-width: 20px"
                                >
                                    {{ option.value }}
                                </label>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Scale legend -->
            <div class="flex items-center justify-between mt-2 mb-2 px-2">
                <span class="text-xs text-gray-500">
                    {{ minLabel }}
                </span>
                <span class="text-xs text-gray-500">
                    {{ maxLabel }}
                </span>
            </div>

            <!-- Help text -->
            <div class="mt-2 flex items-center text-xs text-gray-500">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
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
                <span> Skala Likert {{ displayScale.length }} poin </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, computed } from "vue";
import { useDefaultConfigs } from "./composables/useDefaultConfigs";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const { defaultLikertScale } = useDefaultConfigs();

// Computed properties to ensure we always have values for display
const displayScale = computed(() => {
    return props.question.scale || defaultLikertScale;
});

const minLabel = computed(() => {
    if (displayScale.value.length > 0) {
        return displayScale.value[0].label;
    }
    return "Sangat Tidak Setuju";
});

const maxLabel = computed(() => {
    if (displayScale.value.length > 0) {
        return displayScale.value[displayScale.value.length - 1].label;
    }
    return "Sangat Setuju";
});
</script>
