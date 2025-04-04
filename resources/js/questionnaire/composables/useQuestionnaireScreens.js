/**
 * @fileoverview Composable for managing questionnaire welcome and thank you screens
 *
 * This composable extracts the logic for handling the welcome and thank you screens,
 * including fallback text and screen transitions.
 */

import { computed } from "vue";

/**
 * Manages the welcome and thank you screens for a questionnaire
 *
 * @param {Object} options - Configuration options
 * @param {Object} options.questionnaire - The questionnaire data object
 * @returns {Object} Welcome and thank you screen properties and methods
 */
export default function useQuestionnaireScreens(options) {
    const { questionnaire } = options;

    // Welcome Screen computed properties with fallbacks
    const welcomeScreenTitle = computed(() => {
        // Check various possible locations for the welcome screen title
        if (questionnaire.welcomeScreen && questionnaire.welcomeScreen.title) {
            return questionnaire.welcomeScreen.title;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.welcomeScreen &&
            questionnaire.settings.welcomeScreen.title
        ) {
            return questionnaire.settings.welcomeScreen.title;
        }

        return questionnaire.title || "Selamat Datang di Kuesioner Alumni";
    });

    const welcomeScreenSubtitle = computed(() => {
        if (
            questionnaire.welcomeScreen &&
            questionnaire.welcomeScreen.subtitle
        ) {
            return questionnaire.welcomeScreen.subtitle;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.welcomeScreen &&
            questionnaire.settings.welcomeScreen.subtitle
        ) {
            return questionnaire.settings.welcomeScreen.subtitle;
        }

        return "TraceStudy UPNVJ";
    });

    const welcomeScreenDescription = computed(() => {
        if (
            questionnaire.welcomeScreen &&
            questionnaire.welcomeScreen.description
        ) {
            return questionnaire.welcomeScreen.description;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.welcomeScreen &&
            questionnaire.settings.welcomeScreen.description
        ) {
            return questionnaire.settings.welcomeScreen.description;
        }

        return "Terima kasih telah berpartisipasi dalam kuesioner ini. Jawaban Anda sangat berarti bagi kami.";
    });

    // Thank You Screen computed properties with fallbacks
    const thankYouScreenTitle = computed(() => {
        if (
            questionnaire.thankYouScreen &&
            questionnaire.thankYouScreen.title
        ) {
            return questionnaire.thankYouScreen.title;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.thankYouScreen &&
            questionnaire.settings.thankYouScreen.title
        ) {
            return questionnaire.settings.thankYouScreen.title;
        }

        return "Terima Kasih!";
    });

    const thankYouScreenDescription = computed(() => {
        if (
            questionnaire.thankYouScreen &&
            questionnaire.thankYouScreen.description
        ) {
            return questionnaire.thankYouScreen.description;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.thankYouScreen &&
            questionnaire.settings.thankYouScreen.description
        ) {
            return questionnaire.settings.thankYouScreen.description;
        }

        return "Jawaban Anda telah berhasil disimpan. Terima kasih atas partisipasi Anda dalam kuesioner ini.";
    });

    return {
        welcomeScreenTitle,
        welcomeScreenSubtitle,
        welcomeScreenDescription,
        thankYouScreenTitle,
        thankYouScreenDescription,
    };
}
