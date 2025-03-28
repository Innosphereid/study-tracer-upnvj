import { defineStore } from "pinia";
import { v4 as uuidv4 } from "uuid";
import axios from "axios";
import { slugify } from "../utils/helpers"; // Import the slugify function

export const useQuestionnaireStore = defineStore("questionnaire", {
    state: () => ({
        questionnaire: {
            id: null,
            title: "Kuesioner Baru",
            description: "",
            slug: "",
            startDate: null,
            endDate: null,
            showProgressBar: true,
            showPageNumbers: true,
            requiresLogin: false,
            sections: [],
            welcomeScreen: {
                title: "Selamat Datang",
                description:
                    "Terima kasih telah berpartisipasi dalam tracer study kami.",
            },
            thankYouScreen: {
                title: "Terima Kasih",
                description:
                    "Terima kasih atas partisipasi Anda dalam tracer study kami.",
            },
        },
        currentSectionIndex: 0,
        currentQuestionIndex: -1,
        selectedComponent: null,
        isDragging: false,
        isEditing: false,
        saveStatus: "idle", // 'idle', 'saving', 'saved', 'error'
        errorMessage: null, // For storing specific error messages
        validationErrors: null, // For storing validation errors
        lastSaved: null,
        autosaveInterval: null,
        originalQuestionnaire: null,
    }),

    getters: {
        currentSection: (state) => {
            return (
                state.questionnaire.sections[state.currentSectionIndex] || null
            );
        },

        currentQuestion: (state) => {
            if (!state.currentSection || state.currentQuestionIndex < 0)
                return null;
            return (
                state.currentSection.questions[state.currentQuestionIndex] ||
                null
            );
        },

        totalSections: (state) => state.questionnaire.sections.length,

        canAddQuestion: (state) => state.questionnaire.sections.length > 0,

        questionTypes: () => [
            {
                id: "short-text",
                name: "Teks Pendek",
                icon: "text",
                category: "dasar",
            },
            {
                id: "long-text",
                name: "Teks Panjang",
                icon: "paragraph",
                category: "dasar",
            },
            {
                id: "email",
                name: "Email",
                icon: "mail",
                category: "dasar",
            },
            {
                id: "phone",
                name: "Nomor Telepon",
                icon: "phone",
                category: "dasar",
            },
            {
                id: "number",
                name: "Angka",
                icon: "number",
                category: "dasar",
            },
            {
                id: "date",
                name: "Tanggal",
                icon: "calendar",
                category: "dasar",
            },
            {
                id: "radio",
                name: "Pilihan Ganda",
                icon: "radio",
                category: "pilihan",
            },
            {
                id: "checkbox",
                name: "Kotak Centang",
                icon: "checkbox",
                category: "pilihan",
            },
            {
                id: "dropdown",
                name: "Dropdown",
                icon: "dropdown",
                category: "pilihan",
            },
            {
                id: "rating",
                name: "Rating Bintang",
                icon: "star",
                category: "pilihan",
            },
            {
                id: "likert",
                name: "Skala Likert",
                icon: "scale",
                category: "pilihan",
            },
            {
                id: "yes-no",
                name: "Ya/Tidak",
                icon: "boolean",
                category: "pilihan",
            },
            {
                id: "file-upload",
                name: "Upload File",
                icon: "upload",
                category: "lanjutan",
            },
            {
                id: "matrix",
                name: "Matriks Pilihan",
                icon: "matrix",
                category: "lanjutan",
            },
            {
                id: "slider",
                name: "Slider",
                icon: "slider",
                category: "lanjutan",
            },
            // Temporarily disabled due to a bug with drag and drop functionality
            // that causes navigation to first page when interacting with ranking items
            // TODO: Fix the bug and re-enable this component
            /* {
                id: "ranking",
                name: "Rangking",
                icon: "ranking",
                category: "lanjutan",
            }, */
        ],

        formattedLastSaved: (state) => {
            if (!state.lastSaved) return "";
            return new Date(state.lastSaved).toLocaleString("id-ID");
        },
    },

    actions: {
        initializeQuestionnaire(data) {
            if (data && Object.keys(data).length > 0) {
                this.questionnaire = { ...this.questionnaire, ...data };
            }
        },

        addSection() {
            const newSection = {
                id: uuidv4(),
                title: `Seksi ${this.totalSections + 1}`,
                description: "",
                questions: [],
            };

            this.questionnaire.sections.push(newSection);
            this.currentSectionIndex = this.questionnaire.sections.length - 1;
            this.currentQuestionIndex = -1;
            this.selectedComponent = { type: "section", id: newSection.id };

            this.saveQuestionnaire();
        },

        addQuestion(questionType) {
            if (!this.canAddQuestion) {
                this.addSection();
            }

            const newQuestion = this.createQuestion(questionType);

            this.questionnaire.sections[
                this.currentSectionIndex
            ].questions.push(newQuestion);
            this.currentQuestionIndex =
                this.questionnaire.sections[this.currentSectionIndex].questions
                    .length - 1;
            this.selectedComponent = { type: "question", id: newQuestion.id };

            this.saveQuestionnaire();
        },

        addQuestionAtPosition(questionType, position) {
            if (!this.canAddQuestion) {
                this.addSection();
                position = 0; // If we had to create a new section, force position to 0
            }

            const newQuestion = this.createQuestion(questionType);
            const sectionQuestions =
                this.questionnaire.sections[this.currentSectionIndex].questions;

            // Validate position - ensure it's within bounds
            if (position < 0) position = 0;
            if (position > sectionQuestions.length)
                position = sectionQuestions.length;

            // Insert question at the specified position
            sectionQuestions.splice(position, 0, newQuestion);

            // Update current question index and selected component
            this.currentQuestionIndex = position;
            this.selectedComponent = { type: "question", id: newQuestion.id };

            this.saveQuestionnaire();
        },

        createQuestion(type) {
            const baseQuestion = {
                id: uuidv4(),
                type: type,
                text: "Pertanyaan baru",
                helpText: "",
                required: false,
                visible: true,
            };

            // Tambahkan properti spesifik berdasarkan tipe pertanyaan
            switch (type) {
                case "short-text":
                case "long-text":
                    return {
                        ...baseQuestion,
                        placeholder: "",
                        maxLength: 0, // 0 berarti tidak ada batasan
                    };

                case "email":
                    return {
                        ...baseQuestion,
                        placeholder: "email@example.com",
                    };

                case "phone":
                    return {
                        ...baseQuestion,
                        placeholder: "+62",
                        format: "international",
                    };

                case "number":
                    return {
                        ...baseQuestion,
                        placeholder: "0",
                        min: null,
                        max: null,
                        step: 1,
                    };

                case "date":
                    return {
                        ...baseQuestion,
                        format: "DD/MM/YYYY",
                        minDate: null,
                        maxDate: null,
                    };

                case "radio":
                case "checkbox":
                case "dropdown":
                    return {
                        ...baseQuestion,
                        options: [
                            { id: uuidv4(), text: "Opsi 1", value: "option_1" },
                            { id: uuidv4(), text: "Opsi 2", value: "option_2" },
                            { id: uuidv4(), text: "Opsi 3", value: "option_3" },
                        ],
                        allowOther: false,
                    };

                case "rating":
                    return {
                        ...baseQuestion,
                        maxRating: 5,
                        labels: {
                            1: "Sangat Buruk",
                            5: "Sangat Baik",
                        },
                    };

                case "likert":
                    return {
                        ...baseQuestion,
                        statements: [{ id: uuidv4(), text: "Pernyataan 1" }],
                        scale: [
                            { value: 1, label: "Sangat Tidak Setuju" },
                            { value: 2, label: "Tidak Setuju" },
                            { value: 3, label: "Netral" },
                            { value: 4, label: "Setuju" },
                            { value: 5, label: "Sangat Setuju" },
                        ],
                    };

                case "yes-no":
                    return {
                        ...baseQuestion,
                        yesLabel: "Ya",
                        noLabel: "Tidak",
                    };

                case "file-upload":
                    return {
                        ...baseQuestion,
                        allowedTypes: ["image/*", "application/pdf"],
                        maxSize: 5, // MB
                        maxFiles: 1,
                    };

                case "matrix":
                    return {
                        ...baseQuestion,
                        rows: [
                            { id: uuidv4(), text: "Baris 1" },
                            { id: uuidv4(), text: "Baris 2" },
                        ],
                        columns: [
                            { id: uuidv4(), text: "Kolom 1" },
                            { id: uuidv4(), text: "Kolom 2" },
                            { id: uuidv4(), text: "Kolom 3" },
                        ],
                        matrixType: "radio", // atau 'checkbox'
                    };

                case "slider":
                    return {
                        ...baseQuestion,
                        min: 0,
                        max: 100,
                        step: 1,
                        showTicks: true,
                        showLabels: true,
                        labels: {
                            0: "Minimum",
                            100: "Maximum",
                        },
                    };

                case "ranking":
                    return {
                        ...baseQuestion,
                        options: [
                            { id: uuidv4(), text: "Item 1" },
                            { id: uuidv4(), text: "Item 2" },
                            { id: uuidv4(), text: "Item 3" },
                        ],
                    };

                default:
                    return baseQuestion;
            }
        },

        duplicateQuestion(questionId) {
            const sectionIndex = this.questionnaire.sections.findIndex(
                (section) => section.questions.some((q) => q.id === questionId)
            );

            if (sectionIndex < 0) return;

            const questionIndex = this.questionnaire.sections[
                sectionIndex
            ].questions.findIndex((q) => q.id === questionId);
            const originalQuestion =
                this.questionnaire.sections[sectionIndex].questions[
                    questionIndex
                ];

            const duplicatedQuestion = {
                ...JSON.parse(JSON.stringify(originalQuestion)),
                id: uuidv4(),
                text: `${originalQuestion.text} (copy)`,
            };

            this.questionnaire.sections[sectionIndex].questions.splice(
                questionIndex + 1,
                0,
                duplicatedQuestion
            );
            this.currentSectionIndex = sectionIndex;
            this.currentQuestionIndex = questionIndex + 1;
            this.selectedComponent = {
                type: "question",
                id: duplicatedQuestion.id,
            };

            this.saveQuestionnaire();
        },

        deleteQuestion(questionId) {
            const sectionIndex = this.questionnaire.sections.findIndex(
                (section) => section.questions.some((q) => q.id === questionId)
            );

            if (sectionIndex < 0) return;

            const questionIndex = this.questionnaire.sections[
                sectionIndex
            ].questions.findIndex((q) => q.id === questionId);

            this.questionnaire.sections[sectionIndex].questions.splice(
                questionIndex,
                1
            );

            if (
                this.selectedComponent?.type === "question" &&
                this.selectedComponent.id === questionId
            ) {
                this.selectedComponent = null;
                this.currentQuestionIndex = -1;
            }

            this.saveQuestionnaire();
        },

        updateQuestion(questionId, updates) {
            this.specialHandlingForFileUpload(updates);

            const sectionIndex = this.questionnaire.sections.findIndex(
                (section) => section.questions.some((q) => q.id === questionId)
            );

            if (sectionIndex < 0) return;

            const questionIndex = this.questionnaire.sections[
                sectionIndex
            ].questions.findIndex((q) => q.id === questionId);

            this.questionnaire.sections[sectionIndex].questions[questionIndex] =
                {
                    ...this.questionnaire.sections[sectionIndex].questions[
                        questionIndex
                    ],
                    ...updates,
                };

            this.saveQuestionnaire();
        },

        duplicateSection(sectionId) {
            const sectionIndex = this.questionnaire.sections.findIndex(
                (s) => s.id === sectionId
            );

            if (sectionIndex < 0) return;

            const originalSection = this.questionnaire.sections[sectionIndex];

            const duplicatedSection = {
                ...JSON.parse(JSON.stringify(originalSection)),
                id: uuidv4(),
                title: `${originalSection.title} (copy)`,
                questions: originalSection.questions.map((q) => ({
                    ...q,
                    id: uuidv4(),
                })),
            };

            this.questionnaire.sections.splice(
                sectionIndex + 1,
                0,
                duplicatedSection
            );
            this.currentSectionIndex = sectionIndex + 1;
            this.currentQuestionIndex = -1;
            this.selectedComponent = {
                type: "section",
                id: duplicatedSection.id,
            };

            this.saveQuestionnaire();
        },

        deleteSection(sectionId) {
            const sectionIndex = this.questionnaire.sections.findIndex(
                (s) => s.id === sectionId
            );

            if (sectionIndex < 0) return;

            this.questionnaire.sections.splice(sectionIndex, 1);

            if (
                this.selectedComponent?.type === "section" &&
                this.selectedComponent.id === sectionId
            ) {
                this.selectedComponent = null;
            }

            if (
                this.currentSectionIndex >= this.questionnaire.sections.length
            ) {
                this.currentSectionIndex = Math.max(
                    0,
                    this.questionnaire.sections.length - 1
                );
            }

            this.currentQuestionIndex = -1;

            this.saveQuestionnaire();
        },

        updateSection(sectionId, updates) {
            const sectionIndex = this.questionnaire.sections.findIndex(
                (s) => s.id === sectionId
            );

            if (sectionIndex < 0) return;

            this.questionnaire.sections[sectionIndex] = {
                ...this.questionnaire.sections[sectionIndex],
                ...updates,
            };

            this.saveQuestionnaire();
        },

        reorderSections(newOrder) {
            // Asumsikan newOrder adalah array indeks baru untuk seksi
            const reorderedSections = newOrder.map(
                (index) => this.questionnaire.sections[index]
            );
            this.questionnaire.sections = reorderedSections;

            this.saveQuestionnaire();
        },

        reorderQuestions(sectionId, newOrder) {
            const sectionIndex = this.questionnaire.sections.findIndex(
                (s) => s.id === sectionId
            );

            if (sectionIndex < 0) return;

            const reorderedQuestions = newOrder.map(
                (index) =>
                    this.questionnaire.sections[sectionIndex].questions[index]
            );

            this.questionnaire.sections[sectionIndex].questions =
                reorderedQuestions;
            this.saveQuestionnaire();
        },

        selectComponent(type, id) {
            this.selectedComponent = { type, id };

            if (type === "section") {
                const sectionIndex = this.questionnaire.sections.findIndex(
                    (s) => s.id === id
                );
                if (sectionIndex >= 0) {
                    this.currentSectionIndex = sectionIndex;
                    this.currentQuestionIndex = -1;
                }
            } else if (type === "question") {
                // Temukan seksi dan pertanyaan yang sesuai dengan ID
                for (let i = 0; i < this.questionnaire.sections.length; i++) {
                    const questionIndex = this.questionnaire.sections[
                        i
                    ].questions.findIndex((q) => q.id === id);
                    if (questionIndex >= 0) {
                        this.currentSectionIndex = i;
                        this.currentQuestionIndex = questionIndex;
                        break;
                    }
                }
            }
        },

        updateQuestionnaireSettings(settings) {
            this.questionnaire = {
                ...this.questionnaire,
                ...settings,
            };

            this.saveQuestionnaire();
        },

        updateWelcomeScreen(updates) {
            this.questionnaire.welcomeScreen = {
                ...this.questionnaire.welcomeScreen,
                ...updates,
            };

            this.saveQuestionnaire();
        },

        updateThankYouScreen(updates) {
            this.questionnaire.thankYouScreen = {
                ...this.questionnaire.thankYouScreen,
                ...updates,
            };

            this.saveQuestionnaire();
        },

        saveQuestionnaire() {
            this.saveStatus = "saving";
            this.errorMessage = null;
            this.validationErrors = null;
            console.log("Saving questionnaire state:", this.questionnaire);

            // Helper function to check if an ID is temporary (either starts with temp_ or is a UUID)
            const isTemporaryId = (id) => {
                if (!id) return true;
                if (typeof id === "string" && id.startsWith("temp_"))
                    return true;
                // Check if it's a UUID format (simple check for presence of hyphens and length)
                if (
                    typeof id === "string" &&
                    id.includes("-") &&
                    id.length > 30
                )
                    return true;
                return false;
            };

            // Save in localStorage for backup
            try {
                localStorage.setItem(
                    "questionnaire_draft",
                    JSON.stringify(this.questionnaire)
                );
            } catch (error) {
                console.error("Failed to save in localStorage:", error);
            }

            // Determine if this is a create or update operation
            const isCreate =
                !this.questionnaire.id || isTemporaryId(this.questionnaire.id);

            // Track original values for detecting changes
            const originalSlug = this.originalQuestionnaire?.slug || null;
            const currentSlug = this.questionnaire.slug || null;

            // Prepare data for API - only include necessary fields
            const questionnaireData = {
                title: this.questionnaire.title,
                description: this.questionnaire.description || null,
                settings: JSON.stringify({
                    showProgressBar: this.questionnaire.showProgressBar,
                    showPageNumbers: this.questionnaire.showPageNumbers,
                    requiresLogin: this.questionnaire.requiresLogin,
                    welcomeScreen: this.questionnaire.welcomeScreen,
                    thankYouScreen: this.questionnaire.thankYouScreen,
                }),
                // Include sections separately
                sections: this.questionnaire.sections.map((section) => ({
                    id: !isTemporaryId(section.id) ? section.id : undefined,
                    title: section.title,
                    description: section.description,
                    order: section.order,
                    questions: section.questions.map((question) => ({
                        id: !isTemporaryId(question.id)
                            ? question.id
                            : undefined,
                        question_type: this.mapQuestionType(question.type),
                        title: question.text,
                        description: question.helpText,
                        is_required: question.required,
                        order: question.order,
                        settings: JSON.stringify(question.settings || {}),
                        options: question.options
                            ? question.options.map((option) => ({
                                  id: !isTemporaryId(option.id)
                                      ? option.id
                                      : undefined,
                                  value: option.value || option.text,
                                  label: option.text,
                                  order: option.order,
                              }))
                            : [],
                    })),
                })),
            };

            // Only include slug for new questionnaires or if explicitly changed
            if (isCreate || (currentSlug !== originalSlug && currentSlug)) {
                questionnaireData.slug = currentSlug;
                console.log(
                    `Including slug in request: ${currentSlug} (create: ${isCreate}, changed: ${
                        currentSlug !== originalSlug
                    })`
                );
            } else {
                console.log(
                    `Not including slug in update request (original: ${originalSlug}, current: ${currentSlug})`
                );
            }

            // Log the data being sent
            console.log("Saving questionnaire data:", questionnaireData);

            // Make sure we have a valid ID for updates - IMPORTANT FIX
            let questionnaireId = this.questionnaire.id;
            if (
                !isCreate &&
                (questionnaireId === null || questionnaireId === undefined)
            ) {
                console.error(
                    "Error: Attempting to update questionnaire without valid ID"
                );
                this.errorMessage =
                    "Tidak dapat menyimpan kuesioner: ID tidak valid";
                this.saveStatus = "error";
                return Promise.reject({
                    success: false,
                    message: this.errorMessage,
                });
            }

            // Ensure numeric ID for API requests
            if (!isCreate && questionnaireId) {
                // Convert string IDs to numbers if they're numeric
                if (
                    typeof questionnaireId === "string" &&
                    /^\d+$/.test(questionnaireId)
                ) {
                    questionnaireId = parseInt(questionnaireId, 10);
                    console.log(
                        "Converted string ID to number:",
                        questionnaireId
                    );
                }
            }

            const url = isCreate
                ? "/kuesioner"
                : `/kuesioner/${questionnaireId}`;
            const method = isCreate ? "post" : "put";

            console.log("Saving questionnaire:", {
                isCreate,
                id: questionnaireId,
                idType: typeof questionnaireId,
                url,
                method,
            });

            // Process all questions to ensure proper handling of special cases
            this.questionnaire.sections.forEach((section) => {
                section.questions.forEach((question) => {
                    this.specialHandlingForFileUpload(question);
                });
            });

            // Send request to server
            return axios({
                method: method,
                url: url,
                data: questionnaireData,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content"),
                },
            })
                .then((response) => {
                    console.log("Save response:", response.data);

                    if (response.data.success) {
                        // Update questionnaire ID if this is a new creation
                        if (isCreate && response.data.id) {
                            console.log(
                                `Updating ID from ${this.questionnaire.id} to ${
                                    response.data.id
                                } (${typeof response.data.id})`
                            );
                            this.questionnaire.id = response.data.id;

                            // Store original questionnaire data after successful create
                            this.originalQuestionnaire = JSON.parse(
                                JSON.stringify(this.questionnaire)
                            );
                        }

                        if (response.data.slug) {
                            this.questionnaire.slug = response.data.slug;
                            // Update original slug value when server returns a new one
                            if (this.originalQuestionnaire) {
                                this.originalQuestionnaire.slug =
                                    response.data.slug;
                            }
                        }

                        this.saveStatus = "saved";
                        this.lastSaved = new Date().toISOString();
                        return response.data;
                    } else {
                        console.error(
                            "Failed to save questionnaire:",
                            response.data.message || "No error message provided"
                        );
                        this.errorMessage =
                            response.data.message ||
                            "Failed to save questionnaire";
                        this.validationErrors = response.data.errors || null;
                        this.saveStatus = "error";
                        throw new Error(this.errorMessage);
                    }
                })
                .catch((error) => {
                    console.error("Error saving questionnaire:", error);

                    if (error.response) {
                        console.error(
                            "Response status:",
                            error.response.status
                        );
                        console.error(
                            "Response headers:",
                            error.response.headers
                        );

                        if (error.response.data) {
                            console.error(
                                "Response data:",
                                error.response.data
                            );

                            // Check if there are validation errors
                            if (error.response.data.errors) {
                                console.error(
                                    "Validation errors:",
                                    JSON.stringify(
                                        error.response.data.errors,
                                        null,
                                        2
                                    )
                                );
                                this.validationErrors =
                                    error.response.data.errors;
                                this.errorMessage =
                                    "Validasi gagal. Periksa data kuesioner.";
                            } else if (error.response.data.message) {
                                this.errorMessage = error.response.data.message;
                                this.validationErrors = null;
                            }

                            // Check if there are exception details
                            if (error.response.data.exception) {
                                console.error(
                                    "Exception:",
                                    error.response.data.exception
                                );
                                console.error(
                                    "File:",
                                    error.response.data.file
                                );
                                console.error(
                                    "Line:",
                                    error.response.data.line
                                );

                                // If we have exception details but no user-friendly message yet
                                if (!this.errorMessage) {
                                    this.errorMessage =
                                        "Terjadi kesalahan server. Silakan coba lagi nanti.";
                                }
                            }
                        }
                    } else if (error.request) {
                        // Request was made but no response received
                        console.error("No response received:", error.request);
                        this.errorMessage =
                            "Tidak ada respons dari server. Periksa koneksi internet Anda.";
                        this.validationErrors = null;
                    } else {
                        // Error in setting up the request
                        console.error(
                            "Error setting up request:",
                            error.message
                        );
                        this.errorMessage =
                            "Gagal mengirim permintaan: " + error.message;
                        this.validationErrors = null;
                    }

                    // If no specific error message has been set, use a default
                    if (!this.errorMessage) {
                        this.errorMessage =
                            "Gagal menyimpan kuesioner. Silakan coba lagi.";
                    }

                    this.saveStatus = "error";
                    throw error;
                });
        },

        // Helper method to map frontend question types to backend types
        mapQuestionType(frontendType) {
            const typeMap = {
                "short-text": "text",
                "long-text": "textarea",
                radio: "radio",
                checkbox: "checkbox",
                dropdown: "dropdown",
                rating: "rating",
                date: "date",
                "file-upload": "file",
                matrix: "matrix",
                email: "text",
                phone: "text",
                number: "text",
                "yes-no": "yes-no",
                slider: "rating",
                ranking: "matrix",
                likert: "likert",
            };

            return typeMap[frontendType] || "text"; // Default to text if mapping not found
        },

        setupAutosave() {
            // Hapus interval sebelumnya jika sudah ada
            if (this.autosaveInterval) {
                clearInterval(this.autosaveInterval);
            }

            // Set interval baru untuk auto-save setiap 1 menit
            this.autosaveInterval = setInterval(() => {
                this.saveQuestionnaire();
            }, 60000); // 60 detik
        },

        loadDraft() {
            try {
                const savedDraft = localStorage.getItem("questionnaire_draft");

                if (savedDraft) {
                    const parsedDraft = JSON.parse(savedDraft);
                    this.initializeQuestionnaire(parsedDraft);

                    // Set lastSaved agar user tahu kapan terakhir kali tersimpan
                    this.lastSaved = new Date().toISOString();
                    return true;
                }
            } catch (error) {
                console.error("Error loading draft from localStorage:", error);
            }

            return false;
        },

        exportQuestionnaire() {
            // Export questionnaire sebagai file JSON
            const dataStr = JSON.stringify(this.questionnaire, null, 2);
            const dataUri =
                "data:application/json;charset=utf-8," +
                encodeURIComponent(dataStr);

            const exportFileDefaultName = `questionnaire_${this.questionnaire.title.replace(
                /\s+/g,
                "_"
            )}.json`;

            const linkElement = document.createElement("a");
            linkElement.setAttribute("href", dataUri);
            linkElement.setAttribute("download", exportFileDefaultName);
            linkElement.click();
        },

        publishQuestionnaire() {
            // Persiapkan data untuk dikirim ke API
            const publishData = {
                start_date: this.questionnaire.startDate || null,
                end_date: this.questionnaire.endDate || null,
                slug: this.questionnaire.slug || null,
                title: this.questionnaire.title || null,
                // Include full questionnaire data to ensure sections and questions are properly saved
                settings: JSON.stringify({
                    showProgressBar: this.questionnaire.showProgressBar,
                    showPageNumbers: this.questionnaire.showPageNumbers,
                    requiresLogin: this.questionnaire.requiresLogin,
                    welcomeScreen: this.questionnaire.welcomeScreen,
                    thankYouScreen: this.questionnaire.thankYouScreen,
                    sections: this.questionnaire.sections.map((section) => ({
                        id:
                            section.id &&
                            (typeof section.id === "number" ||
                                !section.id.startsWith("temp_"))
                                ? section.id
                                : undefined,
                        title: section.title,
                        description: section.description,
                        order: section.order,
                        questions: section.questions.map((question) => ({
                            id:
                                question.id &&
                                (typeof question.id === "number" ||
                                    !question.id.startsWith("temp_"))
                                    ? question.id
                                    : undefined,
                            type: question.type,
                            text: question.text,
                            helpText: question.helpText,
                            required: question.required,
                            order: question.order,
                            options: question.options
                                ? question.options.map((option) => ({
                                      id:
                                          option.id &&
                                          (typeof option.id === "number" ||
                                              !option.id.startsWith("temp_"))
                                              ? option.id
                                              : undefined,
                                      value: option.value,
                                      text: option.text,
                                      order: option.order,
                                  }))
                                : [],
                        })),
                    })),
                }),
                // Include sections directly
                sections: this.questionnaire.sections.map((section) => ({
                    id:
                        section.id &&
                        (typeof section.id === "number" ||
                            !section.id.startsWith("temp_"))
                            ? section.id
                            : undefined,
                    title: section.title,
                    description: section.description,
                    order: section.order,
                    questions: section.questions.map((question) => ({
                        id:
                            question.id &&
                            (typeof question.id === "number" ||
                                !question.id.startsWith("temp_"))
                                ? question.id
                                : undefined,
                        question_type: this.mapQuestionType(question.type),
                        title: question.text,
                        description: question.helpText,
                        is_required: question.required,
                        order: question.order,
                        settings: JSON.stringify(question.settings || {}),
                        options: question.options
                            ? question.options.map((option) => ({
                                  id:
                                      option.id &&
                                      (typeof option.id === "number" ||
                                          !option.id.startsWith("temp_"))
                                          ? option.id
                                          : undefined,
                                  value: option.value,
                                  label: option.text,
                                  order: option.order,
                              }))
                            : [],
                    })),
                })),
            };

            // Validasi ID
            const questionnaireId = this.questionnaire.id;
            if (!questionnaireId) {
                console.error("Cannot publish: Questionnaire has no ID");
                return Promise.reject({
                    success: false,
                    message:
                        "Kuesioner belum disimpan. Simpan kuesioner terlebih dahulu.",
                });
            }

            // Periksa jika ID masih berupa ID sementara (string yang dimulai dengan 'temp_')
            if (
                typeof questionnaireId === "string" &&
                questionnaireId.startsWith("temp_")
            ) {
                console.error(
                    "Cannot publish: Questionnaire has only temporary ID"
                );
                return Promise.reject({
                    success: false,
                    message:
                        "Kuesioner masih dalam draft sementara. Simpan kuesioner terlebih dahulu.",
                });
            }

            console.log("Publishing questionnaire:", {
                id: questionnaireId,
                idType: typeof questionnaireId,
                data: publishData,
            });

            // Ambil CSRF token dengan cara yang lebih aman
            let csrfToken = null;
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                csrfToken = metaTag.getAttribute("content");
            } else {
                console.warn(
                    "CSRF token meta tag tidak ditemukan. Coba dengan token dari cookie."
                );
                // Coba dapatkan dari cookie sebagai fallback
                const cookies = document.cookie.split(";");
                for (let i = 0; i < cookies.length; i++) {
                    const cookie = cookies[i].trim();
                    if (cookie.startsWith("XSRF-TOKEN=")) {
                        csrfToken = decodeURIComponent(
                            cookie.substring("XSRF-TOKEN=".length)
                        );
                        break;
                    }
                }
            }

            // Persiapkan headers
            const headers = {
                "Content-Type": "application/json",
            };

            // Tambahkan CSRF token jika ditemukan
            if (csrfToken) {
                headers["X-CSRF-TOKEN"] = csrfToken;
            } else {
                console.error(
                    "Tidak dapat menemukan CSRF token. Request mungkin akan gagal."
                );
            }

            // Kirim permintaan ke API endpoint
            return axios
                .post(`/kuesioner/${questionnaireId}/publish`, publishData, {
                    headers,
                })
                .then((response) => {
                    console.log("Publish response:", response.data);
                    if (response.data.success) {
                        // Jika berhasil, perbarui status kuesioner di state lokal
                        this.questionnaire.status = "published";
                        this.questionnaire.is_published = true;
                        this.questionnaire.is_draft = false;

                        this.saveStatus = "Tersimpan";

                        // Buat URL lengkap untuk kuesioner yang dipublikasikan
                        let detailUrl = `/kuesioner/${this.questionnaire.id}`;
                        if (
                            response.data.questionnaire &&
                            response.data.questionnaire.slug
                        ) {
                            const slug = response.data.questionnaire.slug;
                            detailUrl = `/kuesioner/${this.questionnaire.id}/${slug}`;
                        }

                        // URL untuk akses publik ke kuesioner (using slug instead of ID)
                        let publicPath = `/kuesioner/${this.questionnaire.id}`;
                        if (
                            response.data.questionnaire &&
                            response.data.questionnaire.slug
                        ) {
                            const slug = this.slugify(
                                response.data.questionnaire.slug
                            );
                            publicPath = `/kuesioner/${slug}`;
                        }

                        return {
                            success: true,
                            url: detailUrl,
                            publicUrl: publicPath,
                            message:
                                response.data.message ||
                                "Kuesioner berhasil dipublikasikan",
                            questionnaire: response.data.questionnaire || null,
                        };
                    }
                    return {
                        success: false,
                        message:
                            response.data.message ||
                            "Gagal mempublikasikan kuesioner",
                    };
                })
                .catch((error) => {
                    console.error(
                        "Error publishing questionnaire:",
                        error.response?.data || error
                    );
                    return {
                        success: false,
                        message:
                            error.response?.data?.message ||
                            "Terjadi kesalahan saat mempublikasikan kuesioner",
                    };
                });
        },

        loadQuestionnaire(id) {
            if (this.autosaveInterval) {
                clearInterval(this.autosaveInterval);
                this.autosaveInterval = null;
            }

            // Reset state
            this.resetState();

            return axios
                .get(`/kuesioner/${id}`)
                .then((response) => {
                    const data = response.data;

                    if (data.questionnaire) {
                        this.setQuestionnaire(data.questionnaire);

                        // Store the original questionnaire for tracking changes
                        this.originalQuestionnaire = JSON.parse(
                            JSON.stringify(this.questionnaire)
                        );

                        // Setup autosave for existing questionnaires
                        this.setupAutosave();
                        return this.questionnaire;
                    } else {
                        throw new Error("Questionnaire data not found");
                    }
                })
                .catch((error) => {
                    console.error("Failed to load questionnaire:", error);
                    throw error;
                });
        },

        // Special handling for file upload questions with '*/*' allowedTypes
        specialHandlingForFileUpload(question) {
            if (
                question.type === "file-upload" &&
                question.allowedTypes &&
                Array.isArray(question.allowedTypes) &&
                question.allowedTypes.includes("*/*")
            ) {
                console.log("Store: Special handling for */* allowedTypes");

                // Ensure we're not mixing allowedTypes
                question.allowedTypes = ["*/*"];

                // Make sure settings also has the correct allowedTypes
                if (
                    question.settings &&
                    typeof question.settings === "object"
                ) {
                    question.settings.allowedTypes = ["*/*"];
                }
            }
        },

        slugify(text) {
            return slugify(text);
        },
    },
});
