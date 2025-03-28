<?php

namespace App\Http\Controllers\Questionnaire;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Services\QuestionnaireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller khusus untuk menangani pertanyaan tipe file upload
 */
class FileUploadQuestionController extends Controller
{
    protected $questionnaireService;

    public function __construct(QuestionnaireService $questionnaireService)
    {
        $this->questionnaireService = $questionnaireService;
    }

    /**
     * Memperbarui pertanyaan file upload yang sudah ada
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        Log::info('Updating file upload question', ['id' => $id]);
        
        $data = $request->all();
        
        // Penanganan khusus untuk allowedTypes
        if (isset($data['settings']) && is_array($data['settings']) && isset($data['settings']['allowedTypes'])) {
            if (in_array('*/*', $data['settings']['allowedTypes'])) {
                Log::info('Found */* in allowedTypes, ensuring proper JSON encoding');
                // Pastikan tidak di-escape saat disimpan
                $data['settings']['allowedTypes'] = ['*/*'];
                $data['settings'] = json_encode($data['settings'], JSON_UNESCAPED_SLASHES);
            }
        }
        
        // Pastikan question_type adalah 'file' atau 'file-upload'
        $data['question_type'] = 'file';
        
        $success = $this->questionnaireService->updateQuestion($id, $data);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Pertanyaan file upload berhasil diperbarui' : 'Gagal memperbarui pertanyaan',
        ]);
    }

    /**
     * Menyimpan pertanyaan file upload baru
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('Storing new file upload question');
        
        $data = $request->all();
        
        // Penanganan khusus untuk allowedTypes
        if (isset($data['settings']) && is_array($data['settings']) && isset($data['settings']['allowedTypes'])) {
            if (in_array('*/*', $data['settings']['allowedTypes'])) {
                Log::info('Found */* in allowedTypes, ensuring proper JSON encoding');
                // Pastikan tidak di-escape saat disimpan
                $data['settings']['allowedTypes'] = ['*/*'];
                $data['settings'] = json_encode($data['settings'], JSON_UNESCAPED_SLASHES);
            }
        }
        
        // Pastikan question_type adalah 'file' atau 'file-upload'
        $data['question_type'] = 'file';
        
        // Validasi section_id
        if (!isset($data['section_id']) || !$data['section_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Section ID diperlukan',
            ], 400);
        }
        
        // Simpan pertanyaan menggunakan addQuestion
        try {
            $question = $this->questionnaireService->addQuestion($data['section_id'], $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan file upload berhasil disimpan',
                'question' => $question,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating file upload question', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pertanyaan file upload: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Validasi file upload untuk memastikan sesuai dengan allowedTypes
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function validateFileType(Request $request): JsonResponse
    {
        $file = $request->file('file');
        $questionId = $request->input('question_id');
        
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan',
            ], 400);
        }
        
        if (!$questionId) {
            return response()->json([
                'success' => false,
                'message' => 'ID pertanyaan tidak ditemukan',
            ], 400);
        }
        
        // Ambil pertanyaan dari database
        $question = Question::find($questionId);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Pertanyaan tidak ditemukan',
            ], 404);
        }
        
        // Dekode settings untuk mendapatkan allowedTypes
        $settings = is_string($question->settings) ? json_decode($question->settings, true) : $question->settings;
        
        if (!$settings || !isset($settings['allowedTypes'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe file yang diperbolehkan tidak terdefinisi',
            ], 400);
        }
        
        // Jika allowedTypes berisi */* maka semua tipe diperbolehkan
        if (in_array('*/*', $settings['allowedTypes'])) {
            return response()->json([
                'success' => true,
                'message' => 'File valid',
            ]);
        }
        
        // Validasi tipe MIME
        $mimeType = $file->getMimeType();
        $valid = false;
        
        foreach ($settings['allowedTypes'] as $allowedType) {
            // Cek wildcard mime type (e.g., "image/*")
            if (strpos($allowedType, '/*') !== false) {
                $category = str_replace('/*', '', $allowedType);
                if (strpos($mimeType, $category . '/') === 0) {
                    $valid = true;
                    break;
                }
            }
            // Exact match
            elseif ($mimeType === $allowedType) {
                $valid = true;
                break;
            }
        }
        
        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe file tidak diperbolehkan. Hanya menerima: ' . implode(', ', $settings['allowedTypes']),
            ], 400);
        }
        
        // Cek ukuran file
        $maxSizeMB = $settings['maxSize'] ?? 5; // Default 5MB jika tidak ditentukan
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;
        
        if ($file->getSize() > $maxSizeBytes) {
            return response()->json([
                'success' => false,
                'message' => "Ukuran file terlalu besar. Maksimal {$maxSizeMB}MB",
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'File valid',
        ]);
    }
} 