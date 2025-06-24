<template>
    <div>
        <QuestionSettingsPanel
            :question="question"
            @update:question="updateQuestionBase"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        >
            <template #type-specific-settings>
                <!-- Checkbox options -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Opsi Jawaban
                    </h3>

                    <div class="space-y-3">
                        <div
                            v-for="(option, index) in localOptions"
                            :key="option.id"
                            class="flex"
                        >
                            <div class="flex-grow">
                                <input
                                    type="text"
                                    :id="`option-${index}`"
                                    v-model="option.text"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Teks Opsi"
                                    @change="updateQuestion"
                                />
                            </div>

                            <div class="flex items-center ml-2">
                                <button
                                    type="button"
                                    class="text-gray-400 hover:text-red-500"
                                    @click="removeOption(index)"
                                    :disabled="localOptions.length <= 1"
                                    :class="{
                                        'opacity-30 cursor-not-allowed':
                                            localOptions.length <= 1,
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="addOption"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Tambah Opsi
                        </button>
                    </div>

                    <!-- Options Order -->
                    <div class="mt-4">
                        <label
                            for="options-order"
                            class="block text-sm font-medium text-gray-700"
                            >Urutan Opsi</label
                        >
                        <select
                            id="options-order"
                            v-model="localOptionsOrder"
                            class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        >
                            <option value="none">Tidak ada urutan</option>
                            <option value="asc">Menaik (A-Z)</option>
                            <option value="desc">Menurun (Z-A)</option>
                        </select>
                    </div>

                    <!-- Additional Options Checkboxes -->
                    <div class="mt-4 space-y-2">
                        <!-- Allow Other Option -->
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="allow-other"
                                v-model="localAllowOther"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                @change="updateQuestion"
                            />
                            <label
                                for="allow-other"
                                class="ml-2 block text-sm text-gray-700"
                                >Tampilkan Opsi "Lainnya"</label
                            >
                        </div>

                        <!-- Allow None Option -->
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="allow-none"
                                v-model="localAllowNone"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                @change="updateQuestion"
                            />
                            <label
                                for="allow-none"
                                class="ml-2 block text-sm text-gray-700"
                                >Tampilkan Opsi "Tidak Ada"</label
                            >
                        </div>

                        <!-- Allow Select All Option -->
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="allow-select-all"
                                v-model="localAllowSelectAll"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                @change="updateQuestion"
                            />
                            <label
                                for="allow-select-all"
                                class="ml-2 block text-sm text-gray-700"
                                >Tampilkan Opsi "Pilih Semua"</label
                            >
                        </div>
                    </div>

                    <!-- Min/Max Selection Limits -->
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">
                            Batas Pemilihan
                        </h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="min-selected"
                                    class="block text-sm font-medium text-gray-700"
                                    >Minimum Pilihan</label
                                >
                                <input
                                    type="number"
                                    id="min-selected"
                                    v-model.number="localMinSelected"
                                    min="0"
                                    :max="maxPossibleSelection"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    @change="handleMinMaxChange"
                                />
                            </div>

                            <div>
                                <label
                                    for="max-selected"
                                    class="block text-sm font-medium text-gray-700"
                                    >Maksimum Pilihan</label
                                >
                                <input
                                    type="number"
                                    id="max-selected"
                                    v-model.number="localMaxSelected"
                                    min="0"
                                    :max="maxPossibleSelection"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    @change="handleMinMaxChange"
                                />
                                <p
                                    class="mt-1 text-xs text-gray-500"
                                    v-if="localMaxSelected === 0"
                                >
                                    0 = tidak dibatasi
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </QuestionSettingsPanel>
    </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, watch } from "vue";
import { v4 as uuidv4 } from "uuid";
import QuestionSettingsPanel from "./QuestionSettingsPanel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "update:question",
    "duplicate-question",
    "delete-question",
]);

// Local state for checkbox-specific settings
const localOptions = ref(
    JSON.parse(JSON.stringify(props.question.options || []))
);
const localAllowOther = ref(props.question.allowOther || false);
const localAllowNone = ref(props.question.allowNone || false);
const localAllowSelectAll = ref(props.question.allowSelectAll || false);
const localOptionsOrder = ref(props.question.optionsOrder || "none");
const localMinSelected = ref(props.question.minSelected || 0);
const localMaxSelected = ref(props.question.maxSelected || 0);

// Compute the maximum possible selection (number of options)
const maxPossibleSelection = computed(() => {
    return localOptions.value.length;
});

// Watch for changes in the question prop
watch(
    () => props.question,
    (newQuestion) => {
        localOptions.value = JSON.parse(
            JSON.stringify(newQuestion.options || [])
        );
        localAllowOther.value = newQuestion.allowOther || false;
        localAllowNone.value = newQuestion.allowNone || false;
        localAllowSelectAll.value = newQuestion.allowSelectAll || false;
        localOptionsOrder.value = newQuestion.optionsOrder || "none";
        localMinSelected.value = newQuestion.minSelected || 0;
        localMaxSelected.value = newQuestion.maxSelected || 0;
    },
    { deep: true }
);

// Add a new option
const addOption = () => {
    const newOption = {
        id: uuidv4(),
        text: `Opsi ${localOptions.value.length + 1}`,
        value: `option_${localOptions.value.length + 1}`,
    };

    localOptions.value.push(newOption);
    updateQuestion();
};

// Remove an option
const removeOption = (index) => {
    if (localOptions.value.length <= 1) return;

    localOptions.value.splice(index, 1);
    updateQuestion();
};

// Handle min/max selection changes
const handleMinMaxChange = () => {
    // Ensure min is not greater than max (if max is set)
    if (
        localMaxSelected.value > 0 &&
        localMinSelected.value > localMaxSelected.value
    ) {
        localMinSelected.value = localMaxSelected.value;
    }

    // Ensure max is not less than min
    if (
        localMaxSelected.value > 0 &&
        localMaxSelected.value < localMinSelected.value
    ) {
        localMaxSelected.value = localMinSelected.value;
    }

    // Ensure min and max are not greater than the number of options
    if (localMinSelected.value > maxPossibleSelection.value) {
        localMinSelected.value = maxPossibleSelection.value;
    }

    if (
        localMaxSelected.value > maxPossibleSelection.value &&
        localMaxSelected.value !== 0
    ) {
        localMaxSelected.value = maxPossibleSelection.value;
    }

    updateQuestion();
};

// Update base question properties
const updateQuestionBase = (updatedBaseQuestion) => {
    emit("update:question", {
        ...updatedBaseQuestion,
        options: localOptions.value,
        allowOther: localAllowOther.value,
        allowNone: localAllowNone.value,
        allowSelectAll: localAllowSelectAll.value,
        optionsOrder: localOptionsOrder.value,
        minSelected: localMinSelected.value,
        maxSelected: localMaxSelected.value,
    });
};

// Update checkbox-specific settings
const updateQuestion = () => {
    emit("update:question", {
        ...props.question,
        options: localOptions.value,
        allowOther: localAllowOther.value,
        allowNone: localAllowNone.value,
        allowSelectAll: localAllowSelectAll.value,
        optionsOrder: localOptionsOrder.value,
        minSelected: localMinSelected.value,
        maxSelected: localMaxSelected.value,
    });
};
</script>
