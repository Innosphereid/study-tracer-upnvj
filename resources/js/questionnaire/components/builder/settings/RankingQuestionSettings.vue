<template>
    <QuestionSettingsPanel
        :question="question"
        @update:question="$emit('update:question', $event)"
        @duplicate-question="$emit('duplicate-question')"
        @delete-question="$emit('delete-question')"
    >
        <template #type-specific-settings>
            <!-- Bagian Pengaturan Opsi Ranking -->
            <div class="space-y-4 p-4">
                <h3 class="text-sm font-medium text-gray-700">
                    Opsi Peringkat
                </h3>

                <!-- Tombol tambah opsi -->
                <div>
                    <button
                        type="button"
                        @click="tambahSatuOpsi"
                        class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-md text-sm font-medium hover:bg-indigo-100 transition-colors flex items-center"
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
                        Tambah Opsi
                    </button>
                </div>

                <!-- Daftar opsi yang ada -->
                <div v-if="question.options && question.options.length > 0">
                    <div
                        v-for="(option, index) in question.options"
                        :key="index"
                        class="flex items-center space-x-3 mb-2 p-2 bg-white border border-gray-200 rounded-md"
                    >
                        <!-- Nomor posisi -->
                        <div
                            class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-gray-100 rounded-full text-xs font-medium text-gray-700"
                        >
                            {{ index + 1 }}
                        </div>

                        <!-- Input teks opsi -->
                        <div class="flex-grow">
                            <input
                                type="text"
                                v-model="option.text"
                                @input="updateQuestion"
                                class="w-full border-0 focus:ring-indigo-500 focus:border-indigo-500 p-0 text-sm"
                                placeholder="Teks opsi"
                            />
                        </div>

                        <!-- Tombol hapus -->
                        <button
                            type="button"
                            @click="removeOption(index)"
                            class="text-gray-400 hover:text-red-500"
                            aria-label="Hapus opsi"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Tampilkan pesan jika tidak ada opsi -->
                <div
                    v-else
                    class="p-4 bg-gray-50 rounded-md border border-gray-200 text-center text-sm text-gray-500"
                >
                    Belum ada opsi peringkat. Tambahkan opsi menggunakan tombol
                    di atas.
                </div>
            </div>
        </template>
    </QuestionSettingsPanel>
</template>

<script setup>
import { defineProps, defineEmits, ref, watch } from "vue";
import QuestionSettingsPanel from "./QuestionSettingsPanel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update:question"]);

// Pastikan options ada
function ensureOptions() {
    if (!props.question.options) {
        emit("update:question", {
            ...props.question,
            options: [],
        });
    }
}

// Fungsi khusus untuk menambah satu opsi saja
function tambahSatuOpsi() {
    ensureOptions();

    // Buat opsi baru dengan nomor urut yang sesuai
    const newOption = {
        text: `Opsi ${props.question.options?.length + 1 || 1}`,
        value: `option_${Date.now()}`,
    };

    // Tambahkan ke array opsi yang sudah ada
    const updatedOptions = [...(props.question.options || []), newOption];

    // Kirim update ke komponen induk
    emit("update:question", {
        ...props.question,
        options: updatedOptions,
    });
}

// Tambah opsi baru (bisa lebih dari satu)
function addOption(count = 1) {
    ensureOptions();
    const updatedOptions = [...(props.question.options || [])];

    // Tambahkan sebanyak jumlah yang diminta
    for (let i = 0; i < count; i++) {
        const newOption = {
            text: "Opsi " + (props.question.options?.length + i + 1 || i + 1),
            value: `option_${Date.now() + i}`,
        };
        updatedOptions.push(newOption);
    }

    const updatedQuestion = {
        ...props.question,
        options: updatedOptions,
    };

    emit("update:question", updatedQuestion);
}

// Hapus opsi
function removeOption(index) {
    const updatedOptions = [...props.question.options];
    updatedOptions.splice(index, 1);
    emit("update:question", {
        ...props.question,
        options: updatedOptions,
    });
}

// Update pertanyaan ketika opsi berubah
function updateQuestion() {
    emit("update:question", { ...props.question });
}

// Inisialisasi options jika belum ada
ensureOptions();

// Ekspos fungsi untuk dipanggil dari luar
defineExpose({
    addOption,
});
</script>
