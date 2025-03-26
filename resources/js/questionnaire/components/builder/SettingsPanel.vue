<template>
    <div
        class="settings-panel bg-white border-l border-gray-200 w-80 flex flex-col h-full"
    >
        <div
            class="p-4 border-b border-gray-200 flex items-center justify-between"
        >
            <h2 class="text-lg font-medium text-gray-900">{{ panelTitle }}</h2>
            <button
                type="button"
                class="text-gray-400 hover:text-gray-500"
                @click="$emit('close')"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <!-- Questionnaire Settings -->
            <div v-if="selectedComponent?.type === 'questionnaire'">
                <div class="space-y-6">
                    <div>
                        <label
                            for="title"
                            class="block text-sm font-medium text-gray-700"
                            >Judul Kuesioner</label
                        >
                        <input
                            type="text"
                            id="title"
                            v-model="questionnaireSettings.title"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestionnaireSettings"
                        />
                    </div>

                    <div>
                        <label
                            for="description"
                            class="block text-sm font-medium text-gray-700"
                            >Deskripsi</label
                        >
                        <textarea
                            id="description"
                            v-model="questionnaireSettings.description"
                            rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestionnaireSettings"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            for="slug"
                            class="block text-sm font-medium text-gray-700"
                            >Slug URL</label
                        >
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span
                                class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm"
                            >
                                /kuesioner/
                            </span>
                            <input
                                type="text"
                                id="slug"
                                v-model="questionnaireSettings.slug"
                                class="flex-1 block w-full rounded-none rounded-r-md border border-gray-300 py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                @change="updateQuestionnaireSettings"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                for="start-date"
                                class="block text-sm font-medium text-gray-700"
                                >Tanggal Mulai</label
                            >
                            <input
                                type="date"
                                id="start-date"
                                v-model="questionnaireSettings.startDate"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                @change="updateQuestionnaireSettings"
                            />
                        </div>

                        <div>
                            <label
                                for="end-date"
                                class="block text-sm font-medium text-gray-700"
                                >Tanggal Selesai</label
                            >
                            <input
                                type="date"
                                id="end-date"
                                v-model="questionnaireSettings.endDate"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                @change="updateQuestionnaireSettings"
                            />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-700">
                            Opsi Tampilan
                        </h3>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="show-progress-bar"
                                v-model="questionnaireSettings.showProgressBar"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                @change="updateQuestionnaireSettings"
                            />
                            <label
                                for="show-progress-bar"
                                class="ml-2 block text-sm text-gray-700"
                            >
                                Tampilkan Progress Bar
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="show-page-numbers"
                                v-model="questionnaireSettings.showPageNumbers"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                @change="updateQuestionnaireSettings"
                            />
                            <label
                                for="show-page-numbers"
                                class="ml-2 block text-sm text-gray-700"
                            >
                                Tampilkan Nomor Halaman
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="requires-login"
                                v-model="questionnaireSettings.requiresLogin"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                @change="updateQuestionnaireSettings"
                            />
                            <label
                                for="requires-login"
                                class="ml-2 block text-sm text-gray-700"
                            >
                                Wajib Login Alumni
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Screen Settings -->
            <div v-else-if="selectedComponent?.type === 'welcome'">
                <div class="space-y-6">
                    <div>
                        <label
                            for="welcome-title"
                            class="block text-sm font-medium text-gray-700"
                            >Judul</label
                        >
                        <input
                            type="text"
                            id="welcome-title"
                            v-model="welcomeScreenSettings.title"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateWelcomeScreen"
                        />
                    </div>

                    <div>
                        <label
                            for="welcome-description"
                            class="block text-sm font-medium text-gray-700"
                            >Deskripsi</label
                        >
                        <textarea
                            id="welcome-description"
                            v-model="welcomeScreenSettings.description"
                            rows="4"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateWelcomeScreen"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Thank You Screen Settings -->
            <div v-else-if="selectedComponent?.type === 'thankYou'">
                <div class="space-y-6">
                    <div>
                        <label
                            for="thank-you-title"
                            class="block text-sm font-medium text-gray-700"
                            >Judul</label
                        >
                        <input
                            type="text"
                            id="thank-you-title"
                            v-model="thankYouScreenSettings.title"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateThankYouScreen"
                        />
                    </div>

                    <div>
                        <label
                            for="thank-you-description"
                            class="block text-sm font-medium text-gray-700"
                            >Deskripsi</label
                        >
                        <textarea
                            id="thank-you-description"
                            v-model="thankYouScreenSettings.description"
                            rows="4"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateThankYouScreen"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Section Settings -->
            <div v-else-if="selectedComponent?.type === 'section'">
                <div class="space-y-6">
                    <div>
                        <label
                            for="section-title"
                            class="block text-sm font-medium text-gray-700"
                            >Judul Seksi</label
                        >
                        <input
                            type="text"
                            id="section-title"
                            v-model="sectionSettings.title"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateSection"
                        />
                    </div>

                    <div>
                        <label
                            for="section-description"
                            class="block text-sm font-medium text-gray-700"
                            >Deskripsi</label
                        >
                        <textarea
                            id="section-description"
                            v-model="sectionSettings.description"
                            rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateSection"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            for="questions-per-page"
                            class="block text-sm font-medium text-gray-700"
                            >Jumlah Pertanyaan Per Halaman</label
                        >
                        <select
                            id="questions-per-page"
                            v-model="sectionSettings.questionsPerPage"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                            @change="updateSection"
                        >
                            <option value="all">Semua (Satu Halaman)</option>
                            <option :value="1">1 Pertanyaan</option>
                            <option :value="2">2 Pertanyaan</option>
                            <option :value="3">3 Pertanyaan</option>
                            <option :value="5">5 Pertanyaan</option>
                            <option :value="10">10 Pertanyaan</option>
                        </select>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="duplicateSection"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5 text-gray-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                />
                            </svg>
                            Duplikasi Seksi
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            @click="deleteSection"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            Hapus Seksi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Question Settings -->
            <div v-else-if="selectedComponent?.type === 'question'">
                <div class="space-y-6">
                    <div>
                        <label
                            for="question-text"
                            class="block text-sm font-medium text-gray-700"
                            >Teks Pertanyaan</label
                        >
                        <input
                            type="text"
                            id="question-text"
                            v-model="questionSettings.text"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        />
                    </div>

                    <div>
                        <label
                            for="help-text"
                            class="block text-sm font-medium text-gray-700"
                            >Teks Bantuan</label
                        >
                        <textarea
                            id="help-text"
                            v-model="questionSettings.helpText"
                            rows="2"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @change="updateQuestion"
                        ></textarea>
                    </div>

                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="required"
                            v-model="questionSettings.required"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            @change="updateQuestion"
                        />
                        <label
                            for="required"
                            class="ml-2 block text-sm text-gray-700"
                        >
                            Wajib Diisi
                        </label>
                    </div>

                    <!-- Text type specific settings -->
                    <div
                        v-if="
                            ['short-text', 'long-text'].includes(
                                currentQuestion?.type
                            )
                        "
                        class="border-t border-gray-200 pt-4"
                    >
                        <div>
                            <label
                                for="placeholder"
                                class="block text-sm font-medium text-gray-700"
                                >Placeholder</label
                            >
                            <input
                                type="text"
                                id="placeholder"
                                v-model="questionSettings.placeholder"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                @change="updateQuestion"
                            />
                        </div>

                        <div
                            v-if="currentQuestion?.type === 'long-text'"
                            class="mt-3"
                        >
                            <label
                                for="rows"
                                class="block text-sm font-medium text-gray-700"
                                >Jumlah Baris</label
                            >
                            <input
                                type="number"
                                id="rows"
                                v-model.number="questionSettings.rows"
                                min="2"
                                max="10"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                @change="updateQuestion"
                            />
                        </div>

                        <div class="mt-3">
                            <label
                                for="max-length"
                                class="block text-sm font-medium text-gray-700"
                                >Panjang Maksimum (0 = tidak dibatasi)</label
                            >
                            <input
                                type="number"
                                id="max-length"
                                v-model.number="questionSettings.maxLength"
                                min="0"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                @change="updateQuestion"
                            />
                        </div>
                    </div>

                    <!-- Number type specific settings -->
                    <div
                        v-else-if="currentQuestion?.type === 'number'"
                        class="border-t border-gray-200 pt-4"
                    >
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="min-value"
                                    class="block text-sm font-medium text-gray-700"
                                    >Nilai Minimum</label
                                >
                                <input
                                    type="number"
                                    id="min-value"
                                    v-model.number="questionSettings.min"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    @change="updateQuestion"
                                />
                            </div>

                            <div>
                                <label
                                    for="max-value"
                                    class="block text-sm font-medium text-gray-700"
                                    >Nilai Maksimum</label
                                >
                                <input
                                    type="number"
                                    id="max-value"
                                    v-model.number="questionSettings.max"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    @change="updateQuestion"
                                />
                            </div>
                        </div>

                        <div class="mt-3">
                            <label
                                for="step"
                                class="block text-sm font-medium text-gray-700"
                                >Langkah</label
                            >
                            <input
                                type="number"
                                id="step"
                                v-model.number="questionSettings.step"
                                min="0.001"
                                step="0.001"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                @change="updateQuestion"
                            />
                        </div>
                    </div>

                    <!-- Choice type specific settings (radio, checkbox, dropdown) -->
                    <div
                        v-else-if="
                            ['radio', 'checkbox', 'dropdown'].includes(
                                currentQuestion?.type
                            )
                        "
                        class="border-t border-gray-200 pt-4"
                    >
                        <h3 class="text-sm font-medium text-gray-700 mb-3">
                            Opsi Jawaban
                        </h3>

                        <div class="space-y-3">
                            <div
                                v-for="(
                                    option, index
                                ) in questionSettings.options"
                                :key="option.id"
                                class="flex"
                            >
                                <div class="flex-grow">
                                    <input
                                        type="text"
                                        :id="`option-${index}`"
                                        v-model="option.text"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Teks Opsi"
                                        @change="updateQuestion"
                                    />
                                </div>

                                <div class="flex items-center ml-2">
                                    <button
                                        type="button"
                                        class="text-gray-400 hover:text-red-500"
                                        @click="removeOption(index)"
                                        :disabled="
                                            questionSettings.options.length <= 1
                                        "
                                        :class="{
                                            'opacity-30 cursor-not-allowed':
                                                questionSettings.options
                                                    .length <= 1,
                                        }"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button
                                type="button"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                @click="addOption"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="-ml-1 mr-2 h-5 w-5 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                Tambah Opsi
                            </button>
                        </div>

                        <div class="mt-4 flex items-center">
                            <input
                                type="checkbox"
                                id="allow-other"
                                v-model="questionSettings.allowOther"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                @change="updateQuestion"
                            />
                            <label
                                for="allow-other"
                                class="ml-2 block text-sm text-gray-700"
                            >
                                Tampilkan Opsi "Lainnya"
                            </label>
                        </div>

                        <!-- Checkbox specific settings -->
                        <div
                            v-if="currentQuestion?.type === 'checkbox'"
                            class="mt-4 border-t border-gray-200 pt-4"
                        >
                            <h3 class="text-sm font-medium text-gray-700 mb-3">
                                Batas Pemilihan
                            </h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        for="min-selected"
                                        class="block text-sm font-medium text-gray-700"
                                        >Minimum</label
                                    >
                                    <input
                                        type="number"
                                        id="min-selected"
                                        v-model.number="
                                            questionSettings.minSelected
                                        "
                                        min="0"
                                        :max="questionSettings.options.length"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        @change="updateQuestion"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="max-selected"
                                        class="block text-sm font-medium text-gray-700"
                                        >Maksimum</label
                                    >
                                    <input
                                        type="number"
                                        id="max-selected"
                                        v-model.number="
                                            questionSettings.maxSelected
                                        "
                                        min="0"
                                        :max="questionSettings.options.length"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        @change="updateQuestion"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rating type specific settings -->
                    <div
                        v-else-if="currentQuestion?.type === 'rating'"
                        class="border-t border-gray-200 pt-4"
                    >
                        <div>
                            <label
                                for="max-rating"
                                class="block text-sm font-medium text-gray-700"
                                >Jumlah Bintang</label
                            >
                            <select
                                id="max-rating"
                                v-model.number="questionSettings.maxRating"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                @change="updateQuestionAndRatingLabels"
                            >
                                <option :value="3">3 Bintang</option>
                                <option :value="5">5 Bintang</option>
                                <option :value="7">7 Bintang</option>
                                <option :value="10">10 Bintang</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">
                                Label Bintang
                            </h3>

                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            :for="`rating-label-1`"
                                            class="block text-xs font-medium text-gray-500"
                                            >1 Bintang</label
                                        >
                                        <input
                                            type="text"
                                            :id="`rating-label-1`"
                                            v-model="questionSettings.labels[1]"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 text-xs focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Label Terendah"
                                            @change="updateQuestion"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            :for="`rating-label-max`"
                                            class="block text-xs font-medium text-gray-500"
                                            >{{
                                                questionSettings.maxRating
                                            }}
                                            Bintang</label
                                        >
                                        <input
                                            type="text"
                                            :id="`rating-label-max`"
                                            v-model="
                                                questionSettings.labels[
                                                    questionSettings.maxRating
                                                ]
                                            "
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 text-xs focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Label Tertinggi"
                                            @change="updateQuestion"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-between space-x-3">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="duplicateQuestion"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5 text-gray-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                />
                            </svg>
                            Duplikasi Pertanyaan
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            @click="deleteQuestion"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            Hapus Pertanyaan
                        </button>
                    </div>
                </div>
            </div>

            <!-- No component selected -->
            <div v-else>
                <div
                    class="py-6 flex flex-col items-center justify-center text-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-12 w-12 text-gray-400 mb-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"
                        />
                    </svg>
                    <h3 class="text-sm font-medium text-gray-900">
                        Pilih komponen untuk mengedit
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Klik pada seksi, pertanyaan, atau elemen lainnya untuk
                        menyunting.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, watch } from "vue";
