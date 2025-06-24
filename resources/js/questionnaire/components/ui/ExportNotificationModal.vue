<!--
* @component ExportNotificationModal
* @description A modal component that informs users about upcoming export features.
* Uses the base Modal component and displays custom content with icons for different export types.
-->
<template>
    <Modal v-model="isVisible" title="Export Feature" @close="onClose">
        <div class="text-center">
            <!-- Icon for the export type -->
            <div
                class="mb-4 inline-flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600"
            >
                <!-- PDF Icon -->
                <svg
                    v-if="exportType === 'pdf'"
                    class="h-8 w-8"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path d="M7 18H17V16H7V18Z" fill="currentColor" />
                    <path d="M17 14H7V12H17V14Z" fill="currentColor" />
                    <path d="M7 10H11V8H7V10Z" fill="currentColor" />
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6 2C4.34315 2 3 3.34315 3 5V19C3 20.6569 4.34315 22 6 22H18C19.6569 22 21 20.6569 21 19V9C21 5.13401 17.866 2 14 2H6ZM6 4H13V9H19V19C19 19.5523 18.5523 20 18 20H6C5.44772 20 5 19.5523 5 19V5C5 4.44772 5.44772 4 6 4ZM15 4.10002C16.6113 4.4271 17.9413 5.52906 18.584 7H15V4.10002Z"
                        fill="currentColor"
                    />
                </svg>

                <!-- Excel Icon -->
                <svg
                    v-else-if="exportType === 'excel'"
                    class="h-8 w-8"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M14 3H6C4.89543 3 4 3.89543 4 5V19C4 20.1046 4.89543 21 6 21H18C19.1046 21 20 20.1046 20 19V9L14 3Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M14 3V9H20"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M16 13H8"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M16 17H8"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M10 9H9H8"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                <!-- CSV Icon -->
                <svg
                    v-else-if="exportType === 'csv'"
                    class="h-8 w-8"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M14 3H6C4.89543 3 4 3.89543 4 5V19C4 20.1046 4.89543 21 6 21H18C19.1046 21 20 20.1046 20 19V9L14 3Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M14 3V9H20"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M12 13V17"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M8 13V17"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M16 13V17"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                <!-- Default Export Icon -->
                <svg
                    v-else
                    class="h-8 w-8"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M12 8V16M12 16L9 13M12 16L15 13"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M3 15C3 17.8284 3 19.2426 3.87868 20.1213C4.75736 21 6.17157 21 9 21H15C17.8284 21 19.2426 21 20.1213 20.1213C21 19.2426 21 17.8284 21 15V9C21 6.17157 21 4.75736 20.1213 3.87868C19.2426 3 17.8284 3 15 3H9C6.17157 3 4.75736 3 3.87868 3.87868C3 4.75736 3 6.17157 3 9V15Z"
                        stroke="currentColor"
                        stroke-width="2"
                    />
                </svg>
            </div>

            <!-- Message -->
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                {{ exportType.toUpperCase() }} Export Coming Soon
            </h3>
            <p class="text-gray-600 mb-6">
                We're currently working on implementing
                {{ exportType.toUpperCase() }} export functionality for your
                questionnaire results. This feature will be available in a
                future update.
            </p>

            <!-- Alternatives -->
            <div class="bg-blue-50 rounded-md p-4 mb-6 text-left">
                <h4 class="font-medium text-blue-700 mb-2">
                    Available alternatives:
                </h4>
                <ul class="text-sm text-blue-600 space-y-2">
                    <li v-if="exportType !== 'csv'" class="flex items-center">
                        <svg
                            class="h-4 w-4 mr-2"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>CSV export is currently available</span>
                    </li>
                    <li class="flex items-center">
                        <svg
                            class="h-4 w-4 mr-2"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>You can print individual question results</span>
                    </li>
                    <li class="flex items-center">
                        <svg
                            class="h-4 w-4 mr-2"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>Use browser print function for full report</span>
                    </li>
                </ul>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between">
                <button
                    @click="onFeedback"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                    Request feature
                </button>
                <button
                    @click="onClose"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                    Got it
                </button>
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from "./Modal.vue";
import { ref, watch } from "vue";

/**
 * @description Component for displaying notifications about export features coming soon
 */
export default {
    name: "ExportNotificationModal",

    components: {
        Modal,
    },

    props: {
        /**
         * Controls whether the modal is visible
         */
        show: {
            type: Boolean,
            default: false,
        },

        /**
         * The type of export (pdf, excel, csv)
         */
        exportType: {
            type: String,
            default: "pdf",
            validator: (value) => ["pdf", "excel", "csv"].includes(value),
        },
    },

    emits: ["update:show", "close", "feedback"],

    setup(props, { emit }) {
        const isVisible = ref(props.show);

        // Sync with parent's show prop
        watch(
            () => props.show,
            (newVal) => {
                isVisible.value = newVal;
            }
        );

        watch(isVisible, (newVal) => {
            emit("update:show", newVal);
        });

        /**
         * Handle close event
         */
        const onClose = () => {
            isVisible.value = false;
            emit("close");
        };

        /**
         * Handle feedback button click
         */
        const onFeedback = () => {
            emit("feedback", props.exportType);
            onClose();
        };

        return {
            isVisible,
            onClose,
            onFeedback,
        };
    },
};
</script>
