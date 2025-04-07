/*
 * Routes for answer details
 */
Route::middleware(['auth:sanctum', 'verified'])->prefix('answer-details')->group(function () {
    Route::get('/response/{responseId}', [App\Http\Controllers\Questionnaire\AnswerDetailController::class, 'getByResponse']);
    Route::get('/question/{questionId}', [App\Http\Controllers\Questionnaire\AnswerDetailController::class, 'getByQuestion']);
    Route::get('/questionnaire/{questionnaireId}', [App\Http\Controllers\Questionnaire\AnswerDetailController::class, 'getByQuestionnaire']);
}); 