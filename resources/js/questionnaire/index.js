import { createApp } from "vue";
import { createPinia } from "pinia";
import Builder from "./pages/Builder.vue";
import Preview from "./pages/Preview.vue";
import FormView from "./pages/FormView.vue";

// Initialize Pinia
const pinia = createPinia();

// Mount the application components if they exist in the DOM
document.addEventListener("DOMContentLoaded", () => {
    // Builder component
    const builderElement = document.getElementById("questionnaire-builder");
    if (builderElement) {
        const questionnaire = JSON.parse(
            builderElement.dataset.questionnaire || "{}"
        );
        const app = createApp(Builder, { initialQuestionnaire: questionnaire });
        app.use(pinia);
        app.mount(builderElement);
    }

    // Preview component
    const previewElement = document.getElementById("questionnaire-preview");
    if (previewElement) {
        const questionnaire = JSON.parse(
            previewElement.dataset.questionnaire || "{}"
        );
        const app = createApp(Preview, { questionnaire });
        app.use(pinia);
        app.mount(previewElement);
    }

    // Form view component (for alumni)
    const formViewElement = document.getElementById("questionnaire-form");
    if (formViewElement) {
        const questionnaire = JSON.parse(
            formViewElement.dataset.questionnaire || "{}"
        );
        const app = createApp(FormView, { questionnaire });
        app.use(pinia);
        app.mount(formViewElement);
    }
});
