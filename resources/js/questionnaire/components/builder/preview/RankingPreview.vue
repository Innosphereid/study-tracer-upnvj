<template>
    <div class="mt-2">
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
            <!-- Ranking explanation -->
            <div class="mb-3 pb-2 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-medium text-gray-500">
                        Urutan peringkat
                    </div>
                    <div
                        class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded-full flex items-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-3 w-3 mr-1"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Drag & drop aktif saat pengisian
                    </div>
                </div>
            </div>

            <!-- Peringatan tidak ada opsi -->
            <div
                v-if="hasNoOptions"
                class="py-6 flex flex-col items-center justify-center"
            >
                <div
                    class="w-16 h-16 mb-3 bg-red-50 rounded-full flex items-center justify-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-red-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                </div>
                <p class="text-sm font-medium text-red-600 mb-1">
                    Tidak ada opsi peringkat!
                </p>
                <p class="text-xs text-center text-gray-500 max-w-xs">
                    Pertanyaan ranking membutuhkan minimal 2 opsi untuk
                    diurutkan. Tambahkan opsi pada panel pengaturan.
                </p>

                <!-- Tombol yang bisa diklik untuk menambahkan opsi -->
                <div class="mt-4 animate-pulse">
                    <button
                        type="button"
                        @click="addOptions"
                        class="px-4 py-2 bg-red-100 text-red-700 rounded-md text-sm font-medium border border-red-200 flex items-center hover:bg-red-200 transition-colors"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        Tambah Opsi Peringkat
                    </button>
                </div>
            </div>

            <!-- Ranking items list with connecting lines -->
            <div v-else class="space-y-0">
                <div
                    v-for="(option, index) in displayOptions"
                    :key="index"
                    class="relative"
                >
                    <!-- Connecting line between items except for the last one -->
                    <div
                        v-if="index < displayOptions.length - 1"
                        class="absolute left-4 top-10 h-4 w-0 border-l-2 border-dashed border-gray-300 z-0"
                    ></div>

                    <!-- Item container with relative z-index to be above connector line -->
                    <div
                        class="flex items-center bg-white border border-gray-200 rounded-md p-2 shadow-sm transition-colors relative z-10"
                        :class="
                            index === 0 ? 'bg-indigo-50 border-indigo-200' : ''
                        "
                    >
                        <!-- Rank Number Badge with custom colors based on rank -->
                        <div
                            class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full font-bold mr-3 transition-colors"
                            :class="[
                                index === 0
                                    ? 'bg-indigo-100 text-indigo-800'
                                    : index === 1
                                    ? 'bg-blue-100 text-blue-800'
                                    : index === 2
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-gray-100 text-gray-800',
                            ]"
                        >
                            {{ index + 1 }}
                        </div>

                        <!-- Item Content -->
                        <div class="flex-grow">
                            <span class="text-sm font-medium text-gray-700">{{
                                option.text
                            }}</span>
                        </div>

                        <!-- Drag Handle Indicator with visual enhancements -->
                        <div
                            class="flex-shrink-0 px-2 py-1 text-gray-400 cursor-not-allowed rounded border border-transparent hover:border-gray-200 hover:bg-gray-50"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 8h16M4 16h16"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Indicator for more items if needed -->
                <div
                    v-if="question.options && question.options.length > 5"
                    class="text-center text-xs text-gray-500 pt-3 mt-2"
                >
                    <span class="flex items-center justify-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                        {{ question.options.length - 5 }} item lainnya
                    </span>
                </div>
            </div>

            <!-- Help text with detailed explanation -->
            <div
                v-if="!hasNoOptions"
                class="mt-4 flex items-center text-xs text-gray-500"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <span>
                    Responden diminta untuk mengurutkan
                    {{ displayOptions.length }}
                    pilihan sesuai prioritas mereka
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, computed, ref, onMounted } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emits = defineEmits(["add-options"]);

// Computed property untuk menampilkan opsi yang sesuai
const displayOptions = computed(() => {
    // Hanya gunakan opsi dari pertanyaan jika ada dan tidak kosong
    if (props.question.options && props.question.options.length > 0) {
        return props.question.options.slice(0, 5);
    }
    // Return array kosong jika tidak ada opsi
    return [];
});

// Apakah tidak ada opsi
const hasNoOptions = computed(() => {
    return !props.question.options || props.question.options.length === 0;
});

// Fungsi untuk menambahkan opsi
function addOptions() {
    // Emit event dengan parameter count=2 untuk menambahkan 2 opsi sekaligus
    emits("add-options", 2);
}
</script>
