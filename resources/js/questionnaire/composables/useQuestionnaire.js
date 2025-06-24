import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useQuestionnaireStore } from "../store/questionnaire";

export function useQuestionnaire(initialData = {}) {
    const store = useQuestionnaireStore();
    const isLoading = ref(false);
    const unsavedChanges = ref(false);

    // Inisialisasi kuesioner dengan data awal jika ada
    onMounted(() => {
        if (initialData && Object.keys(initialData).length > 0) {
            store.initializeQuestionnaire(initialData);
        } else {
            // Coba load draft dari localStorage
            const hasDraft = store.loadDraft();

            if (!hasDraft) {
                // Jika tidak ada draft, inisialisasi dengan kuesioner baru
                store.addSection(); // Tambahkan seksi pertama
            }
        }

        // Setup autosave
        store.setupAutosave();

        // Setup pencegahan navigasi jika ada perubahan yang belum disimpan
        window.addEventListener("beforeunload", handleBeforeUnload);
    });

    onBeforeUnmount(() => {
        // Bersihkan interval autosave
        if (store.autosaveInterval) {
            clearInterval(store.autosaveInterval);
        }

        // Hapus event listener
        window.removeEventListener("beforeunload", handleBeforeUnload);
    });

    const handleBeforeUnload = (e) => {
        if (unsavedChanges.value) {
            e.preventDefault();
            e.returnValue =
                "Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?";
            return e.returnValue;
        }
    };

    const saveStatus = computed(() => store.saveStatus);
    const lastSaved = computed(() => store.formattedLastSaved);

    const saveQuestionnaire = () => {
        store.saveQuestionnaire();
        unsavedChanges.value = false;
    };

    const previewQuestionnaire = () => {
        // Simpan kuesioner terlebih dahulu
        saveQuestionnaire();

        // Buka preview di tab baru dengan URL baru
        const previewUrl = `/preview?id=${store.questionnaire.id || "draft"}`;
        window.open(previewUrl, "_blank");
    };

    const publishQuestionnaire = async () => {
        isLoading.value = true;

        try {
            const result = await store.publishQuestionnaire();

            if (result.success) {
                unsavedChanges.value = false;
                return result;
            } else {
                throw new Error("Failed to publish questionnaire");
            }
        } catch (error) {
            console.error("Error publishing questionnaire:", error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    const exportQuestionnaire = () => {
        store.exportQuestionnaire();
    };

    const getComponentCategories = computed(() => {
        const types = store.questionTypes;

        // Kelompokkan berdasarkan kategori
        const categories = {
            dasar: types.filter((t) => t.category === "dasar"),
            pilihan: types.filter((t) => t.category === "pilihan"),
            lanjutan: types.filter((t) => t.category === "lanjutan"),
        };

        return categories;
    });

    const addComponent = (type) => {
        store.addQuestion(type);
        unsavedChanges.value = true;
    };

    const addSection = () => {
        store.addSection();
        unsavedChanges.value = true;
    };

    const duplicateSection = (sectionId) => {
        store.duplicateSection(sectionId);
        unsavedChanges.value = true;
    };

    const deleteSection = (sectionId) => {
        store.deleteSection(sectionId);
        unsavedChanges.value = true;
    };

    const updateSection = (sectionId, updates) => {
        store.updateSection(sectionId, updates);
        unsavedChanges.value = true;
    };

    const duplicateQuestion = (questionId) => {
        store.duplicateQuestion(questionId);
        unsavedChanges.value = true;
    };

    const deleteQuestion = (questionId) => {
        store.deleteQuestion(questionId);
        unsavedChanges.value = true;
    };

    const updateQuestion = (questionId, updates) => {
        store.updateQuestion(questionId, updates);
        unsavedChanges.value = true;
    };

    const selectComponent = (type, id) => {
        store.selectComponent(type, id);
    };

    const updateQuestionnaireSettings = (settings) => {
        store.updateQuestionnaireSettings(settings);
        unsavedChanges.value = true;
    };

    const updateWelcomeScreen = (updates) => {
        store.updateWelcomeScreen(updates);
        unsavedChanges.value = true;
    };

    const updateThankYouScreen = (updates) => {
        store.updateThankYouScreen(updates);
        unsavedChanges.value = true;
    };

    return {
        questionnaire: computed(() => store.questionnaire),
        currentSection: computed(() => store.currentSection),
        currentQuestion: computed(() => store.currentQuestion),
        selectedComponent: computed(() => store.selectedComponent),
        totalSections: computed(() => store.totalSections),
        questionTypes: computed(() => store.questionTypes),
        componentCategories: getComponentCategories,
        isLoading,
        saveStatus,
        lastSaved,
        unsavedChanges,
        saveQuestionnaire,
        previewQuestionnaire,
        publishQuestionnaire,
        exportQuestionnaire,
        addComponent,
        addSection,
        duplicateSection,
        deleteSection,
        updateSection,
        duplicateQuestion,
        deleteQuestion,
        updateQuestion,
        selectComponent,
        updateQuestionnaireSettings,
        updateWelcomeScreen,
        updateThankYouScreen,
    };
}
