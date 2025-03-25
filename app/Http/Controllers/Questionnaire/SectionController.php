<?php

namespace App\Http\Controllers;

use App\Contracts\Services\QuestionnaireServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
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
     * Store a newly created section in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Debug log
        Log::info('Creating new section', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $request->input('questionnaire_id'),
            'request_data' => $request->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'questionnaire_id' => 'required|integer|exists:questionnaires,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            Log::warning('Section validation failed', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $result = $this->questionnaireService->createSection(
                $request->input('questionnaire_id'),
                $validator->validated()
            );
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error creating section', [
                'user_id' => auth()->id(),
                'questionnaire_id' => $request->input('questionnaire_id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified section in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Debug log
        Log::info('Updating section', [
            'user_id' => auth()->id(),
            'section_id' => $id,
            'request_data' => $request->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            Log::warning('Section update validation failed', [
                'user_id' => auth()->id(),
                'section_id' => $id,
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $result = $this->questionnaireService->updateSection($id, $validator->validated());
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error updating section', [
                'user_id' => auth()->id(),
                'section_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified section from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Debug log
        Log::info('Deleting section', [
            'user_id' => auth()->id(),
            'section_id' => $id
        ]);
        
        try {
            $result = $this->questionnaireService->deleteSection($id);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Bagian berhasil dihapus' : 'Gagal menghapus bagian',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting section', [
                'user_id' => auth()->id(),
                'section_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder sections.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        // Debug log
        Log::info('Reordering sections', [
            'user_id' => auth()->id(),
            'questionnaire_id' => $request->input('questionnaire_id'),
            'ordered_ids' => $request->input('ordered_ids')
        ]);
        
        $validator = Validator::make($request->all(), [
            'questionnaire_id' => 'required|integer|exists:questionnaires,id',
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:sections,id',
        ]);
        
        if ($validator->fails()) {
            Log::warning('Section reorder validation failed', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $result = $this->questionnaireService->reorderSections(
                $request->input('questionnaire_id'),
                $request->input('ordered_ids')
            );
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Urutan bagian berhasil diperbarui' : 'Gagal memperbarui urutan bagian',
            ]);
        } catch (\Exception $e) {
            Log::error('Error reordering sections', [
                'user_id' => auth()->id(),
                'questionnaire_id' => $request->input('questionnaire_id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}