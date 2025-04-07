<?php

namespace App\Http\Controllers\Questionnaire;

use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Contracts\Services\ResponseServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResponseController extends Controller
{
    /**
     * @var QuestionnaireServiceInterface
     */
    protected $questionnaireService;
    
    /**
     * @var ResponseServiceInterface
     */
    protected $responseService;
    
    /**
     * ResponseController constructor.
     *
     * @param QuestionnaireServiceInterface $questionnaireService
     * @param ResponseServiceInterface $responseService
     */
    public function __construct(QuestionnaireServiceInterface $questionnaireService, ResponseServiceInterface $responseService)
    {
        $this->questionnaireService = $questionnaireService;
        $this->responseService = $responseService;
        // Controller middleware is defined in the routes file instead
    }
    
    /**
     * Display a listing of the responses for a questionnaire.
     *
     * @param int $questionnaireId
     * @return View
     */
    public function index(int $questionnaireId): View
    {
        Log::info('Displaying responses list', ['questionnaireId' => $questionnaireId]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($questionnaireId);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        $responses = $this->responseService->getPaginatedResponses($questionnaireId);
        $statistics = $this->responseService->getQuestionnaireStatistics($questionnaireId);
        
        return view('questionnaire.responses.index', compact('questionnaire', 'responses', 'statistics'));
    }
    
    /**
     * Display the specified response.
     *
     * @param int $questionnaireId
     * @param int $responseId
     * @return View
     */
    public function show(int $questionnaireId, int $responseId): View
    {
        Log::info('Displaying response details', ['questionnaireId' => $questionnaireId, 'responseId' => $responseId]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($questionnaireId);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        $response = $this->responseService->getResponseById($responseId);
        
        abort_if(!$response, 404, 'Respons tidak ditemukan');
        abort_if($response->questionnaire_id != $questionnaireId, 403, 'Respons tidak ditemukan');
        
        $answers = $this->responseService->getResponseAnswers($responseId);
        
        return view('questionnaire.responses.show', compact('questionnaire', 'response', 'answers'));
    }
    
    /**
     * Remove the specified response from storage.
     *
     * @param int $questionnaireId
     * @param int $responseId
     * @return JsonResponse
     */
    public function destroy(int $questionnaireId, int $responseId): JsonResponse
    {
        Log::info('Deleting response', ['questionnaireId' => $questionnaireId, 'responseId' => $responseId]);
        
        $deleted = $this->responseService->deleteResponse($responseId);
        
        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Respons berhasil dihapus' : 'Gagal menghapus respons'
        ]);
    }
    
    /**
     * Export responses to CSV.
     *
     * @param int $questionnaireId
     * @return Response
     */
    public function export(int $questionnaireId): Response
    {
        Log::info('Exporting responses to CSV', ['questionnaireId' => $questionnaireId]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($questionnaireId);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        $csvContent = $this->responseService->exportResponsesToCSV($questionnaireId);
        $filename = Str::slug($questionnaire->title) . '-responses-' . now()->format('Y-m-d') . '.csv';
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Get statistics for a questionnaire.
     *
     * @param int $questionnaireId
     * @return View|JsonResponse
     */
    public function statistics(int $questionnaireId)
    {
        Log::info('Getting statistics', ['questionnaireId' => $questionnaireId]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($questionnaireId);
        
        if (!$questionnaire) {
            return response()->json(['success' => false, 'message' => 'Kuesioner tidak ditemukan'], 404);
        }
        
        $statistics = $this->responseService->getQuestionnaireStatistics($questionnaireId);
        
        // Check if the request expects JSON or HTML
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'statistics' => $statistics]);
        }
        
        // Return the view for HTML requests
        return view('questionnaire.results', compact('questionnaire', 'statistics'));
    }
} 