import { useQuestionnaireStore } from "../../store/questionnaire";
import { v4 as uuidv4 } from "uuid";

const props = defineProps({
    selectedComponent: {
        type: Object,
        default: null,
    },
    questionnaire: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "update:questionnaire",
    "duplicate-section",
    "delete-section",
    "duplicate-question",
    "delete-question",
    "close",
]);

const store = useQuestionnaireStore();

// Computed panel title based on selected component
const panelTitle = computed(() => {
    if (!props.selectedComponent) return "Pengaturan";

    switch (props.selectedComponent.type) {
        case "questionnaire":
            return "Pengaturan Kuesioner";
        case "welcome":
            return "Halaman Pembuka";
        case "thankYou":
            return "Halaman Terima Kasih";
        case "section":
            return "Pengaturan Seksi";
        case "question":
            return "Pengaturan Pertanyaan";
        default:
            return "Pengaturan";
    }
});

// Current question reference (if question is selected)
const currentQuestion = computed(() => {
    if (props.selectedComponent?.type !== "question") return null;

    // Find question in questionnaire
    for (const section of props.questionnaire.sections) {
        const question = section.questions.find(
            (q) => q.id === props.selectedComponent.id
        );
        if (question) return question;
    }

    return null;
});

// Settings state for different component types
const questionnaireSettings = ref({
    title: props.questionnaire.title,
    description: props.questionnaire.description,
    slug: props.questionnaire.slug,
    startDate: props.questionnaire.startDate,
    endDate: props.questionnaire.endDate,
    showProgressBar: props.questionnaire.showProgressBar,
    showPageNumbers: props.questionnaire.showPageNumbers,
    requiresLogin: props.questionnaire.requiresLogin,
});

