{{-- 
/**
 * Date Selection Modal Component
 * 
 * This component displays a modal for selecting start and end dates before publishing a questionnaire.
 * It's designed to be included once in the page rather than duplicated for each questionnaire card.
 * 
 * Usage:
 * <x-questionnaire.date-selection-modal />
 * 
 * The modal is controlled via JavaScript functions:
 * - openDateSelectionModal(questionnaireId, formUrl): Opens the modal and sets the current questionnaire
 * - confirmDateSelection(): Processes the selected dates and submits the form
 */
--}}

<div id="global-date-selection-modal" class="fixed z-50 inset-0 overflow-y-auto hidden" 
    aria-labelledby="date-selection-modal" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div id="modal-backdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
            aria-hidden="true"></div>

        <!-- Modal positioning helper -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <!-- Modal content -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <!-- Modal icon -->
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    
                    <!-- Modal content -->
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Atur Tanggal Kuesioner
                        </h3>
                        
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Silakan atur tanggal mulai dan tanggal berakhir kuesioner ini. Kuesioner hanya akan aktif selama periode yang ditentukan.
                            </p>
                        </div>

                        <div class="mt-4 space-y-4">
                            <!-- Start date input -->
                            <div>
                                <label for="modal-start-date" class="block text-sm font-medium text-gray-700">
                                    Tanggal Mulai
                                </label>
                                <input type="date" id="modal-start-date" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <p class="mt-1 text-xs text-gray-500">
                                    Jika tidak diisi, kuesioner akan aktif segera setelah dipublikasikan
                                </p>
                            </div>

                            <!-- End date input -->
                            <div>
                                <label for="modal-end-date" class="block text-sm font-medium text-gray-700">
                                    Tanggal Berakhir
                                </label>
                                <input type="date" id="modal-end-date" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <p class="mt-1 text-xs text-gray-500">
                                    Jika tidak diisi, kuesioner akan aktif tanpa batas waktu
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal footer with action buttons -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirm-date-selection-btn"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Lanjutkan
                </button>
                <button type="button" id="cancel-date-selection-btn"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden storage for current questionnaire ID and form URL -->
<input type="hidden" id="current-questionnaire-id" />
<input type="hidden" id="current-form-url" /> 