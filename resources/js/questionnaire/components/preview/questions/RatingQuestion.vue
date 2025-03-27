<template>
    <div class="rating-question">
        <div class="flex flex-wrap justify-between items-center gap-2 mt-2">
            <template v-for="(n, index) in 5" :key="index">
                <button
                    type="button"
                    :class="[
                        'flex justify-center items-center rounded-full w-12 h-12 font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
                        modelValue === n.toString()
                            ? 'bg-blue-500 text-white hover:bg-blue-600'
                            : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50',
                    ]"
                    @click="selectRating(n)"
                >
                    {{ n }}
                </button>
            </template>
        </div>

        <div class="flex justify-between mt-1 text-xs text-gray-500">
            <span>{{ question.labels?.min || "Sangat buruk" }}</span>
            <span>{{ question.labels?.max || "Sangat baik" }}</span>
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

const selectRating = (value) => {
    emit("update:modelValue", value.toString());
};
</script>

<style scoped>
.rating-question button {
    position: relative;
    overflow: hidden;
}

.rating-question button::after {
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

.rating-question button:focus::after {
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
