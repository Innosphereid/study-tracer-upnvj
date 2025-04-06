/**
 * Actions for publishing and exporting questionnaires
 * Contains operations for publishing, exporting, and other sharing operations
 *
 * @module questionnaire/store/actions/publishing
 */

import axios from "axios";

/**
 * Collection of actions for publishing and exporting questionnaires
 * @type {Object}
 */
export default {
    /**
     * Export the questionnaire as JSON
     */
    exportQuestionnaire() {
        // Create a JSON export of the questionnaire
        const exportData = JSON.stringify(this.questionnaire, null, 2);
        const blob = new Blob([exportData], { type: "application/json" });
        const url = URL.createObjectURL(blob);

        // Create a temporary link for download
        const a = document.createElement("a");
        a.href = url;
        a.download = `${
            this.questionnaire.slug || "questionnaire"
        }_export.json`;
        document.body.appendChild(a);
        a.click();

        // Clean up
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 100);
    },

    /**
     * Publish the questionnaire to make it accessible to respondents
     * @returns {Promise} Promise that resolves when the publish is complete
     */
    publishQuestionnaire() {
        // Ensure the questionnaire is saved first
        return this.saveQuestionnaire()
            .then(() => {
                // Now publish the saved questionnaire
                return axios({
                    method: "post",
                    url: `/kuesioner/${this.questionnaire.id}/publish`,
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content"),
                    },
                });
            })
            .then((response) => {
                if (response.data.success) {
                    // Update the status locally
                    this.questionnaire.status = "published";
                    return response.data;
                } else {
                    throw new Error(
                        response.data.message ||
                            "Failed to publish questionnaire"
                    );
                }
            });
    },
};
