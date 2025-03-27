<?php

namespace App\Http\Controllers\Questionnaire;

use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Questionnaire\PublishQuestionnaireRequest;
use App\Http\Requests\Questionnaire\StoreQuestionnaireRequest;
use App\Http\Requests\Questionnaire\UpdateQuestionnaireRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    /**
     * @var QuestionnaireServiceInterface
     */
    protected $questionnaireService;
    
    /**
     * QuestionnaireController constructor.
     *
     * @param QuestionnaireServiceInterface $questionnaireService
     */
    public function __construct(QuestionnaireServiceInterface $questionnaireService)
    {
        $this->questionnaireService = $questionnaireService;
        // Controller middleware is defined in the routes file instead
    }
    
    /**
     * Display a listing of the questionnaires.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        Log::info('Displaying questionnaires list');
        
        $questionnaires = $this->questionnaireService->getPaginatedQuestionnaires(10);
        
        return view('questionnaire.index', compact('questionnaires'));
    }
    
    /**
     * Show the form for creating a new questionnaire.
     *
     * @return View
     */
    public function create(): View
    {
        Log::info('Displaying questionnaire creation form');
        
        $templates = $this->questionnaireService->getTemplateQuestionnaires();
        $initialData = [
            'title' => 'Kuesioner Baru',
            'sections' => []
        ];
        
        return view('questionnaire.create', compact('templates', 'initialData'));
    }
    
    /**
     * Store a newly created questionnaire in storage.
     *
     * @param StoreQuestionnaireRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(StoreQuestionnaireRequest $request): JsonResponse|RedirectResponse
    {
        Log::info('Storing new questionnaire');
        
        $userId = Auth::id();
        $questionnaire = $this->questionnaireService->createQuestionnaire($request->validated(), $userId);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kuesioner berhasil dibuat',
                'id' => $questionnaire->id,
                'slug' => $questionnaire->slug
            ]);
        }
        
        return redirect()->route('questionnaires.edit', $questionnaire->id)
            ->with('success', 'Kuesioner berhasil dibuat');
    }
    
    /**
     * Display the specified questionnaire.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        Log::info('Displaying questionnaire', ['id' => $id]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        $sections = $this->questionnaireService->getQuestionnaireSections($id);
        
        return view('questionnaire.show', compact('questionnaire', 'sections'));
    }
    
    /**
     * Show the form for editing the specified questionnaire.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        Log::info('Displaying questionnaire edit form', ['id' => $id]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        $sections = $this->questionnaireService->getQuestionnaireSections($id);
        
        return view('questionnaire.edit', compact('questionnaire', 'sections'));
    }
    
    /**
     * Update the specified questionnaire in storage.
     *
     * @param UpdateQuestionnaireRequest $request
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function update(UpdateQuestionnaireRequest $request, int $id): JsonResponse|RedirectResponse
    {
        Log::info('Updating questionnaire', ['id' => $id]);
        
        $updated = $this->questionnaireService->updateQuestionnaire($id, $request->validated());
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $updated,
                'message' => $updated ? 'Kuesioner berhasil diperbarui' : 'Gagal memperbarui kuesioner'
            ]);
        }
        
        if ($updated) {
            return redirect()->route('questionnaires.edit', $id)
                ->with('success', 'Kuesioner berhasil diperbarui');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui kuesioner']);
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
        Log::info('Deleting questionnaire', ['id' => $id]);
        
        $deleted = $this->questionnaireService->deleteQuestionnaire($id);
        
        if ($deleted) {
            return redirect()->route('questionnaires.index')
                ->with('success', 'Kuesioner berhasil dihapus');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus kuesioner']);
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
        Log::info('Previewing questionnaire', ['id' => $id]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($id);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        return view('questionnaire.preview', compact('questionnaire'));
    }
    
    /**
     * Publish the specified questionnaire.
     *
     * @param PublishQuestionnaireRequest $request
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function publish(PublishQuestionnaireRequest $request, int $id): JsonResponse|RedirectResponse
    {
        Log::info('Publishing questionnaire - REQUEST DETAILS', [
            'id' => $id, 
            'method' => $request->method(),
            'all_data' => $request->all(),
            'validated_data' => $request->validated(),
            'user_id' => Auth::id(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'csrf_present' => $request->hasHeader('X-CSRF-TOKEN'),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
        ]);
        
        try {
            // Verifikasi bahwa kuesioner ada dan dimiliki oleh user yang melakukan request
            $questionnaire = $this->questionnaireService->getQuestionnaireById($id);
            
            if (!$questionnaire) {
                Log::error('Questionnaire not found', ['id' => $id]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kuesioner tidak ditemukan'
                    ], 404);
                }
                
                return redirect()->back()->withErrors(['error' => 'Kuesioner tidak ditemukan']);
            }
            
            // Log detil kuesioner
            Log::info('Questionnaire found', [
                'id' => $questionnaire->id,
                'title' => $questionnaire->title,
                'user_id' => $questionnaire->user_id,
                'status' => $questionnaire->status
            ]);
            
            // Verifikasi kepemilikan (opsional, tergantung kebutuhan)
            // if ($questionnaire->user_id !== Auth::id()) {
            //     Log::error('Unauthorized publish attempt', ['id' => $id, 'user_id' => Auth::id()]);
            //     
            //     if ($request->expectsJson()) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Anda tidak memiliki izin untuk menerbitkan kuesioner ini'
            //         ], 403);
            //     }
            //     
            //     return redirect()->back()->withErrors(['error' => 'Anda tidak memiliki izin untuk menerbitkan kuesioner ini']);
            // }
            
            // Publikasikan kuesioner
            $published = $this->questionnaireService->publishQuestionnaire($id, $request->validated());
            
            if ($published) {
                Log::info('Questionnaire published successfully', ['id' => $id]);
            } else {
                Log::error('Failed to publish questionnaire', ['id' => $id]);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => $published,
                    'message' => $published ? 'Kuesioner berhasil dipublikasikan' : 'Gagal mempublikasikan kuesioner',
                    'questionnaire' => $published ? $this->questionnaireService->getQuestionnaireById($id) : null
                ]);
            }
            
            if ($published) {
                return redirect()->route('questionnaires.show', $id)
                    ->with('success', 'Kuesioner berhasil dipublikasikan');
            } else {
                return redirect()->back()->withErrors(['error' => 'Gagal mempublikasikan kuesioner']);
            }
        } catch (\Exception $e) {
            Log::error('Exception when publishing questionnaire', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menerbitkan kuesioner: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menerbitkan kuesioner']);
        }
    }
    
    /**
     * Close the specified questionnaire.
     *
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function close(int $id): JsonResponse|RedirectResponse
    {
        Log::info('Closing questionnaire', ['id' => $id]);
        
        $closed = $this->questionnaireService->closeQuestionnaire($id);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => $closed,
                'message' => $closed ? 'Kuesioner berhasil ditutup' : 'Gagal menutup kuesioner'
            ]);
        }
        
        if ($closed) {
            return redirect()->route('questionnaires.show', $id)
                ->with('success', 'Kuesioner berhasil ditutup');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal menutup kuesioner']);
        }
    }
    
    /**
     * Clone the specified questionnaire.
     *
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function clone(int $id): JsonResponse|RedirectResponse
    {
        Log::info('Cloning questionnaire', ['id' => $id]);
        
        $clone = $this->questionnaireService->cloneQuestionnaire($id);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => (bool)$clone,
                'message' => $clone ? 'Kuesioner berhasil diduplikasi' : 'Gagal menduplikasi kuesioner',
                'id' => $clone ? $clone->id : null
            ]);
        }
        
        if ($clone) {
            return redirect()->route('questionnaires.edit', $clone->id)
                ->with('success', 'Kuesioner berhasil diduplikasi');
        } else {
            return redirect()->back()->withErrors(['error' => 'Gagal menduplikasi kuesioner']);
        }
    }
} 