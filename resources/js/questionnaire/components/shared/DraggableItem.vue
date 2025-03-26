<template>
    <div
        :class="[
            'draggable-item transition-all duration-200',
            { 'opacity-50': isDragging },
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

    // Tambahkan data ke event drag
    event.dataTransfer.effectAllowed = "move";

    try {
        // Format data untuk transfer
        const transferData = {
            item: props.item,
            sourceType: props.sourceType,
        };

        event.dataTransfer.setData(
            "application/json",
            JSON.stringify(transferData)
        );
    } catch (error) {
        console.error("Error setting drag data:", error);
    }

    // Opsi: tambahkan ghost image kustom
    // const dragImage = document.createElement('div');
    // dragImage.textContent = props.item.name || 'Item';
    // dragImage.className = 'bg-indigo-100 text-indigo-800 p-2 rounded shadow-md';
    // document.body.appendChild(dragImage);
    // event.dataTransfer.setDragImage(dragImage, 0, 0);
    // setTimeout(() => document.body.removeChild(dragImage), 0);

    emit("dragstart", { item: props.item, sourceType: props.sourceType });
};

const onDragEnd = () => {
    emit("dragend");
};
</script>
