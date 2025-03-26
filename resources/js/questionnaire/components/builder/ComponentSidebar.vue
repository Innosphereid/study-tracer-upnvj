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

        <div
            ref="tabContainer"
            class="tab-container overflow-x-auto hide-scrollbar border-b border-gray-200"
        >
            <div class="flex whitespace-nowrap min-w-full">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    :data-tab-id="tab.id"
                    class="py-2 px-4 text-sm font-medium text-center focus:outline-none transition-colors flex-shrink-0"
                    :class="{
                        'text-indigo-600 border-b-2 border-indigo-500':
                            activeTab === tab.id,
                        'text-gray-500 hover:text-indigo-600':
                            activeTab !== tab.id,
                    }"
                    @click="activeTab = tab.id"
                >
                    {{ tab.name }}
                </button>
            </div>
        </div>

        <!-- Slider untuk navigasi horizontal -->
        <div
            class="tab-slider-container px-4 py-1 border-b border-gray-200 flex items-center"
        >
            <button
                class="text-gray-500 hover:text-indigo-600 focus:outline-none px-1"
                @click="scrollTabsByAmount(-80)"
                :disabled="isAtStart"
                :class="{ 'opacity-30 cursor-not-allowed': isAtStart }"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-4 h-4"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 19.5L8.25 12l7.5-7.5"
                    />
                </svg>
            </button>

            <div class="flex-1 mx-2 relative">
                <div class="h-1 bg-gray-200 rounded-full">
                    <div
                        ref="sliderThumb"
                        class="slider-thumb absolute top-1/2 transform -translate-y-1/2 bg-indigo-500 rounded-full cursor-pointer hover:bg-indigo-600"
                        :style="{
                            left: `${sliderPosition}%`,
                            width: `${sliderWidth}%`,
                            height: '8px',
                        }"
                        @mousedown="startDragSlider"
                    ></div>
                </div>
            </div>

            <button
                class="text-gray-500 hover:text-indigo-600 focus:outline-none px-1"
                @click="scrollTabsByAmount(80)"
                :disabled="isAtEnd"
                :class="{ 'opacity-30 cursor-not-allowed': isAtEnd }"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-4 h-4"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8.25 4.5l7.5 7.5-7.5 7.5"
                    />
                </svg>
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
import {
    ref,
    computed,
    defineProps,
    defineEmits,
    onMounted,
    watch,
    onBeforeUnmount,
    provide,
} from "vue";
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
const tabContainer = ref(null);
const tabRefs = ref({});
const sliderThumb = ref(null);
const sliderPosition = ref(0);
const sliderWidth = ref(10); // Width of the slider as percentage
const isDraggingSlider = ref(false);
const isAtStart = ref(true);
const isAtEnd = ref(false);
const isDraggingComponent = ref(false);

// Provide dragging state to be consumed by other components
provide("isDraggingComponent", isDraggingComponent);

// Calculate slider position based on scroll
const updateSliderPosition = () => {
    if (!tabContainer.value) return;

    const container = tabContainer.value;
    const maxScroll = container.scrollWidth - container.clientWidth;

    if (maxScroll <= 0) {
        // No need for slider if content fits
        sliderPosition.value = 0;
        sliderWidth.value = 100;
        isAtStart.value = true;
        isAtEnd.value = true;
        return;
    }

    // Calculate how much of the content is visible
    const visibleRatio = container.clientWidth / container.scrollWidth;
    sliderWidth.value = Math.max(10, visibleRatio * 100); // Min 10% width for thumb

    // Calculate position
    const scrollRatio = container.scrollLeft / maxScroll;
    sliderPosition.value = scrollRatio * (100 - sliderWidth.value);

    // Update button states
    isAtStart.value = container.scrollLeft <= 0;
    isAtEnd.value = Math.abs(container.scrollLeft - maxScroll) < 1;
};

// Function to scroll tabs by a specific amount
const scrollTabsByAmount = (amount) => {
    if (!tabContainer.value) return;

    tabContainer.value.scrollBy({
        left: amount,
        behavior: "smooth",
    });
};

// Slider drag handlers
const startDragSlider = (e) => {
    isDraggingSlider.value = true;
    document.addEventListener("mousemove", handleSliderDrag);
    document.addEventListener("mouseup", endDragSlider);
    e.preventDefault(); // Prevent text selection
};

const handleSliderDrag = (e) => {
    if (!isDraggingSlider.value || !tabContainer.value || !sliderThumb.value)
        return;

    const container = tabContainer.value;
    const maxScroll = container.scrollWidth - container.clientWidth;

    // Get parent element for positioning
    const sliderTrack = sliderThumb.value.parentElement;
    const trackRect = sliderTrack.getBoundingClientRect();

    // Calculate new position in percentage
    let newPosition = ((e.clientX - trackRect.left) / trackRect.width) * 100;

    // Constrain position
    newPosition = Math.max(0, Math.min(newPosition, 100 - sliderWidth.value));

    // Calculate and apply scroll position
    const scrollPosition =
        (newPosition / (100 - sliderWidth.value)) * maxScroll;
    container.scrollLeft = scrollPosition;

    // Update slider position directly
    sliderPosition.value = newPosition;
};

