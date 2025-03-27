import "../bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";
import Builder from "./pages/Builder.vue";
import Preview from "./pages/Preview.vue";
import FormView from "./pages/FormView.vue";

// Import standalone preview component
import PreviewApp from "./components/preview/PreviewApp.vue";

// Enable debug mode untuk troubleshooting drag-drop
window.DEBUG_MODE = true;

// Find the standalone preview element
const standalonePreviewElement = document.getElementById("standalone-preview");

// Pastikan script ini berjalan setelah DOM selesai dimuat
document.addEventListener("DOMContentLoaded", () => {
    const builderElement = document.getElementById("questionnaire-builder");
    const previewElement = document.getElementById("questionnaire-preview");
    const formElement = document.getElementById("questionnaire-form");

    const pinia = createPinia();

    if (builderElement) {
        console.log("Initializing Vue Builder...");
        const app = createApp(Builder);
        app.use(pinia);

        // Get initial data from the data-questionnaire attribute
        let initialQuestionnaire = {};
        try {
            if (builderElement.dataset.questionnaire) {
                initialQuestionnaire = JSON.parse(
                    builderElement.dataset.questionnaire
                );
                console.log("Parsed questionnaire data successfully");

                // Ensure ID is properly handled for string operations
                if (
                    initialQuestionnaire.id &&
                    typeof initialQuestionnaire.id !== "string"
                ) {
                    console.log(
                        `Converting ID from ${typeof initialQuestionnaire.id} to string`
                    );
                    // Convert ID to string if it's not already a string
                    // This ensures methods like startsWith can be safely used
                    initialQuestionnaire.id = String(initialQuestionnaire.id);
                }

                // Log processed data
                console.log("Processed questionnaire data:", {
                    id: initialQuestionnaire.id,
                    idType: typeof initialQuestionnaire.id,
                    title: initialQuestionnaire.title,
                    status: initialQuestionnaire.status,
                });
            }
        } catch (error) {
            console.error("Error parsing questionnaire data:", error);
        }

        app.provide("initialData", initialQuestionnaire);
        app.mount(builderElement);
    } else if (previewElement) {
        console.log("Preview element found, initializing preview app...");

        // Parse questionnaire data
        let questionnaire = {};
        try {
            if (previewElement.dataset.questionnaire) {
                questionnaire = JSON.parse(
                    previewElement.dataset.questionnaire
                );

                // Ensure ID is properly handled for string operations
                if (questionnaire.id && typeof questionnaire.id !== "string") {
                    console.log(
                        `Converting ID from ${typeof questionnaire.id} to string`
                    );
                    questionnaire.id = String(questionnaire.id);
                }
            }
        } catch (e) {
            console.error("Error parsing questionnaire data:", e);
        }

        const app = createApp(Preview, {
            questionnaire,
        });
        app.use(pinia);
        app.mount(previewElement);
    } else if (formElement) {
        console.log("Form element found, initializing form app...");

        // Parse questionnaire data
        let questionnaire = {};
        try {
            if (formElement.dataset.questionnaire) {
                questionnaire = JSON.parse(formElement.dataset.questionnaire);

                // Ensure ID is properly handled for string operations
                if (questionnaire.id && typeof questionnaire.id !== "string") {
                    console.log(
                        `Converting ID from ${typeof questionnaire.id} to string`
                    );
                    questionnaire.id = String(questionnaire.id);
                }
            }
        } catch (e) {
            console.error("Error parsing questionnaire data:", e);
        }

        const app = createApp(FormView, {
            questionnaire,
            isPreview: formElement.dataset.preview === "true",
        });
        app.use(pinia);
        app.mount(formElement);
    } else if (standalonePreviewElement) {
        console.log(
            "Standalone preview element found, initializing standalone preview app..."
        );

        // Parse questionnaire data
        let questionnaire = {};
        try {
            if (standalonePreviewElement.dataset.questionnaire) {
                questionnaire = JSON.parse(
                    standalonePreviewElement.dataset.questionnaire
                );

                // Ensure ID is properly handled for string operations
                if (questionnaire.id && typeof questionnaire.id !== "string") {
                    console.log(
                        `Converting ID from ${typeof questionnaire.id} to string`
                    );
                    questionnaire.id = String(questionnaire.id);
                }
            }
        } catch (e) {
            console.error("Error parsing questionnaire data:", e);
        }

        const app = createApp(PreviewApp, {
            questionnaire,
        });
        app.use(pinia);
        app.mount(standalonePreviewElement);
    }
});