const welcomeScreenSettings = ref({
    title: props.questionnaire.welcomeScreen.title,
    description: props.questionnaire.welcomeScreen.description,
});

const thankYouScreenSettings = ref({
    title: props.questionnaire.thankYouScreen.title,
    description: props.questionnaire.thankYouScreen.description,
});

const sectionSettings = ref({
    title: "",
    description: "",
    questionsPerPage: "all",
});

const questionSettings = ref({
    text: "",
    helpText: "",
    required: false,
    // Additional properties will be set based on question type
});

// Update settings when selected component changes
watch(
    () => props.selectedComponent,
    (newVal) => {
        if (!newVal) return;

        if (newVal.type === "questionnaire") {
            questionnaireSettings.value = {
                title: props.questionnaire.title,
                description: props.questionnaire.description,
                slug: props.questionnaire.slug,
                startDate: props.questionnaire.startDate,
                endDate: props.questionnaire.endDate,
                showProgressBar: props.questionnaire.showProgressBar,
                showPageNumbers: props.questionnaire.showPageNumbers,
                requiresLogin: props.questionnaire.requiresLogin,
            };
        } else if (newVal.type === "welcome") {
            welcomeScreenSettings.value = {
                title: props.questionnaire.welcomeScreen.title,
                description: props.questionnaire.welcomeScreen.description,
            };
        } else if (newVal.type === "thankYou") {
            thankYouScreenSettings.value = {
                title: props.questionnaire.thankYouScreen.title,
                description: props.questionnaire.thankYouScreen.description,
            };
        } else if (newVal.type === "section") {
            const section = props.questionnaire.sections.find(
                (s) => s.id === newVal.id
            );
            if (section) {
                sectionSettings.value = {
                    title: section.title,
                    description: section.description,
                    questionsPerPage: section.questionsPerPage || "all",
                };
            }
        } else if (newVal.type === "question") {
            const question = currentQuestion.value;
            if (question) {
                // Base settings for all question types
                questionSettings.value = {
                    text: question.text,
                    helpText: question.helpText || "",
                    required: question.required || false,
                };

                // Additional settings based on question type
                switch (question.type) {
                    case "short-text":
                    case "long-text":
                        questionSettings.value.placeholder =
                            question.placeholder || "";
                        questionSettings.value.maxLength =
                            question.maxLength || 0;
                        if (question.type === "long-text") {
                            questionSettings.value.rows = question.rows || 4;
                        }
                        break;

                    case "number":
                        questionSettings.value.min = question.min ?? null;
                        questionSettings.value.max = question.max ?? null;
                        questionSettings.value.step = question.step || 1;
                        break;

                    case "radio":
                    case "checkbox":
                    case "dropdown":
                        questionSettings.value.options = JSON.parse(
                            JSON.stringify(question.options || [])
                        );
                        questionSettings.value.allowOther =
                            question.allowOther || false;

                        if (question.type === "checkbox") {
                            questionSettings.value.minSelected =
                                question.minSelected || 0;
                            questionSettings.value.maxSelected =
                                question.maxSelected || 0;
                        }
                        break;

                    case "rating":
                        questionSettings.value.maxRating =
                            question.maxRating || 5;
                        questionSettings.value.labels = JSON.parse(
                            JSON.stringify(question.labels || {})
                        );
                        break;
                }
            }
        }
    },
    { immediate: true }
);

