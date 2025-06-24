<!--
* @component Modal
* @description A reusable modal component that displays content in a centered overlay.
* Supports closing via a close button or clicking outside the modal.
* Can be used as a base component for various modal needs in the application.
-->
<template>
    <Teleport to="body">
        <div v-if="modelValue" class="modal-container">
            <!-- Backdrop with blur effect -->
            <div
                class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 transition-opacity"
                :class="{ 'opacity-0': !modelValue, 'opacity-100': modelValue }"
                @click="closeOnBackdropClick && close()"
            ></div>

            <!-- Modal Dialog -->
            <div
                class="fixed inset-0 flex items-center justify-center p-4 z-50"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="titleId"
            >
                <!-- Modal Content -->
                <div
                    ref="modalContent"
                    class="bg-white rounded-lg shadow-xl w-full max-w-md relative transform transition-all duration-300 ease-in-out"
                    :class="[
                        sizeClass,
                        {
                            'translate-y-0 opacity-100': modelValue,
                            'translate-y-4 opacity-0': !modelValue,
                        },
                    ]"
                >
                    <!-- Close Button -->
                    <button
                        v-if="showCloseButton"
                        type="button"
                        class="absolute top-3 right-3 text-gray-400 hover:text-gray-500 z-10"
                        @click="close"
                        aria-label="Close"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <!-- Modal Title -->
                    <div v-if="title" class="px-6 pt-6 pb-0">
                        <h3
                            :id="titleId"
                            class="text-lg font-medium text-gray-900"
                        >
                            {{ title }}
                        </h3>
                    </div>

                    <!-- Modal Content Slot -->
                    <div :class="contentClass">
                        <slot></slot>
                    </div>

                    <!-- Modal Footer -->
                    <div
                        v-if="$slots.footer"
                        class="px-6 py-4 bg-gray-50 rounded-b-lg"
                    >
                        <slot name="footer"></slot>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script>
import {
    computed,
    ref,
    watch,
    onMounted,
    onBeforeUnmount,
    nextTick,
} from "vue";

/**
 * @description Reusable modal component
 */
export default {
    name: "Modal",

    props: {
        /**
         * Controls whether the modal is visible
         */
        modelValue: {
            type: Boolean,
            required: true,
        },

        /**
         * Optional title for the modal
         */
        title: {
            type: String,
            default: "",
        },

        /**
         * Size of the modal - controls max width
         */
        size: {
            type: String,
            default: "md",
            validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
        },

        /**
         * Whether to show the close button
         */
        showCloseButton: {
            type: Boolean,
            default: true,
        },

        /**
         * Whether clicking on the backdrop closes the modal
         */
        closeOnBackdropClick: {
            type: Boolean,
            default: true,
        },

        /**
         * CSS class to apply to the content area
         */
        contentClass: {
            type: String,
            default: "px-6 py-6",
        },
    },

    emits: ["update:modelValue", "close"],

    setup(props, { emit }) {
        const modalContent = ref(null);
        const titleId = `modal-title-${Math.random()
            .toString(36)
            .substring(2, 9)}`;

        /**
         * Computed class based on the size prop
         */
        const sizeClass = computed(() => {
            const sizes = {
                sm: "max-w-sm",
                md: "max-w-md",
                lg: "max-w-lg",
                xl: "max-w-xl",
            };
            return sizes[props.size] || sizes.md;
        });

        /**
         * Close the modal
         */
        const close = () => {
            emit("update:modelValue", false);
            emit("close");
        };

        /**
         * Handle escape key press to close modal
         */
        const handleEscKey = (event) => {
            if (event.key === "Escape" && props.modelValue) {
                close();
            }
        };

        /**
         * Focus trap inside modal
         */
        const handleTabKey = (event) => {
            if (!modalContent.value || !props.modelValue) return;

            const focusableElements = modalContent.value.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );

            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (event.shiftKey && document.activeElement === firstElement) {
                lastElement.focus();
                event.preventDefault();
            } else if (
                !event.shiftKey &&
                document.activeElement === lastElement
            ) {
                firstElement.focus();
                event.preventDefault();
            }
        };

        /**
         * Handle key events
         */
        const handleKeyDown = (event) => {
            if (event.key === "Escape") {
                handleEscKey(event);
            } else if (event.key === "Tab") {
                handleTabKey(event);
            }
        };

        /**
         * Focus first focusable element when modal opens
         */
        const focusFirstElement = () => {
            if (!modalContent.value) return;

            nextTick(() => {
                const focusableElements = modalContent.value.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );

                if (focusableElements.length > 0) {
                    focusableElements[0].focus();
                } else {
                    modalContent.value.focus();
                }
            });
        };

        // Watch for changes in visibility
        watch(
            () => props.modelValue,
            (newVal) => {
                if (newVal) {
                    document.body.style.overflow = "hidden"; // Prevent scrolling when modal is open
                    nextTick(() => {
                        focusFirstElement();
                    });
                } else {
                    document.body.style.overflow = ""; // Restore scrolling
                }
            },
            { immediate: true }
        );

        // Lifecycle hooks
        onMounted(() => {
            document.addEventListener("keydown", handleKeyDown);
        });

        onBeforeUnmount(() => {
            document.removeEventListener("keydown", handleKeyDown);
            document.body.style.overflow = ""; // Ensure scroll is restored when component unmounts
        });

        return {
            close,
            modalContent,
            titleId,
            sizeClass,
        };
    },
};
</script>

<style scoped>
.modal-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 50;
}
</style>
