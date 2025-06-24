/**
 * Composable untuk menyediakan konfigurasi default untuk berbagai jenis pertanyaan
 * yang dapat digunakan oleh komponen preview
 */
export function useDefaultConfigs() {
    // Default scale for likert preview if not specified in the question
    const defaultLikertScale = [
        { value: 1, label: "Sangat Tidak Setuju" },
        { value: 2, label: "Tidak Setuju" },
        { value: 3, label: "Netral" },
        { value: 4, label: "Setuju" },
        { value: 5, label: "Sangat Setuju" },
    ];

    // Default values for matrix preview if not specified in the question
    const defaultMatrixRows = [
        { id: "row1", text: "Baris 1" },
        { id: "row2", text: "Baris 2" },
    ];

    const defaultMatrixColumns = [
        { id: "col1", text: "Kolom 1" },
        { id: "col2", text: "Kolom 2" },
        { id: "col3", text: "Kolom 3" },
    ];

    // Default slider configuration for preview if not specified in the question
    const defaultSliderConfig = {
        min: 0,
        max: 100,
        step: 1,
        defaultValue: 50,
        labels: [
            { value: 0, text: "Minimum" },
            { value: 50, text: "Sedang" },
            { value: 100, text: "Maksimum" },
        ],
    };

    // Default ranking options for preview if not specified in the question
    const defaultRankingOptions = [
        { id: "option1", text: "Pilihan 1" },
        { id: "option2", text: "Pilihan 2" },
        { id: "option3", text: "Pilihan 3" },
        { id: "option4", text: "Pilihan 4" },
        { id: "option5", text: "Pilihan 5" },
    ];

    // Helper functions untuk slider
    const calculateSliderPercentage = (question) => {
        const min = question.min ?? defaultSliderConfig.min;
        const max = question.max ?? defaultSliderConfig.max;
        const value = question.defaultValue ?? defaultSliderConfig.defaultValue;

        return ((value - min) / (max - min)) * 100;
    };

    const calculateCurrentValue = (question) => {
        const min = question.min ?? defaultSliderConfig.min;
        const max = question.max ?? defaultSliderConfig.max;
        const step = question.step ?? defaultSliderConfig.step;

        // If defaultValue is defined, use it, otherwise use the config default
        const rawValue =
            question.defaultValue ?? defaultSliderConfig.defaultValue;

        // Round to the nearest step value if needed
        const roundedValue = Math.round(rawValue / step) * step;

        return roundedValue;
    };

    const calculateLabelPosition = (value, question) => {
        const min = question.min ?? defaultSliderConfig.min;
        const max = question.max ?? defaultSliderConfig.max;
        const percentage = ((value - min) / (max - min)) * 100;
        return percentage;
    };

    return {
        defaultLikertScale,
        defaultMatrixRows,
        defaultMatrixColumns,
        defaultSliderConfig,
        defaultRankingOptions,
        calculateSliderPercentage,
        calculateCurrentValue,
        calculateLabelPosition,
    };
}
