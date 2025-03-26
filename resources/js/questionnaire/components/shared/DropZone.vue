<template>
    <div
        :class="[
            'drop-zone transition-all duration-200',
            { 'drop-zone-active': isOver && isValidTarget },
            { 'drop-zone-invalid': isOver && !isValidTarget },
            zoneClass,
        ]"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
    >
        <slot :is-over="isOver" :is-valid-target="isValidTarget"></slot>
    </div>
</template>

<script setup>
import { ref, defineEmits, defineProps } from "vue";

const props = defineProps({
    targetType: {
        type: String,
        required: true,
    },
    targetId: {
        type: String,
        default: null,
    },
    acceptTypes: {
        type: Array,
        default: () => [],
    },
    zoneClass: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["drop", "dragover", "dragleave"]);

const isOver = ref(false);
const isValidTarget = ref(false);

const onDragOver = (event) => {
    event.preventDefault();

    if (!isOver.value) {
        isOver.value = true;

        // Cek apakah tipe sumber valid
        try {
            const data = JSON.parse(
                event.dataTransfer.getData("application/json") || "{}"
            );
            const sourceType = data.sourceType;

            // Validasi: jika acceptTypes kosong, terima semua. Jika tidak, cek apakah tipe diterima
            isValidTarget.value =
                props.acceptTypes.length === 0 ||
                props.acceptTypes.includes(sourceType);

            emit("dragover", {
                isValid: isValidTarget.value,
                sourceType,
                targetType: props.targetType,
                targetId: props.targetId,
            });
        } catch (error) {
            isValidTarget.value = false;
        }
    }

    // Set drop effect berdasarkan validitas
    event.dataTransfer.dropEffect = isValidTarget.value ? "move" : "none";
};

const onDragLeave = (event) => {
    // Pastikan kita tidak trigger dragleave saat berpindah ke element anak
    if (!event.currentTarget.contains(event.relatedTarget)) {
        isOver.value = false;
        isValidTarget.value = false;
        emit("dragleave");
    }
};

const onDrop = (event) => {
    event.preventDefault();
    isOver.value = false;

    try {
        const data = JSON.parse(
            event.dataTransfer.getData("application/json") || "{}"
        );

        if (data && data.item) {
            emit("drop", {
                item: data.item,
                sourceType: data.sourceType,
                targetType: props.targetType,
                targetId: props.targetId,
            });
        }
    } catch (error) {
        console.error("Error parsing drop data:", error);
    }
};
</script>

<style scoped>
/* Menggunakan CSS kustom secara terlokalisasi pada komponen ini saja */
.drop-zone-active {
    background-color: #eef2ff; /* Warna yang setara dengan bg-indigo-50 */
    border: 2px dashed #a5b4fc; /* Warna yang setara dengan border-indigo-300 */
}

.drop-zone-invalid {
    background-color: #fee2e2; /* Warna yang setara dengan bg-red-50 */
    border: 2px dashed #fecaca; /* Warna yang setara dengan border-red-300 */
}

/* Menambahkan class-class lain yang mungkin dibutuhkan */
.transition-all {
    transition-property: all;
}

.duration-200 {
    transition-duration: 200ms;
}
</style>
