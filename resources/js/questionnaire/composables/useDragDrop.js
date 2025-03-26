import { ref } from "vue";
import { useQuestionnaireStore } from "../store/questionnaire";

export function useDragDrop() {
    const store = useQuestionnaireStore();

    const isDragging = ref(false);
    const draggedItem = ref(null);
    const dragSource = ref(null);
    const dropTarget = ref(null);

    const startDrag = (dragEvent) => {
        if (dragEvent && dragEvent.item) {
            isDragging.value = true;
            draggedItem.value = dragEvent.item;
            dragSource.value = { type: dragEvent.sourceType };
            store.isDragging = true;
        }
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

    const handleDrop = (dropEvent) => {
        // Handle drop events from DropZone component
        if (dropEvent && dropEvent.item && dropEvent.targetType) {
            const { item, sourceType, targetType, targetId } = dropEvent;

            console.log("Drop event:", {
                item,
                sourceType,
                targetType,
                targetId,
            });

            if (!canDrop(targetType, sourceType)) {
                console.log("Cannot drop this item type in this target", {
                    targetType,
                    sourceType,
                });
                return false;
            }

            if (sourceType === "component" && targetType === "section") {
                // Add new component to section
                const sectionId = targetId;
                const componentType = item.id;

                console.log("Adding component to section", {
                    sectionId,
                    componentType,
                });

                // Find section index
                const sectionIndex = store.questionnaire.sections.findIndex(
                    (s) => s.id === sectionId
                );

                console.log("Section index:", sectionIndex);

                if (sectionIndex >= 0) {
                    store.currentSectionIndex = sectionIndex;
                    store.addQuestion(componentType);
                    return true;
                }
            } else if (sourceType === "component" && targetType === "canvas") {
                // For components dropped on canvas, create a new section first
                console.log("Adding component to new section");

                store.addSection();
                const newSectionId =
                    store.questionnaire.sections[
                        store.questionnaire.sections.length - 1
                    ].id;

                console.log("New section created:", newSectionId);

                // Then add the component to the new section
                const componentType = item.id;
                store.addQuestion(componentType);
                return true;
            }
        }
        return false;
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