// Update methods
const updateQuestionnaireSettings = () => {
    emit("update:questionnaire", {
        ...props.questionnaire,
        title: questionnaireSettings.value.title,
        description: questionnaireSettings.value.description,
        slug: questionnaireSettings.value.slug,
        startDate: questionnaireSettings.value.startDate,
        endDate: questionnaireSettings.value.endDate,
        showProgressBar: questionnaireSettings.value.showProgressBar,
        showPageNumbers: questionnaireSettings.value.showPageNumbers,
        requiresLogin: questionnaireSettings.value.requiresLogin,
    });
};

const updateWelcomeScreen = () => {
    emit("update:questionnaire", {
        ...props.questionnaire,
        welcomeScreen: {
            title: welcomeScreenSettings.value.title,
            description: welcomeScreenSettings.value.description,
        },
    });
};

const updateThankYouScreen = () => {
    emit("update:questionnaire", {
        ...props.questionnaire,
        thankYouScreen: {
            title: thankYouScreenSettings.value.title,
            description: thankYouScreenSettings.value.description,
        },
    });
};

const updateSection = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "section")
        return;

    const sectionId = props.selectedComponent.id;
    const sectionIndex = props.questionnaire.sections.findIndex(
        (s) => s.id === sectionId
    );

    if (sectionIndex === -1) return;

    const updatedSections = [...props.questionnaire.sections];
    updatedSections[sectionIndex] = {
        ...updatedSections[sectionIndex],
        title: sectionSettings.value.title,
        description: sectionSettings.value.description,
        questionsPerPage: sectionSettings.value.questionsPerPage,
    };

    emit("update:questionnaire", {
        ...props.questionnaire,
        sections: updatedSections,
    });
};

