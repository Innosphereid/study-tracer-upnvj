<template>
    <div
        class="question-wrapper p-4 border rounded-md transition-all"
        :class="{
            'border-indigo-500 bg-indigo-50': isSelected,
            'border-gray-200 hover:border-indigo-300 hover:shadow-sm':
                !isSelected,
            dragging: isDragging,
        }"
        @click.stop="selectQuestion"
        draggable="true"
        @dragstart="onDragStart"
        @dragend="onDragEnd"
    >
        <!-- Question Type Badge -->
        <div class="flex justify-between items-start mb-3">
            <div class="flex items-center">
                <div class="drag-handle mr-2 text-gray-400 cursor-move">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 8h16M4 16h16"
                        />
                    </svg>
                </div>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getTypeClass(question.type)"
                >
                    {{ getTypeLabel(question.type) }}
                </span>
                <span v-if="question.required" class="ml-2 text-red-500 text-xs"
                    >*Wajib</span
                >
            </div>

            <!-- Actions -->
            <div class="flex">
                <button
                    type="button"
                    class="p-1 text-gray-400 hover:text-indigo-500 focus:outline-none"
                    @click.stop="$emit('duplicate', question.id)"
                    title="Duplikasi Pertanyaan"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"
                        />
                        <path
                            d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z"
                        />
                    </svg>
                </button>
                <button
                    type="button"
                    class="p-1 text-gray-400 hover:text-red-500 focus:outline-none"
                    @click.stop="$emit('delete', question.id)"
                    title="Hapus Pertanyaan"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
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

        <!-- Question Text -->
        <div class="mb-2">
            <h4 class="text-sm font-medium text-gray-900">
                {{ question.text }}
            </h4>
            <p v-if="question.helpText" class="mt-1 text-xs text-gray-500">
                {{ question.helpText }}
            </p>
        </div>

        <!-- Question Preview -->
        <div class="question-preview">
            <!-- Short Text -->
            <div v-if="question.type === 'short-text'" class="mt-1">
                <input
                    type="text"
                    disabled
                    :placeholder="question.placeholder || 'Jawaban singkat'"
                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                />
            </div>

            <!-- Long Text -->
            <div v-else-if="question.type === 'long-text'" class="mt-1">
                <textarea
                    disabled
                    :placeholder="question.placeholder || 'Jawaban panjang'"
                    :rows="question.rows || 3"
                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                ></textarea>
            </div>

            <!-- Email -->
            <div v-else-if="question.type === 'email'" class="mt-1">
                <div class="relative rounded-md shadow-sm">
                    <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
                            />
                            <path
                                d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"
                            />
                        </svg>
                    </div>
                    <input
                        type="email"
                        disabled
                        :placeholder="
                            question.placeholder || 'email@example.com'
                        "
                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 pl-10 pr-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                    />
                </div>
                <div class="mt-1 flex items-center text-xs text-gray-500">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    Responden harus memasukkan alamat email yang valid
                </div>
            </div>

            <!-- Phone Number -->
            <div v-else-if="question.type === 'phone'" class="mt-1">
                <div class="relative rounded-md shadow-sm">
                    <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                            />
                        </svg>
                    </div>
                    <input
                        type="tel"
                        disabled
                        :placeholder="
                            question.placeholder || '+62 8xx-xxxx-xxxx'
                        "
                        value="+62 812-3456-7890"
                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 pl-10 pr-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                    />
                </div>
                <div class="mt-1 flex items-center text-xs text-gray-500">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    Format: kode negara + nomor telepon (mis. +62 812-3456-7890)
                </div>
            </div>

            <!-- Number -->
            <div v-else-if="question.type === 'number'" class="mt-1">
                <div class="relative rounded-md shadow-sm">
                    <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2a1 1 0 000 2h6a1 1 0 100-2H7zm6 7a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1zm-3 3a1 1 0 100 2h.01a1 1 0 100-2H10zm-4 1a1 1 0 011-1h.01a1 1 0 110 2H7a1 1 0 01-1-1zm1-4a1 1 0 100 2h.01a1 1 0 100-2H7zm2 1a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1zm4-4a1 1 0 100 2h.01a1 1 0 100-2H13zM9 9a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1zM7 8a1 1 0 000 2h.01a1 1 0 000-2H7z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <input
                        type="text"
                        disabled
                        :placeholder="question.placeholder || '0'"
                        value="42"
                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 pl-10 pr-12 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                    />
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                    >
                        <div class="flex flex-col">
                            <button
                                disabled
                                class="h-3 w-3 text-gray-400 focus:outline-none cursor-not-allowed"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <button
                                disabled
                                class="h-3 w-3 text-gray-400 focus:outline-none cursor-not-allowed"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-1 flex items-center text-xs text-gray-500">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    Hanya menerima input angka{{
                        question.min !== null || question.max !== null
                            ? ", dengan batasan: "
                            : ""
                    }}
                    <span v-if="question.min !== null"
                        >min: {{ question.min }}</span
                    >
                    <span v-if="question.min !== null && question.max !== null"
                        >,
                    </span>
                    <span v-if="question.max !== null"
                        >max: {{ question.max }}</span
                    >
                </div>
            </div>

            <!-- Date -->
            <div v-else-if="question.type === 'date'" class="mt-1">
                <div class="relative rounded-md shadow-sm">
                    <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <input
                        type="text"
                        disabled
                        value="01/01/2023"
                        :placeholder="question.format || 'DD/MM/YYYY'"
                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 pl-10 pr-3 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                    />
                </div>
                <div class="mt-1 flex items-center text-xs text-gray-500">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    Format tanggal: {{ question.format || "DD/MM/YYYY" }}
                    <span v-if="question.minDate || question.maxDate">
                        (Batasan:
                        <span v-if="question.minDate"
                            >dari {{ question.minDate }}</span
                        >
                        <span v-if="question.minDate && question.maxDate">
                            -
                        </span>
                        <span v-if="question.maxDate"
                            >sampai {{ question.maxDate }}</span
                        >)
                    </span>
                </div>
            </div>

            <!-- Radio options -->
            <div v-else-if="question.type === 'radio'" class="mt-2 space-y-2">
                <div
                    v-for="option in question.options"
                    :key="option.id"
                    class="flex items-center"
                >
                    <div class="flex items-center h-5">
                        <input
                            type="radio"
                            disabled
                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label class="font-medium text-gray-700">{{
                            option.text
                        }}</label>
                    </div>
                </div>
                <div v-if="question.allowOther" class="flex items-center">
                    <div class="flex items-center h-5">
                        <input
                            type="radio"
                            disabled
                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
                        />
                    </div>
                    <div class="ml-3 text-sm flex items-center">
                        <label class="font-medium text-gray-700 mr-2"
                            >Lainnya:</label
                        >
                        <input
                            type="text"
                            disabled
                            class="shadow-sm border border-gray-300 rounded-md py-1 px-2 bg-gray-50 text-gray-500 cursor-not-allowed text-xs"
                        />
                    </div>
                </div>
            </div>

            <!-- Checkbox options -->
            <div
                v-else-if="question.type === 'checkbox'"
                class="mt-2 space-y-2"
            >
                <div
                    v-for="option in question.options"
                    :key="option.id"
                    class="flex items-center"
                >
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            disabled
                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 rounded"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label class="font-medium text-gray-700">{{
                            option.text
                        }}</label>
                    </div>
                </div>
                <div v-if="question.allowOther" class="flex items-center">
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            disabled
                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 rounded"
                        />
                    </div>
                    <div class="ml-3 text-sm flex items-center">
                        <label class="font-medium text-gray-700 mr-2"
                            >Lainnya:</label
                        >
                        <input
                            type="text"
                            disabled
                            class="shadow-sm border border-gray-300 rounded-md py-1 px-2 bg-gray-50 text-gray-500 cursor-not-allowed text-xs"
                        />
                    </div>
                </div>
            </div>

            <!-- Dropdown -->
            <div v-else-if="question.type === 'dropdown'" class="mt-1">
                <select
                    disabled
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 bg-gray-50 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md cursor-not-allowed"
                >
                    <option>-- Pilih Opsi --</option>
                    <option v-for="option in question.options" :key="option.id">
                        {{ option.text }}
                    </option>
                    <option v-if="question.allowOther">Lainnya...</option>
                </select>
            </div>

            <!-- Rating -->
            <div v-else-if="question.type === 'rating'" class="mt-2">
                <div class="flex items-center justify-center space-x-1">
                    <template v-for="n in question.maxRating || 5" :key="n">
                        <span class="text-gray-300">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                        </span>
                    </template>
                </div>
            </div>

            <!-- Likert Scale -->
            <div v-else-if="question.type === 'likert'" class="mt-2">
                <div class="flex flex-col">
                    <!-- Sample statement row for preview -->
                    <div
                        class="mb-3 bg-gray-50 p-3 rounded-md border border-gray-200"
                    >
                        <!-- Statement text -->
                        <div class="mb-3 text-sm font-medium text-gray-700">
                            {{
                                question.statements &&
                                question.statements.length > 0
                                    ? question.statements[0].text
                                    : "Pernyataan sampel"
                            }}
                        </div>

                        <!-- Scale options in horizontal layout -->
                        <div
                            class="flex items-center justify-between w-full px-2"
                        >
                            <div
                                class="flex flex-1 justify-between overflow-x-auto py-1"
                            >
                                <!-- Generate scale options from the scale property -->
                                <template
                                    v-for="(option, index) in question.scale ||
                                    defaultLikertScale"
                                    :key="index"
                                >
                                    <div
                                        class="flex flex-col items-center mx-1"
                                    >
                                        <input
                                            type="radio"
                                            disabled
                                            class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 flex-shrink-0"
                                            :checked="index === 2"
                                        />
                                        <label
                                            class="text-xs text-gray-600 mt-1 text-center whitespace-nowrap"
                                            style="min-width: 20px"
                                        >
                                            {{ option.value }}
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Scale legend -->
                    <div class="flex items-center justify-between mb-2 px-2">
                        <span class="text-xs text-gray-500">
                            {{
                                question.scale && question.scale.length > 0
                                    ? question.scale[0].label
                                    : "Sangat Tidak Setuju"
                            }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{
                                question.scale && question.scale.length > 0
                                    ? question.scale[question.scale.length - 1]
                                          .label
                                    : "Sangat Setuju"
                            }}
                        </span>
                    </div>

                    <!-- Help text -->
                    <div class="mt-2 flex items-center text-xs text-gray-500">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>
                            Skala
                            {{ question.scale ? question.scale.length : 5 }}
                            poin untuk
                            {{
                                question.statements
                                    ? question.statements.length
                                    : 1
                            }}
                            pernyataan
                        </span>
                    </div>
                </div>
            </div>

            <!-- Yes/No (Ya/Tidak) -->
            <div v-else-if="question.type === 'yes-no'" class="mt-2">
                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                    <!-- Radio buttons in horizontal layout with visual distinction -->
                    <div class="flex items-center justify-center space-x-8">
                        <!-- Yes option with green accent -->
                        <div
                            class="flex items-center bg-green-50 px-4 py-2 rounded-md border border-green-100"
                        >
                            <input
                                type="radio"
                                disabled
                                class="h-4 w-4 text-green-600 cursor-not-allowed border-gray-300"
                                checked
                            />
                            <label
                                class="ml-2 text-sm font-medium text-green-700"
                            >
                                {{ question.yesLabel || "Ya" }}
                            </label>
                        </div>

                        <!-- No option with red accent -->
                        <div
                            class="flex items-center bg-red-50 px-4 py-2 rounded-md border border-red-100"
                        >
                            <input
                                type="radio"
                                disabled
                                class="h-4 w-4 text-red-600 cursor-not-allowed border-gray-300"
                            />
                            <label
                                class="ml-2 text-sm font-medium text-red-700"
                            >
                                {{ question.noLabel || "Tidak" }}
                            </label>
                        </div>
                    </div>

                    <!-- Help text for binary choice -->
                    <div
                        class="mt-3 flex items-center justify-center text-xs text-gray-500"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>
                            Responden harus memilih salah satu dari dua opsi
                            yang disediakan
                        </span>
                    </div>
                </div>
            </div>

            <!-- File Upload -->
            <div v-else-if="question.type === 'file-upload'" class="mt-1">
                <div
                    class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50"
                >
                    <div class="space-y-1 text-center">
                        <svg
                            class="mx-auto h-8 w-8 text-gray-400"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                            />
                        </svg>
                        <p class="text-xs text-gray-500">
                            Upload file
                            {{ question.allowedTypes?.join(", ") || "" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Matrix -->
            <div v-else-if="question.type === 'matrix'" class="mt-2">
                <div
                    class="bg-gray-50 rounded-md border border-gray-200 overflow-hidden"
                >
                    <!-- Matrix table with header and rows -->
                    <div class="overflow-x-auto hover:shadow-inner">
                        <table
                            class="min-w-full divide-y divide-gray-200 table-fixed"
                        >
                            <!-- Table Header -->
                            <thead class="bg-gray-100">
                                <tr>
                                    <!-- Empty corner cell -->
                                    <th
                                        scope="col"
                                        class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4"
                                    >
                                        <!-- Empty corner cell -->
                                    </th>
                                    <!-- Column headers - limit to max-width for better display -->
                                    <th
                                        v-for="(
                                            column, colIndex
                                        ) in question.columns &&
                                        question.columns.length
                                            ? question.columns
                                            : defaultMatrixColumns"
                                        :key="colIndex"
                                        scope="col"
                                        class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        style="max-width: 100px"
                                    >
                                        <div class="truncate">
                                            {{ column.text }}
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <!-- Table Body -->
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Show maximum 3 rows or default ones if not defined -->
                                <tr
                                    v-for="(row, rowIndex) in question.rows &&
                                    question.rows.length
                                        ? question.rows.slice(0, 3)
                                        : defaultMatrixRows.slice(0, 3)"
                                    :key="rowIndex"
                                    class="bg-white hover:bg-gray-50"
                                >
                                    <!-- Row header -->
                                    <td
                                        class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-700 bg-gray-50"
                                    >
                                        <div class="truncate">
                                            {{ row.text }}
                                        </div>
                                    </td>

                                    <!-- Matrix cells -->
                                    <td
                                        v-for="(
                                            column, colIndex
                                        ) in question.columns &&
                                        question.columns.length
                                            ? question.columns
                                            : defaultMatrixColumns"
                                        :key="colIndex"
                                        class="px-2 py-2 whitespace-nowrap text-center text-sm"
                                        :class="{
                                            'bg-indigo-50':
                                                rowIndex === 0 &&
                                                colIndex === 1,
                                        }"
                                    >
                                        <!-- Radio button if matrixType is radio or default -->
                                        <div
                                            v-if="
                                                question.matrixType !==
                                                'checkbox'
                                            "
                                            class="flex justify-center"
                                        >
                                            <input
                                                type="radio"
                                                disabled
                                                class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300"
                                                :checked="
                                                    rowIndex === 0 &&
                                                    colIndex === 1
                                                "
                                            />
                                        </div>

                                        <!-- Checkbox if matrixType is checkbox -->
                                        <div v-else class="flex justify-center">
                                            <input
                                                type="checkbox"
                                                disabled
                                                class="h-4 w-4 text-indigo-600 cursor-not-allowed border-gray-300 rounded"
                                                :checked="
                                                    rowIndex === 0 &&
                                                    colIndex === 1
                                                "
                                            />
                                        </div>
                                    </td>
                                </tr>

                                <!-- Indicator for more rows if applicable -->
                                <tr
                                    v-if="
                                        question.rows &&
                                        question.rows.length > 3
                                    "
                                    class="bg-white"
                                >
                                    <td
                                        colspan="100%"
                                        class="px-3 py-2 text-center text-xs text-gray-500 border-t border-dashed border-gray-300"
                                    >
                                        <span
                                            class="flex items-center justify-center"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 mr-1"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 9l-7 7-7-7"
                                                />
                                            </svg>
                                            {{ question.rows.length - 3 }} baris
                                            lainnya
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Help text -->
                    <div class="py-2 px-3 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center text-xs text-gray-500">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <span>
                                Matriks
                                {{
                                    question.matrixType === "checkbox"
                                        ? "pilihan ganda"
                                        : "pilihan tunggal"
                                }}
                                dengan
                                {{ question.rows ? question.rows.length : 0 }}
                                baris dan
                                {{
                                    question.columns
                                        ? question.columns.length
                                        : 0
                                }}
                                kolom
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slider -->
            <div v-else-if="question.type === 'slider'" class="mt-2">
                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                    <!-- Container for slider with min/max labels -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-gray-500">{{
                                props.question.min ?? defaultSliderConfig.min
                            }}</span>
                            <span class="text-xs text-gray-500">{{
                                props.question.max ?? defaultSliderConfig.max
                            }}</span>
                        </div>

                        <!-- Slider track with thumb -->
                        <div class="relative">
                            <!-- Track background -->
                            <div
                                class="w-full h-2 bg-gray-200 rounded-full"
                            ></div>

                            <!-- Colored portion of the track (from min to current value) -->
                            <div
                                class="absolute top-0 left-0 h-2 bg-indigo-500 rounded-full"
                                :style="{
                                    width: `${calculateSliderPercentage()}%`,
                                }"
                            ></div>

                            <!-- Thumb -->
                            <div
                                class="absolute top-0 h-5 w-5 bg-white border-2 border-indigo-500 rounded-full shadow transform -translate-y-1.5 cursor-not-allowed"
                                :style="{
                                    left: `calc(${calculateSliderPercentage()}% - 10px)`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- Current value display -->
                    <div class="flex justify-center items-center mb-3">
                        <div
                            class="px-3 py-1 bg-indigo-100 rounded-full text-sm text-indigo-700 font-medium"
                        >
                            {{ calculateCurrentValue() }}
                        </div>
                    </div>

                    <!-- Labels for value meanings, if provided -->
                    <div class="relative h-10 mb-2">
                        <template
                            v-for="(label, index) in props.question.labels ??
                            defaultSliderConfig.labels"
                            :key="index"
                        >
                            <div
                                class="absolute flex flex-col items-center"
                                :style="{
                                    left: `${calculateLabelPosition(
                                        label.value
                                    )}%`,
                                    transform: 'translateX(-50%)',
                                }"
                            >
                                <div
                                    class="w-1 h-3 bg-gray-300 mb-1"
                                    :class="{
                                        'bg-indigo-500':
                                            label.value <=
                                            calculateCurrentValue(),
                                    }"
                                ></div>
                                <span
                                    class="text-xs text-gray-600 whitespace-nowrap"
                                    >{{ label.text }}</span
                                >
                            </div>
                        </template>
                    </div>

                    <!-- Step indicators -->
                    <div
                        v-if="
                            (props.question.step ?? defaultSliderConfig.step) >
                            1
                        "
                        class="flex items-center justify-center space-x-1 text-xs text-gray-500 mt-2"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span
                            >Interval:
                            {{
                                props.question.step ?? defaultSliderConfig.step
                            }}</span
                        >
                    </div>

                    <!-- Help text -->
                    <div class="mt-2 flex items-center text-xs text-gray-500">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>
                            Slider dengan nilai dari
                            {{ props.question.min ?? defaultSliderConfig.min }}
                            hingga
                            {{ props.question.max ?? defaultSliderConfig.max }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Default/Not Implemented -->
            <div
                v-else
                class="mt-1 py-2 px-3 text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-md"
            >
                [Preview untuk tipe pertanyaan {{ question.type }}]
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from "vue";

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

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    sectionId: {
        type: String,
        required: true,
    },
    index: {
        type: Number,
        required: true,
    },
    isSelected: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "select",
    "duplicate",
    "delete",
    "dragstart",
    "dragend",
]);

