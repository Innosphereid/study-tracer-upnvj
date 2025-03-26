<template>
    <div class="mt-2">
        <div class="flex flex-col">
            <!-- Sample statement row for preview -->
            <div class="mb-3 bg-gray-50 p-3 rounded-md border border-gray-200">
                <!-- Statement text -->
                <div class="mb-3 text-sm font-medium text-gray-700">
                    {{
                        question.statements && question.statements.length > 0
                            ? question.statements[0].text
                            : "Pernyataan sampel"
                    }}
                </div>

                <!-- Scale options in horizontal layout -->
                <div class="flex items-center justify-between w-full px-2">
                    <div
                        class="flex flex-1 justify-between overflow-x-auto py-1"
                    >
                        <!-- Generate scale options from the scale property -->
                        <template
                            v-for="(option, index) in question.scale ||
                            defaultLikertScale"
                            :key="index"
                        >
                            <div class="flex flex-col items-center mx-1">
                                <input
                                    type="radio"
                                    disabled
                                    class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 flex-shrink-0"
                                    :checked="index === 2"
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
            <div class="flex items-center justify-between mb-2 px-2">
                <span class="text-xs text-gray-500">
                    {{
                        question.scale && question.scale.length > 0
                            ? question.scale[0].label
                            : "Sangat Tidak Setuju"
                    }}
                </span>
                <span class="text-xs text-gray-500">
                    {{
                        question.scale && question.scale.length > 0
                            ? question.scale[question.scale.length - 1].label
                            : "Sangat Setuju"
                    }}
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
                <span>
                    Skala
                    {{ question.scale ? question.scale.length : 5 }}
                    poin untuk
                    {{ question.statements ? question.statements.length : 1 }}
                    pernyataan
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

const { defaultLikertScale } = useDefaultConfigs();
</script>