const updateQuestion = () => {
    if (
        !props.selectedComponent ||
        props.selectedComponent.type !== "question" ||
        !currentQuestion.value
    )
        return;

    const questionId = props.selectedComponent.id;
    let sectionIndex = -1;
    let questionIndex = -1;

    // Find question position
    props.questionnaire.sections.forEach((section, secIdx) => {
        const qIdx = section.questions.findIndex((q) => q.id === questionId);
        if (qIdx !== -1) {
            sectionIndex = secIdx;
            questionIndex = qIdx;
        }
    });

    if (sectionIndex === -1 || questionIndex === -1) return;

    // Create a copy of the questionnaire structure
    const updatedSections = [...props.questionnaire.sections];
    const originalQuestion =
        updatedSections[sectionIndex].questions[questionIndex];

    // Build the updated question based on type
    const updatedQuestion = { ...originalQuestion };

    // Update common properties
    updatedQuestion.text = questionSettings.value.text;
    updatedQuestion.helpText = questionSettings.value.helpText;
    updatedQuestion.required = questionSettings.value.required;

    // Update type-specific properties
    switch (originalQuestion.type) {
        case "short-text":
        case "long-text":
            updatedQuestion.placeholder = questionSettings.value.placeholder;
            updatedQuestion.maxLength = questionSettings.value.maxLength;
            if (originalQuestion.type === "long-text") {
                updatedQuestion.rows = questionSettings.value.rows;
            }
            break;

        case "number":
            updatedQuestion.min = questionSettings.value.min;
            updatedQuestion.max = questionSettings.value.max;
            updatedQuestion.step = questionSettings.value.step;
            break;

        case "radio":
        case "checkbox":
        case "dropdown":
            updatedQuestion.options = questionSettings.value.options;
            updatedQuestion.allowOther = questionSettings.value.allowOther;

            if (originalQuestion.type === "checkbox") {
                updatedQuestion.minSelected =
                    questionSettings.value.minSelected;
                updatedQuestion.maxSelected =
                    questionSettings.value.maxSelected;
            }
            break;

        case "rating":
            updatedQuestion.maxRating = questionSettings.value.maxRating;
            updatedQuestion.labels = questionSettings.value.labels;
            break;
    }

    // Apply the update
    updatedSections[sectionIndex].questions[questionIndex] = updatedQuestion;

    emit("update:questionnaire", {
        ...props.questionnaire,
        sections: updatedSections,
    });
};

