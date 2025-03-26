<template>
    <!-- Menggunakan pendekatan v-if untuk menampilkan komponen secara konditional -->
    <div>
        <!-- Komponen sesuai tipe pertanyaan -->
        <RadioQuestionSettings
            v-if="question.type === 'radio'"
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        />

        <CheckboxQuestionSettings
            v-else-if="question.type === 'checkbox'"
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        />

        <DropdownQuestionSettings
            v-else-if="question.type === 'dropdown'"
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        />

        <RatingQuestionSettings
            v-else-if="question.type === 'rating'"
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        />

        <LikertQuestionSettings
            v-else-if="question.type === 'likert'"
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        />

        <FileUploadQuestionSettings
            v-else-if="question.type === 'file-upload'"
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        />

        <MatrixQuestionSettings
            v-else-if="question.type === 'matrix'"
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        />

        <RankingQuestionSettings
            v-else-if="question.type === 'ranking'"
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        />

        <QuestionSettingsPanel
            v-else
            :question="question"
            @update:question="$emit('update:question', $event)"
            @duplicate-question="$emit('duplicate-question')"
            @delete-question="$emit('delete-question')"
        >
            <template #type-specific-settings>
                <div class="py-4 text-center text-sm text-gray-500">
                    Tidak ada pengaturan khusus untuk tipe {{ question.type }}
                </div>
            </template>
        </QuestionSettingsPanel>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, onMounted } from "vue";
import RadioQuestionSettings from "./RadioQuestionSettings.vue";
import CheckboxQuestionSettings from "./CheckboxQuestionSettings.vue";
import DropdownQuestionSettings from "./DropdownQuestionSettings.vue";
import RatingQuestionSettings from "./RatingQuestionSettings.vue";
import LikertQuestionSettings from "./LikertQuestionSettings.vue";
import FileUploadQuestionSettings from "./FileUploadQuestionSettings.vue";
import MatrixQuestionSettings from "./MatrixQuestionSettings.vue";
import RankingQuestionSettings from "./RankingQuestionSettings.vue";
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

// Debug initial question
onMounted(() => {
    console.log("QuestionTypeSettings mounted with question:", props.question);
    console.log("Question type:", props.question.type);
});
</script>
