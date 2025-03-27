    <!-- Toast message for showing save status -->
    <div v-if="saveStatus === 'saving' || saveStatus === 'error'" 
        class="fixed bottom-4 right-4 p-4 rounded-lg shadow-lg transition-opacity duration-300 z-50"
        :class="{
            'bg-blue-500 text-white': saveStatus === 'saving',
            'bg-red-500 text-white': saveStatus === 'error'
        }">
        <div class="flex items-center">
            <span v-if="saveStatus === 'saving'">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
            </span>
            <span v-else-if="saveStatus === 'error'" class="flex flex-col">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ errorMessage || 'Gagal menyimpan. Coba lagi.' }}</span>
                </div>
                <div v-if="validationErrors && Object.keys(validationErrors).length > 0" class="mt-2 text-sm">
                    <div v-if="validationErrors.slug" class="mt-1 p-2 bg-red-600 rounded">
                        <strong>Slug:</strong> {{ validationErrors.slug.join(', ') }}
                        <div class="mt-1">
                            <button @click="generateNewSlug" class="bg-white text-red-600 px-2 py-1 rounded text-xs font-medium">
                                Generate Slug Baru
                            </button>
                        </div>
                    </div>
                    <div v-for="(errors, field) in otherErrors" :key="field" class="mt-1">
                        <strong>{{ field }}:</strong> {{ errors.join(', ') }}
                    </div>
                </div>
            </span>
        </div>
    </div> 

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useQuestionnaireStore } from '@/questionnaire/store/questionnaire';

const questionnaireStore = useQuestionnaireStore();

// Extract validation errors and error message from the store
const validationErrors = computed(() => questionnaireStore.validationErrors);
const errorMessage = computed(() => questionnaireStore.errorMessage);
const saveStatus = computed(() => questionnaireStore.saveStatus);

// Filter out slug errors to display separately
const otherErrors = computed(() => {
    if (!validationErrors.value) return {};
    
    const errors = { ...validationErrors.value };
    delete errors.slug;
    return errors;
});

// Method to generate a new unique slug
const generateNewSlug = () => {
    // Generate a random 4-digit number
    const randomSuffix = Math.floor(1000 + Math.random() * 9000);
    const baseSlug = questionnaireStore.questionnaire.title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
        
    // Set the new slug with the random suffix
    questionnaireStore.questionnaire.slug = `${baseSlug}-${randomSuffix}`;
    
    // Try saving again
    questionnaireStore.saveQuestionnaire();
};
</script> 