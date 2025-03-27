<template>
    <div class="ranking-question">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <ul class="space-y-2">
                <draggable
                    v-model="sortedItems"
                    ghost-class="ghost"
                    handle=".drag-handle"
                    item-key="id"
                    :animation="200"
                    @start="dragging = true"
                    @end="dragging = false"
                >
                    <template #item="{ element, index }">
                        <li
                            class="flex items-center p-3 bg-white border border-gray-200 rounded-lg shadow-sm transition-all duration-200"
                            :class="{ 'shadow-md': dragging }"
                        >
                            <div
                                class="drag-handle cursor-move flex items-center justify-center w-10 mr-2 text-gray-500"
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
                                        d="M4 6h16M4 12h16m-7 6h7"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">
                                    {{ index + 1 }}. {{ element.text }}
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
    opacity: 0.5;
    background: #f3f4f6;
    border: 1px dashed #d1d5db;
}

.drag-handle:hover {
    color: #4f46e5;
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
