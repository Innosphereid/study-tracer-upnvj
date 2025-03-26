<template>
    <div
        class="form-header py-4 px-6 bg-white shadow-sm border-b border-gray-200"
    >
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Logo and Title -->
            <div class="flex items-center space-x-4">
                <a href="/" class="flex items-center">
                    <img src="/logo.svg" alt="Logo" class="h-8 w-auto" />
                </a>

                <div v-if="editable">
                    <input
                        type="text"
                        v-model="localTitle"
                        class="text-xl font-medium focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md"
                        @change="updateTitle"
                        placeholder="Judul Kuesioner"
                    />
                </div>
                <h1 v-else class="text-xl font-medium text-gray-900">
                    {{ title }}
                </h1>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-3">
                <slot name="actions"></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from "vue";

const props = defineProps({
    title: {
        type: String,
        default: "Kuesioner TraceStudy UPNVJ",
    },
    editable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:title"]);

const localTitle = ref(props.title);

watch(
    () => props.title,
    (newTitle) => {
        localTitle.value = newTitle;
    }
);

const updateTitle = () => {
    emit("update:title", localTitle.value);
};
</script>
