import { defineStore } from "pinia";
import { v4 as uuidv4 } from "uuid";

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
        lastSaved: null,
        autosaveInterval: null,
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
            {
                id: "ranking",
                name: "Rangking",
                icon: "ranking",
                category: "lanjutan",
            },
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

            // Simulasi saving delay for UI feedback
            setTimeout(() => {
                try {
                    // Simpan kuesioner ke localStorage untuk auto-save
                    localStorage.setItem(
                        "questionnaire_draft",
                        JSON.stringify(this.questionnaire)
                    );

                    // TODO: Implement real API saving
                    // Jika memiliki API endpoint, bisa di-uncomment:
                    /*
          const saveData = async () => {
            try {
              const response = await fetch('/api/questionnaires', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.questionnaire)
              });
              
              if (!response.ok) {
                throw new Error('Failed to save questionnaire');
              }
              
              const data = await response.json();
              
              // Update questionnaire id jika ini kuesioner baru
              if (!this.questionnaire.id && data.id) {
                this.questionnaire.id = data.id;
              }
              
              this.saveStatus = 'saved';
              this.lastSaved = new Date().toISOString();
            } catch (error) {
              console.error('Error saving questionnaire:', error);
              this.saveStatus = 'error';
            }
          };
          
          saveData();
          */

                    // Untuk sementara, simulasi successful save
                    this.saveStatus = "saved";
                    this.lastSaved = new Date().toISOString();
                } catch (error) {
                    console.error("Error saving to localStorage:", error);
                    this.saveStatus = "error";
                }
            }, 500);
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
            // TODO: Implement real publishing logic
            console.log("Publishing questionnaire:", this.questionnaire);

            // Simulasi successful publish
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({
                        success: true,
                        url: `/kuesioner/${
                            this.questionnaire.slug || "preview"
                        }`,
                    });
                }, 1000);
            });
        },
    },
});
