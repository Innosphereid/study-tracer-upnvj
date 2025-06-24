<!--
* @component EmptyState
* @description A reusable component to display an empty state message when no data is available
-->
<template>
    <div
        class="bg-white border border-gray-100 rounded-lg p-6 text-center shadow-sm"
    >
        <div class="flex flex-col items-center">
            <!-- Icon -->
            <div
                class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-500 mb-4"
            >
                <svg
                    v-if="icon === 'calendar'"
                    class="h-7 w-7"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    ></path>
                </svg>
                <svg
                    v-else-if="icon === 'chart'"
                    class="h-7 w-7"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    ></path>
                </svg>
                <svg
                    v-else-if="icon === 'data'"
                    class="h-7 w-7"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                    ></path>
                </svg>
                <svg
                    v-else
                    class="h-7 w-7"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    ></path>
                </svg>
            </div>

            <!-- Title -->
            <h3 class="text-lg font-medium text-gray-900 mb-1">{{ title }}</h3>

            <!-- Description -->
            <p class="text-sm text-gray-500 mb-4">{{ description }}</p>

            <!-- Action -->
            <div v-if="actionText && actionHandler" class="mt-2">
                <button
                    @click="actionHandler"
                    class="px-4 py-2 bg-blue-500 text-white text-sm rounded-md hover:bg-blue-600 transition-colors"
                >
                    {{ actionText }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * A reusable empty state component that displays when there is no data to show
 * @module EmptyState
 */
export default {
    name: "EmptyState",
    props: {
        /**
         * The title to display in the empty state
         */
        title: {
            type: String,
            required: true,
            default: "No data available",
        },
        /**
         * The description text to display below the title
         */
        description: {
            type: String,
            required: false,
            default:
                "Try adjusting your filters or selecting a different time period.",
        },
        /**
         * The icon to display. Options: 'calendar', 'chart', 'data', or 'info' (default)
         */
        icon: {
            type: String,
            required: false,
            default: "info",
            validator: (value) => {
                return ["calendar", "chart", "data", "info"].includes(value);
            },
        },
        /**
         * Text for the action button. If not provided, no button will be shown.
         */
        actionText: {
            type: String,
            required: false,
            default: "",
        },
    },
    emits: ["action"],
    setup(props, { emit }) {
        /**
         * Handler for the action button click
         */
        const actionHandler = () => {
            emit("action");
        };

        return {
            actionHandler,
        };
    },
};
</script>

<style scoped>
/* Additional styling if needed */
</style>
