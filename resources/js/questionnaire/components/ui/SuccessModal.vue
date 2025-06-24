<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
    >
        <!-- Backdrop with blur effect -->
        <div
            class="fixed inset-0 bg-black/30 backdrop-blur-sm"
            @click="onClose"
        ></div>

        <!-- Modal Content -->
        <div
            class="bg-white rounded-lg shadow-xl w-full max-w-md relative transform transition-all duration-300 ease-in-out"
            :class="{
                'translate-y-0 opacity-100': show,
                'translate-y-4 opacity-0': !show,
            }"
        >
            <!-- Success Icon -->
            <div class="absolute -top-14 left-1/2 transform -translate-x-1/2">
                <div class="bg-green-500 rounded-full p-3 shadow-lg">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-10 w-10 text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-8 pt-14 text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-1">
                    Kuesioner Berhasil Diterbitkan!
                </h3>
                <p class="text-gray-600 mb-6">
                    Kuesioner Anda sekarang tersedia untuk diisi oleh responden.
                </p>

                <!-- Link Container -->
                <div class="bg-gray-50 p-4 rounded-md mb-6 text-left">
                    <p class="text-sm font-medium text-gray-700 mb-2">
                        Link kuesioner:
                    </p>
                    <div class="flex items-center">
                        <input
                            type="text"
                            readonly
                            :value="questionnaireUrl"
                            class="flex-1 p-2 text-sm border rounded-l-md focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white"
                            ref="urlInput"
                        />
                        <button
                            @click="copyToClipboard"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-r-md transition-colors duration-200"
                            :class="{
                                'bg-green-500 hover:bg-green-600': copied,
                            }"
                        >
                            <svg
                                v-if="!copied"
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
                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"
                                />
                            </svg>
                            <svg
                                v-else
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
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </button>
                    </div>
                    <p v-if="copied" class="text-xs text-green-600 mt-1">
                        Link berhasil disalin!
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4">
                    <button
                        @click="goToDetail"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Lihat Detail
                    </button>
                    <button
                        @click="goToList"
                        class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                    >
                        Kembali ke Daftar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from "vue";

const props = defineProps({
    show: Boolean,
    questionnaireUrl: {
        type: String,
        required: true,
    },
    questionnaireId: {
        type: [String, Number],
        required: true,
    },
});

const emit = defineEmits(["close", "go-to-detail", "go-to-list"]);

const copied = ref(false);
const urlInput = ref(null);

// Reset copied state when modal shows
watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            copied.value = false;
        }
    }
);

const copyToClipboard = async () => {
    if (!navigator.clipboard) {
        // Fallback for browsers that don't support clipboard API
        urlInput.value.select();
        document.execCommand("copy");
    } else {
        await navigator.clipboard.writeText(props.questionnaireUrl);
    }

    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const onClose = () => {
    emit("close");
};

const goToDetail = () => {
    emit("go-to-detail");
};

const goToList = () => {
    emit("go-to-list");
};
</script>
