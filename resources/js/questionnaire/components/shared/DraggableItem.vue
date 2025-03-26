<template>
    <div
        :class="[
            'draggable-item transition-all duration-200',
            { 'opacity-50 scale-95 shadow-md': isDragging },
            { 'hover:shadow-md hover:translate-y-[-2px]': !disabled },
            { 'cursor-grab': !disabled },
            { 'cursor-not-allowed': disabled },
        ]"
        draggable="true"
        @dragstart="onDragStart"
        @dragend="onDragEnd"
    >
        <slot></slot>
    </div>
</template>

<script setup>
import { defineEmits, defineProps } from "vue";

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    sourceType: {
        type: String,
        required: true,
    },
    isDragging: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["dragstart", "dragend"]);

const onDragStart = (event) => {
    if (props.disabled) {
        event.preventDefault();
        return;
    }

    // Set the drag image and offset
    const rect = event.target.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    // Set the drag data
    const dragData = {
        item: props.item,
        sourceType: props.sourceType,
        sourceIndex: props.item.index || -1,
    };

    // Serialize to string to allow transfer between components
    const dataString = JSON.stringify(dragData);
    event.dataTransfer.setData("application/json", dataString);
    event.dataTransfer.setData("text/plain", dataString);
    event.dataTransfer.effectAllowed = "move";

    // Trigger the drag event in parent component
    emit("dragstart", dragData);
};

const onDragEnd = (event) => {
    emit("dragend");
};
</script>

<style scoped>
.draggable-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    will-change: transform, box-shadow, opacity;
    transform-origin: center center;
}

.draggable-item:active {
    transform: scale(0.97);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}
</style>
