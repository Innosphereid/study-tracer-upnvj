<template>
    <div class="ranking-question">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <p v-if="sortedItems.length" class="text-sm text-gray-500 mb-3">
                Seret dan tata item untuk membuat peringkat dari yang paling
                penting hingga kurang penting
            </p>
            <ul class="space-y-3">
                <draggable
                    v-model="sortedItems"
                    ghost-class="ghost"
                    item-key="id"
                    :animation="200"
                    @start="dragging = true"
                    @end="dragging = false"
                >
                    <template #item="{ element, index }">
                        <li
                            class="cursor-move p-0 bg-white border border-gray-200 rounded-lg shadow-sm transition-all duration-200 overflow-hidden hover:border-indigo-300"
                            :class="{ 'shadow-md border-indigo-500': dragging }"
                        >
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 bg-indigo-100 text-indigo-800 font-bold px-3 py-3 text-center flex flex-col justify-center items-center"
                                    style="width: 50px"
                                >
                                    <span class="text-xl">{{ index + 1 }}</span>
                                </div>
                                <div class="flex-1 p-3 pl-4">
                                    <div class="font-medium">
                                        {{ element.text }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0 px-3 text-gray-400">
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
                                            d="M4 6h16M4 12h16m-7 6h7"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </li>
                    </template>
                </draggable>
            </ul>

            <div
                v-if="!sortedItems.length"
                class="text-center py-4 text-gray-500"
            >
                <p>No ranking options available</p>
            </div>
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
import draggable from "vuedraggable";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Object,
        default: () => ({ order: [] }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// Local state
const dragging = ref(false);

// Initialize sortedItems from question or from existing modelValue
const initializeSortedItems = () => {
    if (props.modelValue?.order?.length) {
        // If we have existing order, apply it
        return mapOrderToItems();
    } else {
        // Otherwise use the original options from the question
        return props.question.options ? [...props.question.options] : [];
    }
};

// Map the saved order back to items
const mapOrderToItems = () => {
    const order = props.modelValue.order;
    const options = props.question.options || [];

    // Create a map of id -> option for quick lookup
    const optionsMap = new Map(
        options.map((option) => [
            option.id || option.value || option.text,
            option,
        ])
    );

    // Create sorted array based on the saved order
    return order.map((id) => {
        return optionsMap.get(id) || { id, text: `Option ${id}` };
    });
};

// Computed property for the sorted items
const sortedItems = computed({
    get() {
        return initializeSortedItems();
    },
    set(newItems) {
        // Extract IDs from the sorted items
        const newOrder = newItems.map(
            (item) => item.id || item.value || item.text
        );
        emit("update:modelValue", { order: newOrder });
    },
});

// Watch for external changes to modelValue
watch(
    () => props.modelValue?.order,
    () => {
        // Re-initialize if the external data changes
        initializeSortedItems();
    },
    { deep: true }
);

// Watch for changes to the question options
watch(
    () => props.question.options,
    () => {
        // Re-initialize if question options change
        initializeSortedItems();
    },
    { deep: true }
);
</script>

<style scoped>
.ranking-question {
    user-select: none;
}

.ghost {
    opacity: 0.7;
    background: #f0f4ff;
    border: 2px dashed #6366f1 !important;
    box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.1),
        0 2px 4px -1px rgba(99, 102, 241, 0.06);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Add subtle animation when sorting */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(99, 102, 241, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
    }
}

.ranking-question li:active {
    animation: pulse 1.5s infinite;
    background-color: #f9fafb;
}
</style>
