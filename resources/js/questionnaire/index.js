import { createApp } from "vue";
import { createPinia } from "pinia";
import Builder from "./pages/Builder.vue";
import Preview from "./pages/Preview.vue";
import FormView from "./pages/FormView.vue";

// Enable debug mode untuk troubleshooting drag-drop
window.DEBUG_MODE = true;

// Pastikan script ini berjalan setelah DOM selesai dimuat
document.addEventListener("DOMContentLoaded", () => {
    // Inisialisasi Pinia (state management)
    const pinia = createPinia();

    // Mount Builder component
    const builderElement = document.getElementById("questionnaire-builder");
    if (builderElement) {
        console.log("Builder element found, initializing Vue app..."); // Debug log

        // Parse data questionnaire (jika ada)
        let questionnaireData = {};
        try {
            if (builderElement.dataset.questionnaire) {
                questionnaireData = JSON.parse(
                    builderElement.dataset.questionnaire
                );
                console.log("Questionnaire data parsed:", questionnaireData); // Debug log
            }
        } catch (e) {
            console.error("Error parsing questionnaire data:", e);
        }

        const app = createApp(Builder, {
            initialQuestionnaire: questionnaireData,
        });
        app.use(pinia);
        app.mount(builderElement);
        console.log("Builder app mounted successfully"); // Debug log
    } else {
        console.log("No builder element found in the DOM"); // Debug log
    }

    // Mount Preview component
    const previewElement = document.getElementById("questionnaire-preview");
    if (previewElement) {
        // Parse data questionnaire
        let questionnaireData = {};
        try {
            if (previewElement.dataset.questionnaire) {
                questionnaireData = JSON.parse(
                    previewElement.dataset.questionnaire
                );
            }
        } catch (e) {
            console.error("Error parsing questionnaire data for preview:", e);
        }

        const app = createApp(Preview, { questionnaire: questionnaireData });
        app.use(pinia);
        app.mount(previewElement);
    }

    // Mount FormView component
    const formViewElement = document.getElementById("questionnaire-form");
    if (formViewElement) {
        // Parse data questionnaire
        let questionnaireData = {};
        try {
            if (formViewElement.dataset.questionnaire) {
                questionnaireData = JSON.parse(
                    formViewElement.dataset.questionnaire
                );
            }
        } catch (e) {
            console.error("Error parsing questionnaire data for form:", e);
        }

        const app = createApp(FormView, { questionnaire: questionnaireData });
        app.use(pinia);
        app.mount(formViewElement);
    }
});
