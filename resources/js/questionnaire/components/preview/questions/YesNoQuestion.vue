<template>
    <div class="yes-no-question">
        <div class="flex space-x-4 mt-2">
            <button
                type="button"
                class="flex-1 py-3 px-4 border rounded-md text-center text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200"
                :class="
                    modelValue === 'yes'
                        ? 'bg-green-100 border-green-500 text-green-700 hover:bg-green-200'
                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
                "
                @click="selectOption('yes')"
            >
                <div class="flex items-center justify-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                    Ya
                </div>
            </button>

            <button
                type="button"
                class="flex-1 py-3 px-4 border rounded-md text-center text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200"
                :class="
                    modelValue === 'no'
                        ? 'bg-red-100 border-red-500 text-red-700 hover:bg-red-200'
                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
                "
                @click="selectOption('no')"
            >
                <div class="flex items-center justify-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 mr-2"
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
                    Tidak
                </div>
            </button>
        </div>

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const selectOption = (value) => {
    emit("update:modelValue", value);
};
</script>

<style scoped>
.yes-no-question button {
    position: relative;
    overflow: hidden;
}

.yes-no-question button::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%);
    transform-origin: 50% 50%;
}

.yes-no-question button:focus::after {
    animation: ripple 1s ease-out;
}

@keyframes ripple {
    0% {
        transform: scale(0, 0);
        opacity: 0.5;
    }
    20% {
        transform: scale(25, 25);
        opacity: 0.3;
    }
    100% {
        opacity: 0;
        transform: scale(40, 40);
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