const endDragSlider = () => {
    isDraggingSlider.value = false;
    document.removeEventListener("mousemove", handleSliderDrag);
    document.removeEventListener("mouseup", endDragSlider);
};

// Scroll event handler
const handleTabScroll = () => {
    updateSliderPosition();
};

// Fungsi untuk scroll tab aktif ke dalam view
const scrollTabIntoView = (tabId) => {
    if (!tabContainer.value || !tabRefs.value[tabId]) return;

    const container = tabContainer.value;
    const tabElement = tabRefs.value[tabId];

    // Hitung posisi untuk scroll
    const tabLeft = tabElement.offsetLeft;
    const tabWidth = tabElement.offsetWidth;
    const containerWidth = container.offsetWidth;
    const containerScrollLeft = container.scrollLeft;

    // Cek apakah tab ada di luar viewport
    if (
        tabLeft < containerScrollLeft ||
        tabLeft + tabWidth > containerScrollLeft + containerWidth
    ) {
        // Scroll ke posisi tab dengan efek smooth
        container.scrollTo({
            left: tabLeft - containerWidth / 2 + tabWidth / 2,
            behavior: "smooth",
        });
    }
};

// Watch perubahan tab aktif untuk scroll ke tab tsb
watch(activeTab, (newTabId) => {
    // Jalankan pada next tick untuk memastikan DOM sudah diupdate
    setTimeout(() => {
        scrollTabIntoView(newTabId);
        // Update slider position after scrolling
        updateSliderPosition();
    }, 50);
});

// Setup and cleanup
onMounted(() => {
    // Setup tab refs untuk semua tab
    tabs.forEach((tab) => {
        tabRefs.value[tab.id] = document.querySelector(
            `[data-tab-id="${tab.id}"]`
        );
    });

    if (tabContainer.value) {
        // Add slider-related event listeners
        tabContainer.value.addEventListener("scroll", handleTabScroll);
        window.addEventListener("resize", updateSliderPosition);

        // Initial slider position update
        setTimeout(updateSliderPosition, 100);

        // Touch event listeners for mobile scrolling
        let isDragging = false;
        let startX = 0;
        let scrollLeft = 0;

        tabContainer.value.addEventListener(
            "touchstart",
            (e) => {
                isDragging = true;
                startX = e.touches[0].pageX - tabContainer.value.offsetLeft;
                scrollLeft = tabContainer.value.scrollLeft;

                // Update slider position when touch starts
                requestAnimationFrame(updateSliderPosition);
            },
            { passive: true }
        );

        tabContainer.value.addEventListener(
            "touchmove",
            (e) => {
                if (!isDragging) return;
                const x = e.touches[0].pageX - tabContainer.value.offsetLeft;
                const walk = x - startX;
                tabContainer.value.scrollLeft = scrollLeft - walk;

                // Update slider position during touch move
                requestAnimationFrame(updateSliderPosition);
            },
            { passive: true }
        );

        tabContainer.value.addEventListener(
            "touchend",
            () => {
                isDragging = false;

                // Final update slider position when touch ends
                requestAnimationFrame(updateSliderPosition);
            },
            { passive: true }
        );

        // Mouse wheel event for horizontal scrolling with mouse wheel
        tabContainer.value.addEventListener(
            "wheel",
            (e) => {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    tabContainer.value.scrollLeft += e.deltaY;
                    requestAnimationFrame(updateSliderPosition);
                }
            },
            { passive: false }
        );
    }
});

onBeforeUnmount(() => {
    // Clean up event listeners
    if (tabContainer.value) {
        tabContainer.value.removeEventListener("scroll", handleTabScroll);
    }
    window.removeEventListener("resize", updateSliderPosition);
    document.removeEventListener("mousemove", handleSliderDrag);
    document.removeEventListener("mouseup", endDragSlider);
});

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
    isDraggingComponent.value = true;
    emit("dragstart", event);
};

const onDragEnd = () => {
    isDraggingComponent.value = false;
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

.tab-container {
    position: relative;
    scrollbar-width: none; /* Firefox */
}

.tab-container::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

.hide-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
}

/* Tambahkan visual hint untuk scrolling pada mobile */
.tab-container::after {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 20px;
    background: linear-gradient(
        to right,
        rgba(255, 255, 255, 0),
        rgba(255, 255, 255, 1)
    );
    pointer-events: none;
    opacity: 0.8;
}

/* Slider styling */
.tab-slider-container {
    user-select: none;
}

.slider-thumb {
    transition: background-color 0.2s, height 0.2s;
    min-width: 20px;
}

.slider-thumb:hover {
    height: 10px !important;
}

.slider-thumb:active {
    height: 10px !important;
    background-color: rgb(79, 70, 229) !important; /* indigo-600 */
}
</style>
