<template>
    <div
        class="component-sidebar bg-white border-r border-gray-200 w-64 flex flex-col h-full"
    >
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                Komponen Kuesioner
            </h2>
            <p class="mt-1 text-sm text-gray-500">Drag komponen ke builder</p>
        </div>

        <div class="flex border-b border-gray-200">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                class="flex-1 py-2 px-3 text-sm font-medium text-center focus:outline-none transition-colors"
                :class="{
                    'text-indigo-600 border-b-2 border-indigo-500':
                        activeTab === tab.id,
                    'text-gray-500 hover:text-indigo-600': activeTab !== tab.id,
                }"
                @click="activeTab = tab.id"
            >
                {{ tab.name }}
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-2">
            <div class="space-y-2">
                <div
                    v-for="component in filteredComponents"
                    :key="component.id"
                    class="component-item"
                >
                    <DraggableItem
                        :item="component"
                        source-type="component"
                        :is-dragging="isDragging"
                        @dragstart="onDragStart"
                        @dragend="onDragEnd"
                    >
                        <div
                            class="p-3 bg-white rounded-md border border-gray-200 hover:border-indigo-300 hover:shadow-sm transition-all cursor-grab"
                        >
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-8 w-8 flex items-center justify-center bg-indigo-100 rounded-md text-indigo-700"
                                >
                                    <component-icon
                                        :type="component.id"
                                        class="h-5 w-5"
                                    />
                                </div>
                                <div class="ml-3">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ component.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </DraggableItem>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from "vue";
import { useQuestionnaireStore } from "../../store/questionnaire";
import DraggableItem from "../shared/DraggableItem.vue";
import ComponentIcon from "./ComponentIcon.vue";

const store = useQuestionnaireStore();

const props = defineProps({
    isDragging: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["dragstart", "dragend"]);

const tabs = [
    { id: "all", name: "Semua" },
    { id: "dasar", name: "Dasar" },
    { id: "pilihan", name: "Pilihan" },
    { id: "lanjutan", name: "Lanjutan" },
];

const activeTab = ref("all");

const filteredComponents = computed(() => {
    const components = store.questionTypes;

    if (activeTab.value === "all") {
        return components;
    }

    return components.filter(
        (component) => component.category === activeTab.value
    );
});

const onDragStart = (event) => {
    emit("dragstart", event);
};

const onDragEnd = () => {
    emit("dragend");
};
</script>

<style scoped>
.component-sidebar {
    min-width: 16rem;
}

.component-item {
    transition: transform 0.15s ease-in-out;
}

.component-item:active {
    transform: scale(0.98);
}
</style>
