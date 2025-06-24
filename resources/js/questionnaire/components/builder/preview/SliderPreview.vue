<template>
    <div class="mt-2">
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
            <!-- Container for slider with min/max labels -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs text-gray-500">
                        {{ question.min ?? defaultSliderConfig.min }}
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ question.max ?? defaultSliderConfig.max }}
                    </span>
                </div>

                <!-- Slider track with thumb -->
                <div class="relative">
                    <!-- Track background -->
                    <div class="w-full h-2 bg-gray-200 rounded-full"></div>

                    <!-- Colored portion of the track (from min to current value) -->
                    <div
                        class="absolute top-0 left-0 h-2 bg-indigo-500 rounded-full"
                        :style="{
                            width: `${calculateSliderPercentage(question)}%`,
                        }"
                    ></div>

                    <!-- Thumb -->
                    <div
                        class="absolute top-0 h-5 w-5 bg-white border-2 border-indigo-500 rounded-full shadow transform -translate-y-1.5 cursor-not-allowed"
                        :style="{
                            left: `calc(${calculateSliderPercentage(
                                question
                            )}% - 10px)`,
                        }"
                    ></div>
                </div>
            </div>

            <!-- Current value display -->
            <div class="flex justify-center items-center mb-3">
                <div
                    class="px-3 py-1 bg-indigo-100 rounded-full text-sm text-indigo-700 font-medium"
                >
                    {{ calculateCurrentValue(question) }}
                </div>
            </div>

            <!-- Labels for value meanings, if provided -->
            <div class="relative h-10 mb-2">
                <template
                    v-for="(label, index) in question.labels ??
                    defaultSliderConfig.labels"
                    :key="index"
                >
                    <div
                        class="absolute flex flex-col items-center"
                        :style="{
                            left: `${calculateLabelPosition(
                                label.value,
                                question
                            )}%`,
                            transform: 'translateX(-50%)',
                        }"
                    >
                        <div
                            class="w-1 h-3 bg-gray-300 mb-1"
                            :class="{
                                'bg-indigo-500':
                                    label.value <=
                                    calculateCurrentValue(question),
                            }"
                        ></div>
                        <span class="text-xs text-gray-600 whitespace-nowrap">
                            {{ label.text }}
                        </span>
                    </div>
                </template>
            </div>

            <!-- Step indicators -->
            <div
                v-if="(question.step ?? defaultSliderConfig.step) > 1"
                class="flex items-center justify-center space-x-1 text-xs text-gray-500 mt-2"
            >
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
                <span
                    >Interval:
                    {{ question.step ?? defaultSliderConfig.step }}</span
                >
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
                <span>
                    Slider dengan nilai dari
                    {{ question.min ?? defaultSliderConfig.min }}
                    hingga
                    {{ question.max ?? defaultSliderConfig.max }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from "vue";
import { useDefaultConfigs } from "./composables/useDefaultConfigs";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const {
    defaultSliderConfig,
    calculateSliderPercentage,
    calculateCurrentValue,
    calculateLabelPosition,
} = useDefaultConfigs();
</script>
