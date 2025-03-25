<?php

namespace App\Http\Controllers;

use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Helpers\SessionManager;
use App\Http\Requests\Questionnaire\StoreQuestionnaireRequest;
use App\Http\Requests\Questionnaire\UpdateQuestionnaireRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    protected $questionnaireService;
    
    /**
     * Create a new controller instance.
     *
     * @param QuestionnaireServiceInterface $questionnaireService
     */
    public function __construct(QuestionnaireServiceInterface $questionnaireService)
    {
        $this->questionnaireService = $questionnaireService;
    }

    /**
     * Display a listing of the questionnaires.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Debug log
        \Log::info('Accessing questionnaire index page', [
            'user_id' => auth()->id(),
            'filters' => $request->all()
        ]);
        
        $filters = [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'is_template' => $request->boolean('is_template'),
        ];
        
        $questionnaires = $this->questionnaireService->getPaginatedQuestionnaires(10, $filters);
        
        return view('questionnaires.index', compact('questionnaires'));
    }

    /**
     * Show the form for creating a new questionnaire.
     *
     * @return View
     */
    public function create(): View
    {
        // Debug log
        \Log::info('Accessing create questionnaire page', [
            'user_id' => auth()->id()
        ]);
        
        $templates = $this->questionnaireService->getTemplateQuestionnaires();
        
        return view('questionnaires.create', compact('templates'));
    }

    /**
     * Store a newly created questionnaire in storage.
     *
     * @param StoreQuestionnaireRequest $request
     * @return RedirectResponse
     */
    public function store(StoreQuestionnaireRequest $request): RedirectResponse
    {
        // Debug log
        \Log::info('Storing new questionnaire', [
            'user_id' => auth()->id(),
            'request_data' => $request->validated()
        ]);
        
        try {
            $questionnaire = $this->questionnaireService->createQuestionnaire($request->validated());
            
            SessionManager::flashSuccess('Kuesioner berhasil dibuat.');
            
            return redirect()->route('questionnaires.edit', $questionnaire);
        } catch (\Exception $e) {
            \Log::error('Error creating questionnaire', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            SessionManager::flashError('Terjadi kesalahan saat membuat kuesioner: ' . $e->getMessage());
            
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified questionnaire.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        // Debug log
        \Log::info('Accessing edit questionnaire page', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id
        ]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaire($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        return view('questionnaires.edit', compact('questionnaire'));
    }

    /**
     * Update the specified questionnaire in storage.
     *
     * @param UpdateQuestionnaireRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateQuestionnaireRequest $request, int $id): RedirectResponse
    {
        // Debug log
        \Log::info('Updating questionnaire', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id,
            'request_data' => $request->validated()
        ]);
        
        try {
            $this->questionnaireService->updateQuestionnaire($id, $request->validated());
            
            SessionManager::flashSuccess('Kuesioner berhasil diperbarui.');
            
            return back();
        } catch (\Exception $e) {
            \Log::error('Error updating questionnaire', [
                'user_id' => auth()->id(),
                'questionnaire_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            SessionManager::flashError('Terjadi kesalahan saat memperbarui kuesioner: ' . $e->getMessage());
            
            return back()->withInput();
        }
    }

    /**
     * Remove the specified questionnaire from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        // Debug log
        \Log::info('Deleting questionnaire', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id
        ]);
        
        try {
            $this->questionnaireService->deleteQuestionnaire($id);
            
            SessionManager::flashSuccess('Kuesioner berhasil dihapus.');
            
            return redirect()->route('questionnaires.index');
        } catch (\Exception $e) {
            \Log::error('Error deleting questionnaire', [
                'user_id' => auth()->id(),
                'questionnaire_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            SessionManager::flashError('Terjadi kesalahan saat menghapus kuesioner: ' . $e->getMessage());
            
            return back();
        }
    }

    /**
     * Preview the specified questionnaire.
     *
     * @param int $id
     * @return View
     */
    public function preview(int $id): View
    {
        // Debug log
        \Log::info('Previewing questionnaire', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id
        ]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaire($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        return view('questionnaires.preview', compact('questionnaire'));
    }

    /**
     * Publish the specified questionnaire.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function publish(int $id): RedirectResponse
    {
        // Debug log
        \Log::info('Publishing questionnaire', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id
        ]);
        
        try {
            $this->questionnaireService->publishQuestionnaire($id);
            
            SessionManager::flashSuccess('Kuesioner berhasil dipublikasikan.');
            
            return back();
        } catch (\Exception $e) {
            \Log::error('Error publishing questionnaire', [
                'user_id' => auth()->id(),
                'questionnaire_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            SessionManager::flashError('Terjadi kesalahan saat mempublikasikan kuesioner: ' . $e->getMessage());
            
            return back();
        }
    }

    /**
     * Close the specified questionnaire.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function close(int $id): RedirectResponse
    {
        // Debug log
        \Log::info('Closing questionnaire', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id
        ]);
        
        try {
            $this->questionnaireService->closeQuestionnaire($id);
            
            SessionManager::flashSuccess('Kuesioner berhasil ditutup.');
            
            return back();
        } catch (\Exception $e) {
            \Log::error('Error closing questionnaire', [
                'user_id' => auth()->id(),
                'questionnaire_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            SessionManager::flashError('Terjadi kesalahan saat menutup kuesioner: ' . $e->getMessage());
            
            return back();
        }
    }

    /**
     * Clone the specified questionnaire.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function clone(int $id): RedirectResponse
    {
        // Debug log
        \Log::info('Cloning questionnaire', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id
        ]);
        
        try {
            $clonedQuestionnaire = $this->questionnaireService->cloneQuestionnaire($id);
            
            SessionManager::flashSuccess('Kuesioner berhasil disalin.');
            
            return redirect()->route('questionnaires.edit', $clonedQuestionnaire);
        } catch (\Exception $e) {
            \Log::error('Error cloning questionnaire', [
                'user_id' => auth()->id(),
                'questionnaire_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            SessionManager::flashError('Terjadi kesalahan saat menyalin kuesioner: ' . $e->getMessage());
            
            return back();
        }
    }

    /**
     * Generate a public link for the specified questionnaire.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function generateLink(int $id): JsonResponse
    {
        // Debug log
        \Log::info('Generating public link for questionnaire', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id
        ]);
        
        try {
            $link = $this->questionnaireService->generatePublicLink($id);
            
            return response()->json([
                'success' => true,
                'link' => $link,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error generating link for questionnaire', [
                'user_id' => auth()->id(),
                'questionnaire_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat tautan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show statistics for the specified questionnaire.
     *
     * @param int $id
     * @return View
     */
    public function statistics(int $id): View
    {
        // Debug log
        \Log::info('Viewing questionnaire statistics', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $id
        ]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaire($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        $statistics = $this->questionnaireService->getStatistics($id);
        
        return view('questionnaires.statistics', compact('questionnaire', 'statistics'));
    }

    /**
     * Show the public questionnaire form for respondents.
     *
     * @param string $code
     * @return View
     */
    public function showPublic(string $code): View
    {
        // Debug log
        \Log::info('Accessing public questionnaire', [
            'code' => $code,
            'ip' => request()->ip()
        ]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireByCode($code);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        abort_if($questionnaire->status !== 'published', 403, 'Kuesioner tidak aktif');
        
        return view('questionnaires.public', compact('questionnaire', 'code'));
    }

    /**
     * Start a new response for the public questionnaire.
     *
     * @param Request $request
     * @param string $code
     * @return JsonResponse
     */
    public function startResponse(Request $request, string $code): JsonResponse
    {
        // Debug log
        \Log::info('Starting questionnaire response', [
            'code' => $code,
            'ip' => $request->ip(),
            'data' => $request->all()
        ]);
        
        try {
            $questionnaire = $this->questionnaireService->getQuestionnaireByCode($code);
            
            if (!$questionnaire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuesioner tidak ditemukan',
                ], 404);
            }
            
            if ($questionnaire->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuesioner tidak aktif',
                ], 403);
            }
            
            $result = $this->questionnaireService->startResponse($questionnaire->id, $request);
            
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Error starting questionnaire response', [
                'code' => $code,
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit an answer for the public questionnaire.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitAnswer(Request $request): JsonResponse
    {
        // Debug log
        \Log::info('Submitting answer', [
            'response_id' => $request->input('response_id'),
            'question_id' => $request->input('question_id'),
            'ip' => $request->ip()
        ]);
        
        try {
            $responseId = $request->input('response_id');
            $questionId = $request->input('question_id');
            $value = $request->input('value');
            
            $result = $this->questionnaireService->saveAnswer($responseId, $questionId, $value);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Jawaban disimpan' : 'Gagal menyimpan jawaban',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error submitting answer', [
                'response_id' => $request->input('response_id'),
                'question_id' => $request->input('question_id'),
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete a response for the public questionnaire.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function completeResponse(Request $request): JsonResponse
    {
        // Debug log
        \Log::info('Completing questionnaire response', [
            'response_id' => $request->input('response_id'),
            'ip' => $request->ip()
        ]);
        
        try {
            $responseId = $request->input('response_id');
            
            $result = $this->questionnaireService->completeResponse($responseId);
            
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Error completing questionnaire response', [
                'response_id' => $request->input('response_id'),
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}