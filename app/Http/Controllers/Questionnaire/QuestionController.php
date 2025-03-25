<?php

namespace App\Http\Controllers;

use App\Contracts\Services\QuestionnaireServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
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
     * Store a newly created question in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Debug log
        Log::info('Creating new question', [
            'user_id' => auth()->id(),
            'section_id' => $request->input('section_id'),
            'request_data' => $request->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|integer|exists:sections,id',
            'question_type' => 'required|string|in:text,textarea,radio,checkbox,dropdown,rating,date,file,matrix,static',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'settings' => 'nullable|array',
            'options' => 'required_if:question_type,radio,checkbox,dropdown,matrix|array',
            'options.*.value' => 'required_with:options|string',
            'options.*.label' => 'required_with:options|string',
            'logic' => 'nullable|array',
            'logic.*.condition_type' => 'required_with:logic|string|in:equals,not_equals,contains,not_contains,greater_than,less_than,is_answered,is_not_answered',
            'logic.*.condition_value' => 'nullable|string',
            'logic.*.action_type' => 'required_with:logic|string|in:show,hide,jump',
            'logic.*.action_target' => 'required_with:logic|integer',
        ]);
        
        if ($validator->fails()) {
            Log::warning('Question validation failed', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $result = $this->questionnaireService->createQuestion(
                $request->input('section_id'),
                $validator->validated()
            );
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error creating question', [
                'user_id' => auth()->id(),
                'section_id' => $request->input('section_id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified question in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Debug log
        Log::info('Updating question', [
            'user_id' => auth()->id(),
            'question_id' => $id,
            'request_data' => $request->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'question_type' => 'string|in:text,textarea,radio,checkbox,dropdown,rating,date,file,matrix,static',
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'settings' => 'nullable|array',
            'options' => 'array',
            'options.*.value' => 'required_with:options|string',
            'options.*.label' => 'required_with:options|string',
            'logic' => 'nullable|array',
            'logic.*.condition_type' => 'required_with:logic|string|in:equals,not_equals,contains,not_contains,greater_than,less_than,is_answered,is_not_answered',
            'logic.*.condition_value' => 'nullable|string',
            'logic.*.action_type' => 'required_with:logic|string|in:show,hide,jump',
            'logic.*.action_target' => 'required_with:logic|integer',
        ]);
        
        if ($validator->fails()) {
            Log::warning('Question update validation failed', [
                'user_id' => auth()->id(),
                'question_id' => $id,
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $result = $this->questionnaireService->updateQuestion($id, $validator->validated());
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error updating question', [
                'user_id' => auth()->id(),
                'question_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified question from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Debug log
        Log::info('Deleting question', [
            'user_id' => auth()->id(),
            'question_id' => $id
        ]);
        
        try {
            $result = $this->questionnaireService->deleteQuestion($id);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Pertanyaan berhasil dihapus' : 'Gagal menghapus pertanyaan',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting question', [
                'user_id' => auth()->id(),
                'question_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder questions within a section.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        // Debug log
        Log::info('Reordering questions', [
            'user_id' => auth()->id(),
            'section_id' => $request->input('section_id'),
            'ordered_ids' => $request->input('ordered_ids')
        ]);
        
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|integer|exists:sections,id',
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:questions,id',
        ]);
        
        if ($validator->fails()) {
            Log::warning('Question reorder validation failed', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $result = $this->questionnaireService->reorderQuestions(
                $request->input('section_id'),
                $request->input('ordered_ids')
            );
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Urutan pertanyaan berhasil diperbarui' : 'Gagal memperbarui urutan pertanyaan',
            ]);
        } catch (\Exception $e) {
            Log::error('Error reordering questions', [
                'user_id' => auth()->id(),
                'section_id' => $request->input('section_id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}