const isDragging = ref(false);

const selectQuestion = () => {
    emit("select", props.question.id);
};

const onDragStart = (event) => {
    isDragging.value = true;

    // Add visual feedback for drag operation
    event.target.classList.add("dragging");

    // Set data untuk transfer
    event.dataTransfer.effectAllowed = "move";

    try {
        // Create drag ghost image to show while dragging
        const ghostElement = event.target.cloneNode(true);
        ghostElement.style.width = `${event.target.offsetWidth}px`;
        ghostElement.classList.add("drag-ghost");
        ghostElement.style.opacity = "0.8";
        document.body.appendChild(ghostElement);

        // Set custom drag image with offset
        const rect = event.target.getBoundingClientRect();
        const offsetX = event.clientX - rect.left;
        const offsetY = event.clientY - rect.top;
        event.dataTransfer.setDragImage(ghostElement, offsetX, offsetY);

        // Remove ghost element after it's been used
        setTimeout(() => {
            document.body.removeChild(ghostElement);
        }, 0);

        const dragData = {
            item: props.question,
            sourceType: "question",
            sourceIndex: props.index,
            sectionId: props.sectionId,
        };

        const jsonData = JSON.stringify(dragData);
        event.dataTransfer.setData("application/json", jsonData);
        event.dataTransfer.setData("text/plain", jsonData);

        emit("dragstart", dragData);
    } catch (error) {
        console.error("Error setting drag data:", error);
    }
};