// Handle rating labels when max rating changes
const updateQuestionAndRatingLabels = () => {
    if (currentQuestion.value?.type !== "rating") return;

    // Make sure labels object exists
    if (!questionSettings.value.labels) {
        questionSettings.value.labels = {};
    }

    // Ensure min and max labels exist
    if (!questionSettings.value.labels[1]) {
        questionSettings.value.labels[1] = "Sangat Buruk";
    }

    if (!questionSettings.value.labels[questionSettings.value.maxRating]) {
        questionSettings.value.labels[questionSettings.value.maxRating] =
            "Sangat Baik";
    }

    updateQuestion();
};

// Option management for choice questions
const addOption = () => {
    const newOption = {
        id: uuidv4(),
        text: `Opsi ${questionSettings.value.options.length + 1}`,
        value: `option_${questionSettings.value.options.length + 1}`,
    };

    questionSettings.value.options.push(newOption);
    updateQuestion();
};

const removeOption = (index) => {
    if (questionSettings.value.options.length <= 1) return;

    questionSettings.value.options.splice(index, 1);
    updateQuestion();
};

// Section management
const duplicateSection = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "section")
        return;

    emit("duplicate-section", props.selectedComponent.id);
};

const deleteSection = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "section")
        return;

    emit("delete-section", props.selectedComponent.id);
};

// Question management
const duplicateQuestion = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "question")
        return;

    emit("duplicate-question", props.selectedComponent.id);
};

const deleteQuestion = () => {
    if (!props.selectedComponent || props.selectedComponent.type !== "question")
        return;

    emit("delete-question", props.selectedComponent.id);
};
</script>

<style scoped>
.settings-panel {
    min-width: 20rem;
}
</style>
