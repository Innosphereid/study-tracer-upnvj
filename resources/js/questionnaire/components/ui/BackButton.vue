<!-- 
* @component BackButton
* @description A reusable back button component that provides consistent styling
* and behavior for navigation. Can be used as a link or trigger a custom action.
-->
<template>
    <component
        :is="to ? 'a' : 'button'"
        :href="to"
        @click="to ? null : $emit('click')"
        class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="mr-2 h-4 w-4 text-gray-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7"
            />
        </svg>
        {{ text }}
    </component>
</template>

<script>
/**
 * BackButton Component
 *
 * @component
 * @example
 * <BackButton to="/kuesioner" text="Kembali" />
 * <BackButton @click="goBack" text="Kembali ke Dashboard" />
 */
export default {
    name: "BackButton",

    props: {
        /**
         * The URL to navigate to when clicked.
         * If not provided, the button will emit a 'click' event instead
         */
        to: {
            type: String,
            default: null,
        },

        /**
         * The text to display on the button
         */
        text: {
            type: String,
            default: "Kembali",
        },
    },

    emits: ["click"],
};
</script>
