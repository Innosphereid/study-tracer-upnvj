import "../bootstrap";
import { createApp } from "vue";
import ResultsApp from "./pages/ResultsApp.vue";

// Disable console logs in production
if (process.env.NODE_ENV === "production") {
    console.log = () => {};
    console.debug = () => {};
}

const mountPoint = document.getElementById("questionnaire-results");

if (mountPoint) {
    try {
        // Parse data attributes
        const questionnaire = JSON.parse(
            mountPoint.dataset.questionnaire || "{}"
        );
        const statistics = JSON.parse(mountPoint.dataset.statistics || "{}");
        const questionnaireId = mountPoint.dataset.questionnaireId;

        // Validate that we have a valid questionnaire ID
        if (!questionnaireId || questionnaireId === "undefined") {
            console.error("Invalid questionnaire ID:", questionnaireId);
            document.getElementById("error-message").style.display = "block";
            document.getElementById("error-message").textContent =
                "Error: Invalid questionnaire ID. Please refresh the page or contact support.";
        }
        // Continue only if we have a valid questionnaire ID
        else {
            // Add questionnaire_id to statistics object to ensure it's available
            if (statistics && questionnaire && questionnaire.id) {
                statistics.questionnaire_id = questionnaire.id;
            }

            // Create and mount Vue app
            const app = createApp(ResultsApp, {
                questionnaire,
                statistics,
                questionnaireId,
            });

            // Error handling
            app.config.errorHandler = (err, vm, info) => {
                console.error("Vue Error:", err);
                console.error("Error Info:", info);
                document.getElementById("error-message").style.display =
                    "block";
            };

            // Mount the application
            app.mount(mountPoint);

            console.info("Results app mounted successfully");
        }
    } catch (error) {
        console.error("Failed to initialize results app:", error);
        document.getElementById("error-message").style.display = "block";
    }
} else {
    console.error("Mount point for results app not found");
}
