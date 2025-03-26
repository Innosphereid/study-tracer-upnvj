import { ref } from "vue";
import { useQuestionnaireStore } from "../store/questionnaire";

export function useDragDrop() {
    const store = useQuestionnaireStore();

    const isDragging = ref(false);
    const draggedItem = ref(null);
    const dragSource = ref(null);
    const dropTarget = ref(null);

    const startDrag = (item, source) => {
        isDragging.value = true;
        draggedItem.value = item;
        dragSource.value = source;
        store.isDragging = true;
    };

    const endDrag = () => {
        isDragging.value = false;
        draggedItem.value = null;
        dragSource.value = null;
        dropTarget.value = null;
        store.isDragging = false;
    };

    const setDropTarget = (target) => {
        dropTarget.value = target;
    };

    const canDrop = (targetType, sourceType) => {
        // Implementasi aturan dropping
        // Misalnya: komponen hanya dapat ditambahkan ke seksi
        if (sourceType === "component" && targetType === "section") {
            return true;
        }

        // Seksi dapat diurutkan dalam kanvas
        if (sourceType === "section" && targetType === "canvas") {
            return true;
        }

        // Pertanyaan dapat diurutkan dalam seksi
        if (sourceType === "question" && targetType === "section") {
            return true;
        }

        return false;
    };

    const handleDrop = (target, targetType) => {
        if (!draggedItem.value || !canDrop(targetType, dragSource.value.type)) {
            endDrag();
            return false;
        }

        if (dragSource.value.type === "component" && targetType === "section") {
            // Tambahkan komponen baru ke seksi
            const sectionId = target.id;
            const componentType = draggedItem.value.type;

            // Temukan indeks seksi
            const sectionIndex = store.questionnaire.sections.findIndex(
                (s) => s.id === sectionId
            );
            if (sectionIndex >= 0) {
                store.currentSectionIndex = sectionIndex;
                store.addQuestion(componentType);
            }
        }

        endDrag();
        return true;
    };

    const handleSectionReorder = (event, sectionList) => {
        // Implementasi reordering seksi
        const { oldIndex, newIndex } = event;

        if (oldIndex === newIndex) return;

        // Buat array indeks baru untuk reorder
        const newOrder = [...Array(sectionList.length).keys()];

        // Swap indeks
        const movedItem = newOrder.splice(oldIndex, 1)[0];
        newOrder.splice(newIndex, 0, movedItem);

        store.reorderSections(newOrder);
    };

    const handleQuestionReorder = (event, sectionId) => {
        // Implementasi reordering pertanyaan dalam seksi
        const { oldIndex, newIndex } = event;

        if (oldIndex === newIndex) return;

        // Temukan seksi yang relevan
        const section = store.questionnaire.sections.find(
            (s) => s.id === sectionId
        );
        if (!section) return;

        // Buat array indeks baru untuk reorder
        const newOrder = [...Array(section.questions.length).keys()];

        // Swap indeks
        const movedItem = newOrder.splice(oldIndex, 1)[0];
        newOrder.splice(newIndex, 0, movedItem);

        store.reorderQuestions(sectionId, newOrder);
    };

    return {
        isDragging,
        draggedItem,
        dragSource,
        dropTarget,
        startDrag,
        endDrag,
        setDropTarget,
        canDrop,
        handleDrop,
        handleSectionReorder,
        handleQuestionReorder,
    };
}
