<?php

namespace App\Services;

use App\Contracts\Repositories\QuestionRepositoryInterface;
use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use App\Contracts\Repositories\ResponseRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class QuestionnaireService implements QuestionnaireServiceInterface
{
    protected $questionnaireRepository;
    protected $sectionRepository;
    protected $questionRepository;
    protected $responseRepository;

    /**
     * Create a new service instance.
     *
     * @param QuestionnaireRepositoryInterface $questionnaireRepository
     * @param SectionRepositoryInterface $sectionRepository
     * @param QuestionRepositoryInterface $questionRepository
     * @param ResponseRepositoryInterface $responseRepository
     */
    public function __construct(
        QuestionnaireRepositoryInterface $questionnaireRepository,
        SectionRepositoryInterface $sectionRepository,
        QuestionRepositoryInterface $questionRepository,
        ResponseRepositoryInterface $responseRepository
    ) {
        $this->questionnaireRepository = $questionnaireRepository;
        $this->sectionRepository = $sectionRepository;
        $this->questionRepository = $questionRepository;
        $this->responseRepository = $responseRepository;
    }

    /**
     * Get all questionnaires for the current user or all if superadmin.
     *
     * @param array $filters Optional filters
     * @return Collection
     */
    public function getQuestionnaires(array $filters = []): Collection
    {
        $user = Auth::user();
        
        // If superadmin, get all questionnaires, otherwise filter by user_id
        return $user->isSuperAdmin()
            ? $this->questionnaireRepository->getAll(null, $filters)
            : $this->questionnaireRepository->getAll($user->id, $filters);
    }

    /**
     * Get paginated questionnaires for the current user or all if superadmin.
     *
     * @param int $perPage Number of items per page
     * @param array $filters Optional filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedQuestionnaires(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();
        
        // If superadmin, get all questionnaires, otherwise filter by user_id
        return $user->isSuperAdmin()
            ? $this->questionnaireRepository->getPaginated($perPage, null, $filters)
            : $this->questionnaireRepository->getPaginated($perPage, $user->id, $filters);
    }

    /**
     * Get a questionnaire by ID.
     *
     * @param int $id
     * @return Questionnaire|null
     */
    public function getQuestionnaire(int $id): ?Questionnaire
    {
        return $this->questionnaireRepository->find($id);
    }

    /**
     * Get a questionnaire by code.
     *
     * @param string $code
     * @return Questionnaire|null
     */
    public function getQuestionnaireByCode(string $code): ?Questionnaire
    {
        return $this->questionnaireRepository->findByCode($code);
    }

    /**
     * Create a new questionnaire.
     *
     * @param array $data
     * @return Questionnaire
     */
    public function createQuestionnaire(array $data): Questionnaire
    {
        // Add the current user id to the data
        $data['user_id'] = Auth::id();
        
        // Create the questionnaire
        $questionnaire = $this->questionnaireRepository->create($data);
        
        return $questionnaire;
    }

    /**
     * Update an existing questionnaire.
     *
     * @param int $id
     * @param array $data
     * @return Questionnaire
     */
    public function updateQuestionnaire(int $id, array $data): Questionnaire
    {
        return $this->questionnaireRepository->update($id, $data);
    }

    /**
     * Delete a questionnaire.
     *
     * @param int $id
     * @return bool
     */
    public function deleteQuestionnaire(int $id): bool
    {
        return $this->questionnaireRepository->delete($id);
    }

    /**
     * Publish a questionnaire.
     *
     * @param int $id
     * @return Questionnaire
     */
    public function publishQuestionnaire(int $id): Questionnaire
    {
        return $this->questionnaireRepository->updateStatus($id, 'published');
    }

    /**
     * Close a questionnaire.
     *
     * @param int $id
     * @return Questionnaire
     */
    public function closeQuestionnaire(int $id): Questionnaire
    {
        return $this->questionnaireRepository->updateStatus($id, 'closed');
    }

    /**
     * Clone a questionnaire.
     *
     * @param int $id
     * @return Questionnaire
     */
    public function cloneQuestionnaire(int $id): Questionnaire
    {
        return $this->questionnaireRepository->clone($id, Auth::id());
    }

    /**
     * Get active questionnaires.
     *
     * @return Collection
     */
    public function getActiveQuestionnaires(): Collection
    {
        return $this->questionnaireRepository->getActive();
    }

    /**
     * Get template questionnaires.
     *
     * @return Collection
     */
    public function getTemplateQuestionnaires(): Collection
    {
        return $this->questionnaireRepository->getTemplates();
    }

    /**
     * Generate a public link for a questionnaire.
     *
     * @param int $id
     * @return string
     */
    public function generatePublicLink(int $id): string
    {
        $code = $this->questionnaireRepository->generateUniqueCode($id);
        
        return URL::to('/kuesioner/' . $code);
    }

    /**
     * Create section in a questionnaire.
     *
     * @param int $questionnaireId
     * @param array $data
     * @return array
     */
    public function createSection(int $questionnaireId, array $data): array
    {
        $data['questionnaire_id'] = $questionnaireId;
        
        $section = $this->sectionRepository->create($data);
        
        return [
            'success' => true,
            'section' => $section,
            'message' => 'Bagian berhasil dibuat',
        ];
    }

    /**
     * Update section.
     *
     * @param int $sectionId
     * @param array $data
     * @return array
     */
    public function updateSection(int $sectionId, array $data): array
    {
        $section = $this->sectionRepository->update($sectionId, $data);
        
        return [
            'success' => true,
            'section' => $section,
            'message' => 'Bagian berhasil diperbarui',
        ];
    }

    /**
     * Delete section.
     *
     * @param int $sectionId
     * @return bool
     */
    public function deleteSection(int $sectionId): bool
    {
        return $this->sectionRepository->delete($sectionId);
    }

    /**
     * Reorder sections.
     *
     * @param int $questionnaireId
     * @param array $orderedIds
     * @return bool
     */
    public function reorderSections(int $questionnaireId, array $orderedIds): bool
    {
        return $this->sectionRepository->reorder($orderedIds);
    }

    /**
     * Create question in a section.
     *
     * @param int $sectionId
     * @param array $data
     * @return array
     */
    public function createQuestion(int $sectionId, array $data): array
    {
        $data['section_id'] = $sectionId;
        
        $question = $this->questionRepository->create($data);
        
        return [
            'success' => true,
            'question' => $question,
            'message' => 'Pertanyaan berhasil dibuat',
        ];
    }

    /**
     * Update question.
     *
     * @param int $questionId
     * @param array $data
     * @return array
     */
    public function updateQuestion(int $questionId, array $data): array
    {
        $question = $this->questionRepository->update($questionId, $data);
        
        return [
            'success' => true,
            'question' => $question,
            'message' => 'Pertanyaan berhasil diperbarui',
        ];
    }

    /**
     * Delete question.
     *
     * @param int $questionId
     * @return bool
     */
    public function deleteQuestion(int $questionId): bool
    {
        return $this->questionRepository->delete($questionId);
    }

    /**
     * Reorder questions.
     *
     * @param int $sectionId
     * @param array $orderedIds
     * @return bool
     */
    public function reorderQuestions(int $sectionId, array $orderedIds): bool
    {
        return $this->questionRepository->reorder($orderedIds);
    }

    /**
     * Start a response for a questionnaire.
     *
     * @param int $questionnaireId
     * @param Request $request
     * @return array
     */
    public function startResponse(int $questionnaireId, Request $request): array
    {
        $respondentId = $request->input('respondent_id');
        
        $response = $this->responseRepository->create([
            'questionnaire_id' => $questionnaireId,
            'respondent_id' => $respondentId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return [
            'success' => true,
            'response_id' => $response->id,
            'message' => 'Respons berhasil dimulai',
        ];
    }

    /**
     * Save an answer for a response.
     *
     * @param int $responseId
     * @param int $questionId
     * @param mixed $value
     * @return bool
     */
    public function saveAnswer(int $responseId, int $questionId, $value): bool
    {
        // Handle different question types
        $question = $this->questionRepository->find($questionId);
        
        if (!$question) {
            return false;
        }
        
        // For checkbox questions, convert array to JSON
        if ($question->question_type === 'checkbox' && is_array($value)) {
            $value = json_encode($value);
        }
        
        return $this->responseRepository->saveAnswer($responseId, $questionId, (string) $value);
    }

    /**
     * Complete a response.
     *
     * @param int $responseId
     * @return array
     */
    public function completeResponse(int $responseId): array
    {
        $response = $this->responseRepository->complete($responseId);
        
        return [
            'success' => true,
            'response' => $response,
            'message' => 'Terima kasih telah mengisi kuesioner!',
        ];
    }

    /**
     * Get statistics for a questionnaire.
     *
     * @param int $questionnaireId
     * @return array
     */
    public function getStatistics(int $questionnaireId): array
    {
        return $this->responseRepository->getStatistics($questionnaireId);
    }
}