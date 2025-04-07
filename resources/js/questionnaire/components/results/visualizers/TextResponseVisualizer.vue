<!-- 
* @component TextResponseVisualizer
* @description Visualizer untuk pertanyaan jenis teks (short-text, long-text).
* Menampilkan statistik teks dan sampel jawaban.
-->
<template>
    <div class="text-response-visualizer" :data-question-id="question.id">
        <!-- Text Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-item bg-gray-50 p-3 rounded-lg">
                <span class="block text-sm text-gray-500 mb-1"
                    >Total responses</span
                >
                <span class="block text-xl font-semibold text-gray-800">{{
                    responses.length
                }}</span>
            </div>

            <div class="stat-item bg-gray-50 p-3 rounded-lg">
                <span class="block text-sm text-gray-500 mb-1"
                    >Unique responses</span
                >
                <span class="block text-xl font-semibold text-gray-800">{{
                    uniqueResponsesCount
                }}</span>
            </div>

            <div class="stat-item bg-gray-50 p-3 rounded-lg">
                <span class="block text-sm text-gray-500 mb-1"
                    >Average length</span
                >
                <span class="block text-xl font-semibold text-gray-800">{{
                    averageLength
                }}</span>
            </div>

            <div class="stat-item bg-gray-50 p-3 rounded-lg">
                <span class="block text-sm text-gray-500 mb-1"
                    >Most common words</span
                >
                <div
                    v-if="commonWords.length"
                    class="flex flex-wrap gap-1 mt-1"
                >
                    <span
                        v-for="(word, i) in commonWords.slice(0, 3)"
                        :key="i"
                        class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full"
                    >
                        {{ word.word }}
                    </span>
                </div>
                <span v-else class="text-sm text-gray-500 italic"
                    >None found</span
                >
            </div>
        </div>

        <!-- View Type Toggle -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-2">
                <button
                    @click="viewType = 'list'"
                    :class="[
                        'px-2 py-1 text-xs rounded',
                        viewType === 'list'
                            ? 'bg-blue-100 text-blue-800 font-medium'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    List View
                </button>
                <button
                    @click="viewType = 'word-cloud'"
                    :class="[
                        'px-2 py-1 text-xs rounded',
                        viewType === 'word-cloud'
                            ? 'bg-blue-100 text-blue-800 font-medium'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                    ]"
                >
                    Word Cloud
                </button>
            </div>

            <!-- Search Filter -->
            <div>
                <input
                    type="text"
                    v-model="searchFilter"
                    placeholder="Search responses..."
                    class="px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
            </div>
        </div>

        <!-- List View -->
        <div v-if="viewType === 'list'" class="list-view">
            <div
                v-if="filteredResponses.length === 0"
                class="text-center py-8 text-gray-500"
            >
                No responses match your search.
            </div>

            <div v-else class="border rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b">
                    <div class="grid grid-cols-12 gap-4">
                        <div
                            class="col-span-2 text-xs font-medium text-gray-500"
                        >
                            Response #
                        </div>
                        <div
                            class="col-span-8 text-xs font-medium text-gray-500"
                        >
                            Response
                        </div>
                        <div
                            class="col-span-2 text-xs font-medium text-gray-500"
                        >
                            Date
                        </div>
                    </div>
                </div>

                <div class="divide-y max-h-96 overflow-y-auto">
                    <div
                        v-for="(response, index) in filteredResponses"
                        :key="response.id || index"
                        class="grid grid-cols-12 gap-4 px-4 py-3 hover:bg-gray-50"
                    >
                        <div class="col-span-2 text-sm text-gray-500">
                            {{ response.response_id || `#${index + 1}` }}
                        </div>
                        <div class="col-span-8 text-sm whitespace-pre-line">
                            {{ truncate(response.answer_value, 200) }}
                        </div>
                        <div class="col-span-2 text-sm text-gray-500">
                            {{ formatDate(response.created_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Word Cloud View -->
        <div v-else-if="viewType === 'word-cloud'" class="word-cloud-view">
            <div
                v-if="wordCloudData.length === 0"
                class="text-center py-8 text-gray-500"
            >
                Not enough text data to generate a word cloud.
            </div>

            <div
                v-else
                class="bg-gray-50 rounded-lg p-4 min-h-[300px] flex items-center justify-center"
            >
                <div class="word-cloud-container relative h-64 w-full">
                    <div
                        v-for="(word, index) in wordCloudData"
                        :key="index"
                        class="absolute text-center select-none"
                        :style="{
                            fontSize: `${word.size}px`,
                            color: word.color,
                            left: `${word.x}%`,
                            top: `${word.y}%`,
                            transform: 'translate(-50%, -50%)',
                        }"
                    >
                        {{ word.word }}
                    </div>
                </div>
            </div>

            <!-- Common words list -->
            <div class="mt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">
                    Most common words
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div
                        v-for="(word, index) in commonWords.slice(0, 12)"
                        :key="index"
                        class="flex justify-between bg-white p-2 rounded border"
                    >
                        <span class="text-sm font-medium">{{ word.word }}</span>
                        <span class="text-xs text-gray-500">{{
                            word.count
                        }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";

export default {
    name: "TextResponseVisualizer",

    props: {
        question: {
            type: Object,
            required: true,
        },
        responses: {
            type: Array,
            required: true,
            default: () => [],
        },
    },

    emits: ["loading"],

    setup(props, { emit }) {
        // State
        const viewType = ref("list");
        const searchFilter = ref("");

        // Emit initial loading state
        emit("loading", true);

        // Computed properties
        const validResponses = computed(() => {
            return props.responses.filter(
                (r) => r.answer_value && r.answer_value.trim() !== ""
            );
        });

        const uniqueResponsesCount = computed(() => {
            const uniqueSet = new Set();
            validResponses.value.forEach((r) => {
                uniqueSet.add(r.answer_value.toLowerCase().trim());
            });
            return uniqueSet.size;
        });

        const averageLength = computed(() => {
            if (validResponses.value.length === 0) return "0 chars";

            const totalLength = validResponses.value.reduce(
                (acc, r) => acc + r.answer_value.length,
                0
            );
            const avg = Math.round(totalLength / validResponses.value.length);

            if (avg > 100) {
                return `${Math.round(avg / 10) / 10} words`;
            }
            return `${avg} chars`;
        });

        const filteredResponses = computed(() => {
            if (!searchFilter.value.trim()) {
                return validResponses.value;
            }

            const search = searchFilter.value.toLowerCase();
            return validResponses.value.filter((r) =>
                r.answer_value.toLowerCase().includes(search)
            );
        });

        const wordFrequency = computed(() => {
            // Don't process if we have too few responses
            if (validResponses.value.length < 3) {
                return {};
            }

            const stopWords = getStopWords();
            const wordCounts = {};

            // Process all responses
            validResponses.value.forEach((r) => {
                // Extract all words, convert to lowercase, remove non-word chars
                const words = r.answer_value
                    .toLowerCase()
                    .replace(/[^\w\s]/g, " ")
                    .split(/\s+/)
                    .filter((w) => w.length > 2 && !stopWords.includes(w));

                // Count words
                words.forEach((word) => {
                    wordCounts[word] = (wordCounts[word] || 0) + 1;
                });
            });

            return wordCounts;
        });

        const commonWords = computed(() => {
            const words = Object.entries(wordFrequency.value)
                .map(([word, count]) => ({ word, count }))
                .sort((a, b) => b.count - a.count)
                .slice(0, 50); // Limit to top 50

            return words;
        });

        const wordCloudData = computed(() => {
            if (commonWords.value.length < 5) {
                return [];
            }

            // Get the max frequency for scaling
            const maxFreq = commonWords.value[0].count;
            const minSize = 14;
            const maxSize = 36;

            // Color palette
            const colors = [
                "#3B82F6", // blue
                "#10B981", // green
                "#6366F1", // indigo
                "#F59E0B", // amber
                "#EC4899", // pink
                "#8B5CF6", // purple
            ];

            // Generate positions using a simple algorithm
            const positions = [];
            const data = commonWords.value
                .slice(0, 30)
                .map((wordItem, index) => {
                    // Calculate relative size
                    const size =
                        minSize +
                        (wordItem.count / maxFreq) * (maxSize - minSize);
                    const color = colors[index % colors.length];

                    // Generate a random position that doesn't overlap too much
                    let x, y;
                    let attempts = 0;

                    do {
                        x = 20 + Math.random() * 60; // 20% to 80% of width
                        y = 20 + Math.random() * 60; // 20% to 80% of height
                        attempts++;
                    } while (isOverlapping(x, y, positions) && attempts < 100);

                    // Store position
                    positions.push({ x, y, size });

                    return {
                        word: wordItem.word,
                        count: wordItem.count,
                        size,
                        color,
                        x,
                        y,
                    };
                });

            return data;
        });

        // Helper functions
        const isOverlapping = (x, y, positions) => {
            const minDistance = 15;

            for (const pos of positions) {
                const dx = Math.abs(x - pos.x);
                const dy = Math.abs(y - pos.y);
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < minDistance) {
                    return true;
                }
            }

            return false;
        };

        const getStopWords = () => {
            // Common English stop words
            return [
                "the",
                "and",
                "for",
                "are",
                "but",
                "not",
                "you",
                "all",
                "any",
                "can",
                "had",
                "her",
                "was",
                "one",
                "our",
                "out",
                "day",
                "get",
                "has",
                "him",
                "his",
                "how",
                "man",
                "new",
                "now",
                "old",
                "see",
                "two",
                "way",
                "who",
                "boy",
                "did",
                "its",
                "let",
                "say",
                "she",
                "too",
                "use",
            ];
        };

        const truncate = (text, maxLength) => {
            if (!text) return "";
            if (text.length <= maxLength) return text;

            return text.substring(0, maxLength) + "...";
        };

        const formatDate = (dateString) => {
            if (!dateString) return "N/A";

            try {
                const date = new Date(dateString);
                return date.toLocaleDateString();
            } catch (e) {
                return "Invalid Date";
            }
        };

        // Lifecycle hooks
        onMounted(() => {
            // No longer loading
            emit("loading", false);
        });

        return {
            viewType,
            searchFilter,
            filteredResponses,
            uniqueResponsesCount,
            averageLength,
            wordCloudData,
            commonWords,
            truncate,
            formatDate,
        };
    },
};
</script>

<style scoped>
.word-cloud-container {
    overflow: hidden;
}

.word-cloud-container div {
    cursor: default;
    transition: transform 0.2s ease;
}

.word-cloud-container div:hover {
    transform: translate(-50%, -50%) scale(1.1);
}
</style>
