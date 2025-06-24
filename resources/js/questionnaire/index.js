import "../bootstrap";
import { createApp } from "vue";
import { createPinia } from "pinia";
import Builder from "./pages/Builder.vue";
import Preview from "./pages/Preview.vue";
import FormView from "./pages/FormView.vue";
import AlumniQuestionnaireApp from "./components/alumni/AlumniQuestionnaireApp.vue";

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
        let sections = [];
        try {
            if (formElement.dataset.questionnaire) {
                questionnaire = JSON.parse(formElement.dataset.questionnaire);
                console.log("Parsed questionnaire data:", questionnaire);

                // Ensure ID is properly handled for string operations
                if (questionnaire.id && typeof questionnaire.id !== "string") {
                    console.log(
                        `Converting ID from ${typeof questionnaire.id} to string`
                    );
                    questionnaire.id = String(questionnaire.id);
                }
            }

            // Try to parse sections data if available
            if (formElement.dataset.sections) {
                sections = JSON.parse(formElement.dataset.sections);
                console.log("Parsed sections data:", sections);

                // Add sections to questionnaire object if it doesn't have them
                if (
                    !questionnaire.sections ||
                    questionnaire.sections.length === 0
                ) {
                    questionnaire.sections = sections;
                    console.log(
                        "Added sections to questionnaire:",
                        questionnaire
                    );
                }
            }

            // Tambahan logging untuk troubleshooting
            console.log("Questionnaire structure validation:");
            console.log("- Has sections property:", !!questionnaire.sections);
            console.log(
                "- Sections count:",
                questionnaire.sections ? questionnaire.sections.length : 0
            );
            console.log("- Has settings:", !!questionnaire.settings);
            console.log(
                "- Has welcomeScreen:",
                questionnaire.settings && !!questionnaire.settings.welcomeScreen
            );
            console.log(
                "- Has thankYouScreen:",
                questionnaire.settings &&
                    !!questionnaire.settings.thankYouScreen
            );
            console.log("- Raw settings:", questionnaire.settings);

            // Ensure settings are parsed if they are stored as string
            if (
                questionnaire.settings &&
                typeof questionnaire.settings === "string"
            ) {
                try {
                    questionnaire.settings = JSON.parse(questionnaire.settings);
                    console.log(
                        "Parsed settings from string:",
                        questionnaire.settings
                    );
                } catch (e) {
                    console.error("Failed to parse settings string:", e);
                }
            }

            // Checando a estrutura de sections
            if (questionnaire.sections && questionnaire.sections.length > 0) {
                console.log("First section validation:");
                const firstSection = questionnaire.sections[0];
                console.log(
                    "- Has questions property:",
                    !!firstSection.questions
                );
                console.log(
                    "- Questions count:",
                    firstSection.questions ? firstSection.questions.length : 0
                );

                if (
                    firstSection.questions &&
                    firstSection.questions.length > 0
                ) {
                    console.log("First question validation:");
                    const firstQuestion = firstSection.questions[0];
                    console.log(
                        "- Question type:",
                        firstQuestion.type || firstQuestion.question_type
                    );
                    console.log("- Has settings:", !!firstQuestion.settings);
                    console.log("- Has options:", !!firstQuestion.options);
                }
            }
        } catch (e) {
            console.error("Error parsing questionnaire or sections data:", e);
        }

        // Determine which app to mount based on context
        const isPreview = formElement.dataset.preview === "true";
        const useNewAlumniInterface =
            formElement.dataset.useNewInterface === "true";

        // Use the new alumni interface if specified, otherwise use the legacy form view
        const AppComponent = useNewAlumniInterface
            ? AlumniQuestionnaireApp
            : FormView;

        console.log(
            "Mounting component:",
            useNewAlumniInterface ? "AlumniQuestionnaireApp" : "FormView"
        );

        try {
            const app = createApp(AppComponent, {
                questionnaire,
                isPreview,
            });
            app.use(pinia);

            // Add global error handler
            app.config.errorHandler = (err, vm, info) => {
                console.error("Vue Error:", err);
                console.error("Component:", vm);
                console.error("Error Info:", info);
                console.error("Error Stack:", err.stack);

                // Display error on page for debugging
                const errorDiv = document.createElement("div");
                errorDiv.style.backgroundColor = "#FEE2E2";
                errorDiv.style.color = "#B91C1C";
                errorDiv.style.padding = "20px";
                errorDiv.style.margin = "20px";
                errorDiv.style.borderRadius = "5px";
                errorDiv.style.border = "1px solid #B91C1C";
                errorDiv.innerHTML = `
                    <h3 style="font-weight: bold; margin-bottom: 10px;">Error Rendering Vue App</h3>
                    <p>${err.message}</p>
                    <details>
                        <summary style="cursor: pointer; margin-top: 10px;">Stack Trace</summary>
                        <pre style="margin-top: 10px; font-size: 12px; white-space: pre-wrap;">${
                            err.stack
                        }</pre>
                    </details>
                    <details>
                        <summary style="cursor: pointer; margin-top: 10px;">Component Data</summary>
                        <pre style="margin-top: 10px; font-size: 12px; white-space: pre-wrap;">Questionnaire ID: ${
                            questionnaire.id || "Unknown"
                        }\nSections: ${
                    questionnaire.sections
                        ? questionnaire.sections.length
                        : "None"
                }</pre>
                    </details>
                `;
                formElement.parentNode.insertBefore(errorDiv, formElement);
            };

            app.mount(formElement);
            console.log("Component mounted successfully");
        } catch (err) {
            console.error("Failed to mount Vue app:", err);
            // Display error on page
            const errorDiv = document.createElement("div");
            errorDiv.style.backgroundColor = "#FEE2E2";
            errorDiv.style.color = "#B91C1C";
            errorDiv.style.padding = "20px";
            errorDiv.style.margin = "20px";
            errorDiv.style.borderRadius = "5px";
            errorDiv.style.border = "1px solid #B91C1C";
            errorDiv.innerHTML = `
                <h3 style="font-weight: bold; margin-bottom: 10px;">Error Initializing Vue App</h3>
                <p>${err.message}</p>
                <details>
                    <summary style="cursor: pointer; margin-top: 10px;">Stack Trace</summary>
                    <pre style="margin-top: 10px; font-size: 12px; white-space: pre-wrap;">${
                        err.stack
                    }</pre>
                </details>
                <details>
                    <summary style="cursor: pointer; margin-top: 10px;">Component Data</summary>
                    <pre style="margin-top: 10px; font-size: 12px; white-space: pre-wrap;">Questionnaire ID: ${
                        questionnaire.id || "Unknown"
                    }\nSections: ${
                questionnaire.sections ? questionnaire.sections.length : "None"
            }</pre>
                </details>
            `;
            formElement.parentNode.insertBefore(errorDiv, formElement);
        }
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
