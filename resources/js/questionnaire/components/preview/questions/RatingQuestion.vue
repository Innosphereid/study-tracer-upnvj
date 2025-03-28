<template>
    <div class="rating-question">
        <div class="rating-stars mt-2 flex items-center justify-center">
            <div
                v-for="n in maxRating"
                :key="n"
                class="rating-star p-1 cursor-pointer"
                @click="selectRating(n)"
                @mouseenter="showHover(n)"
                @mouseleave="clearHover()"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    :class="[
                        'w-8 h-8 transition-all',
                        isRatingActive(n) ? 'text-yellow-400' : 'text-gray-300',
                    ]"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
        </div>

        <div class="text-center mt-2">
            <span class="text-sm font-medium" v-if="internalValue">
                {{ internalValue }} / {{ maxRating }}
            </span>
            <span v-else-if="hoveredRating" class="text-sm text-gray-500">
                {{ hoveredRating }} / {{ maxRating }}
            </span>
            <span v-else class="text-sm text-gray-400">
                Klik untuk memberi rating
            </span>
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
import { ref, computed, watch } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        // Allow either number or string
        type: [Number, String],
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// Get max rating from question settings or default to 5
const maxRating = computed(() => {
    // Ensure maxRating is a number between 3 and 10
    const max = props.question.maxRating || 5;
    return typeof max === "number" ? max : Number(max) || 5;
});

// Internal state
const internalValue = ref(props.modelValue ? Number(props.modelValue) : 0);
const hoveredRating = ref(0);

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        internalValue.value = newVal ? Number(newVal) : 0;
    }
);

// Check if a star position should be active
const isRatingActive = (position) => {
    if (hoveredRating.value) {
        return hoveredRating.value >= position;
    }

    return internalValue.value >= position;
};

const selectRating = (value) => {
    internalValue.value = value;
    // Always emit as string to be consistent
    emit("update:modelValue", value.toString());
};

const showHover = (rating) => {
    hoveredRating.value = rating;
};

const clearHover = () => {
    hoveredRating.value = 0;
};
</script>

<style scoped>
.rating-star {
    display: inline-flex;
}

.rating-star:hover {
    transform: scale(1.1);
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