const onDragEnd = () => {
    isDragging.value = false;

    // Remove visual feedback
    event.target.classList.remove("dragging");

    emit("dragend");
};

// Helper methods for displaying type information
const getTypeLabel = (type) => {
    const labels = {
        "short-text": "Teks Pendek",
        "long-text": "Teks Panjang",
        email: "Email",
        phone: "Nomor Telepon",
        number: "Angka",
        date: "Tanggal",
        radio: "Pilihan Ganda",
        checkbox: "Kotak Centang",
        dropdown: "Dropdown",
        rating: "Rating Bintang",
        likert: "Skala Likert",
        "yes-no": "Ya/Tidak",
        "file-upload": "Upload File",
        matrix: "Matriks",
        slider: "Slider",
        ranking: "Rangking",
    };

    return labels[type] || type;
};

const getTypeClass = (type) => {
    const typeCategories = {
        dasar: ["short-text", "long-text", "email", "phone", "number", "date"],
        pilihan: [
            "radio",
            "checkbox",
            "dropdown",
            "rating",
            "likert",
            "yes-no",
        ],
        lanjutan: ["file-upload", "matrix", "slider", "ranking"],
    };

    // Determine category based on type
    let category = "dasar";
    Object.entries(typeCategories).forEach(([cat, types]) => {
        if (types.includes(type)) category = cat;
    });

    // Return appropriate class based on category
    switch (category) {
        case "dasar":
            return "bg-blue-100 text-blue-800";
        case "pilihan":
            return "bg-green-100 text-green-800";
        case "lanjutan":
            return "bg-purple-100 text-purple-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

// Helper functions for slider
const calculateSliderPercentage = () => {
    const min = props.question.min ?? defaultSliderConfig.min;
    const max = props.question.max ?? defaultSliderConfig.max;
    const value =
        props.question.defaultValue ?? defaultSliderConfig.defaultValue;

    return ((value - min) / (max - min)) * 100;
};

const calculateCurrentValue = () => {
    const min = props.question.min ?? defaultSliderConfig.min;
    const max = props.question.max ?? defaultSliderConfig.max;
    const step = props.question.step ?? defaultSliderConfig.step;

    // If defaultValue is defined, use it, otherwise use the config default
    const rawValue =
        props.question.defaultValue ?? defaultSliderConfig.defaultValue;

    // Round to the nearest step value if needed
    const roundedValue = Math.round(rawValue / step) * step;

    return roundedValue;
};

const calculateLabelPosition = (value) => {
    const min = props.question.min ?? defaultSliderConfig.min;
    const max = props.question.max ?? defaultSliderConfig.max;
    const percentage = ((value - min) / (max - min)) * 100;
    return percentage;
};
</script>

<style scoped>
.question-wrapper {
    cursor: pointer;
    transition: all 0.2s ease;
    transform-origin: center center;
    will-change: transform, box-shadow, opacity;
}

.question-wrapper.dragging {
    opacity: 0.7;
    transform: scale(0.98);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: #6366f1;
    background-color: #eef2ff;
    z-index: 10;
    position: relative;
}

.drag-ghost {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
    z-index: -1;
}

.drag-handle {
    opacity: 0.5;
    transition: opacity 0.2s;
}

.question-wrapper:hover .drag-handle {
    opacity: 1;
}
</style>
