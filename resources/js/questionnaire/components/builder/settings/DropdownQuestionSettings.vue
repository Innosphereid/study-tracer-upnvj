<template>
    <div>
        <QuestionSettingsPanel
            :question="question"
            @update:question="updateQuestionBase"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        >
            <template #type-specific-settings>
                <!-- Dropdown options -->
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
                    </div>
                </div>
            </template>
        </QuestionSettingsPanel>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from "vue";
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

// Local state for dropdown-specific settings
const localOptions = ref([]);
const localAllowOther = ref(props.question.allowOther || false);
const localAllowNone = ref(props.question.allowNone || false);

// Initialize with default options if none exist
const initializeOptions = () => {
    if (props.question.options && props.question.options.length > 0) {
        localOptions.value = JSON.parse(JSON.stringify(props.question.options));
    } else {
        // Create 3 default options
        localOptions.value = [
            { id: uuidv4(), text: "Opsi 1", value: "option_1" },
            { id: uuidv4(), text: "Opsi 2", value: "option_2" },
            { id: uuidv4(), text: "Opsi 3", value: "option_3" },
        ];
    }
};

// Initialize options on component creation
initializeOptions();

// Watch for changes in the question prop
watch(
    () => props.question,
    (newQuestion) => {
        if (newQuestion.options && newQuestion.options.length > 0) {
            localOptions.value = JSON.parse(
                JSON.stringify(newQuestion.options)
            );
        }
        localAllowOther.value = newQuestion.allowOther || false;
        localAllowNone.value = newQuestion.allowNone || false;
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

// Update base question properties
const updateQuestionBase = (updatedBaseQuestion) => {
    emit("update:question", {
        ...updatedBaseQuestion,
        options: localOptions.value,
        allowOther: localAllowOther.value,
        allowNone: localAllowNone.value,
    });
};

// Update dropdown-specific settings
const updateQuestion = () => {
    emit("update:question", {
        ...props.question,
        options: localOptions.value,
        allowOther: localAllowOther.value,
        allowNone: localAllowNone.value,
    });
};
</script>
