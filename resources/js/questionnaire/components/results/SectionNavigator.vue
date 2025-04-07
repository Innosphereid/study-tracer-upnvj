<!-- 
* @component SectionNavigator
* @description Komponen untuk navigasi antar section kuesioner, menampilkan tab horizontal yang
* dapat di-scroll dan dropdown untuk navigasi cepat.
-->
<template>
    <div class="section-navigator bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-base font-medium text-gray-900">Sections</h3>

            <!-- Jump to dropdown for mobile -->
            <div class="md:hidden relative" x-data="{ open: false }">
                <button
                    @click="
                        $event.preventDefault();
                        $refs.jumpToMenu.classList.toggle('hidden');
                    "
                    class="flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500"
                >
                    Jump to section
                    <svg
                        class="ml-1 h-4 w-4 text-gray-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>

                <div
                    ref="jumpToMenu"
                    class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
                >
                    <div class="py-1 max-h-60 overflow-y-auto">
                        <a
                            v-for="(section, index) in sections"
                            :key="'dropdown-' + section.id"
                            @click="onSectionSelected(index)"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                            :class="{
                                'bg-blue-50': currentSectionIndex === index,
                            }"
                        >
                            <span>{{ section.title }}</span>
                            <span
                                class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                            >
                                {{
                                    section.questions
                                        ? section.questions.length
                                        : 0
                                }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable tabs -->
        <div class="relative">
            <!-- Shadow indicators for scroll -->
            <div
                class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"
                ref="leftShadow"
                :class="{ 'opacity-0': !showLeftShadow }"
            ></div>
            <div
                class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"
                ref="rightShadow"
                :class="{ 'opacity-0': !showRightShadow }"
            ></div>

            <!-- Scroll buttons -->
            <button
                v-if="showScrollButtons"
                @click="scrollLeft"
                class="absolute left-0 top-1/2 transform -translate-y-1/2 z-20 w-7 h-7 flex items-center justify-center bg-white rounded-full shadow-md text-gray-500 hover:text-gray-700"
                :class="{ 'opacity-0': !showLeftShadow }"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
            </button>

            <button
                v-if="showScrollButtons"
                @click="scrollRight"
                class="absolute right-0 top-1/2 transform -translate-y-1/2 z-20 w-7 h-7 flex items-center justify-center bg-white rounded-full shadow-md text-gray-500 hover:text-gray-700"
                :class="{ 'opacity-0': !showRightShadow }"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </button>

            <!-- Scrollable tabs container -->
            <div
                ref="tabsContainer"
                class="tabs-container overflow-x-auto scrollbar-hide"
                @scroll="handleScroll"
            >
                <div class="inline-flex space-x-2 py-1 px-1">
                    <button
                        v-for="(section, index) in sections"
                        :key="section.id"
                        @click="onSectionSelected(index)"
                        class="tab whitespace-nowrap px-4 py-2 rounded-md text-sm inline-flex items-center transition-colors"
                        :class="[
                            currentSectionIndex === index
                                ? 'bg-blue-500 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                    >
                        <span>{{ section.title }}</span>
                        <span
                            class="ml-1.5 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium rounded-full"
                            :class="
                                currentSectionIndex === index
                                    ? 'bg-blue-400 text-white'
                                    : 'bg-gray-200 text-gray-700'
                            "
                        >
                            {{
                                section.questions ? section.questions.length : 0
                            }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch, computed } from "vue";

export default {
    name: "SectionNavigator",

    props: {
        sections: {
            type: Array,
            required: true,
            default: () => [],
        },
        currentSection: {
            type: Object,
            required: false,
            default: null,
        },
    },

    emits: ["change-section"],

    setup(props, { emit }) {
        const tabsContainer = ref(null);
        const leftShadow = ref(null);
        const rightShadow = ref(null);
        const showLeftShadow = ref(false);
        const showRightShadow = ref(true);
        const showScrollButtons = ref(false);

        // Computed property to find current section index
        const currentSectionIndex = computed(() => {
            if (!props.currentSection) return 0;
            return props.sections.findIndex(
                (section) => section.id === props.currentSection.id
            );
        });

        // Methods
        const onSectionSelected = (index) => {
            if (index >= 0 && index < props.sections.length) {
                emit("change-section", index);
                scrollTabIntoView(index);

                // If using a dropdown, close it after selection
                if (this.$refs && this.$refs.jumpToMenu) {
                    this.$refs.jumpToMenu.classList.add("hidden");
                }
            }
        };

        const handleScroll = () => {
            if (!tabsContainer.value) return;

            const { scrollLeft, scrollWidth, clientWidth } =
                tabsContainer.value;

            // Show/hide shadows based on scroll position
            showLeftShadow.value = scrollLeft > 0;
            showRightShadow.value = scrollLeft < scrollWidth - clientWidth - 5; // 5px tolerance
        };

        const scrollTabIntoView = (index) => {
            if (!tabsContainer.value) return;

            const tabs = tabsContainer.value.querySelectorAll(".tab");
            if (tabs.length > index) {
                const tab = tabs[index];
                const containerWidth = tabsContainer.value.offsetWidth;
                const scrollLeft = tabsContainer.value.scrollLeft;
                const tabLeft = tab.offsetLeft;
                const tabWidth = tab.offsetWidth;

                // Check if tab is not fully visible
                if (
                    tabLeft < scrollLeft ||
                    tabLeft + tabWidth > scrollLeft + containerWidth
                ) {
                    // Center the tab
                    tabsContainer.value.scrollTo({
                        left: tabLeft - containerWidth / 2 + tabWidth / 2,
                        behavior: "smooth",
                    });
                }
            }
        };

        const scrollLeft = () => {
            if (!tabsContainer.value) return;

            tabsContainer.value.scrollBy({
                left: -200,
                behavior: "smooth",
            });
        };

        const scrollRight = () => {
            if (!tabsContainer.value) return;

            tabsContainer.value.scrollBy({
                left: 200,
                behavior: "smooth",
            });
        };

        const checkScrollStatus = () => {
            // Only show scroll buttons if content is wider than container
            if (tabsContainer.value) {
                showScrollButtons.value =
                    tabsContainer.value.scrollWidth >
                    tabsContainer.value.clientWidth;
                handleScroll(); // Update shadows
            }
        };

        // Lifecycle hooks and watchers
        onMounted(() => {
            checkScrollStatus();
            window.addEventListener("resize", checkScrollStatus);

            // Initial scroll to selected section
            if (currentSectionIndex.value >= 0) {
                scrollTabIntoView(currentSectionIndex.value);
            }
        });

        watch(
            () => props.sections,
            () => {
                // Recheck scroll status when sections change
                setTimeout(checkScrollStatus, 100);
            },
            { deep: true }
        );

        watch(currentSectionIndex, (newIndex) => {
            if (newIndex >= 0) {
                scrollTabIntoView(newIndex);
            }
        });

        return {
            tabsContainer,
            leftShadow,
            rightShadow,
            showLeftShadow,
            showRightShadow,
            showScrollButtons,
            currentSectionIndex,
            onSectionSelected,
            handleScroll,
            scrollLeft,
            scrollRight,
        };
    },
};
</script>

<style scoped>
.tabs-container {
    scrollbar-width: none; /* For Firefox */
    -ms-overflow-style: none; /* For IE and Edge */
}

.tabs-container::-webkit-scrollbar {
    display: none; /* For Chrome, Safari, and Opera */
}

.tab {
    user-select: none;
    transition: all 0.2s ease;
}

.tab:hover {
    transform: translateY(-1px);
}

.tab:active {
    transform: translateY(0);
}
</style>
