<?php

namespace App\Repositories;

use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuestionnaireRepository extends BaseRepository implements QuestionnaireRepositoryInterface
{
    /**
     * QuestionnaireRepository constructor.
     *
     * @param Questionnaire $model
     */
    public function __construct(Questionnaire $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getPaginated(int $perPage = 10, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        Log::info('Fetching paginated questionnaires', ['perPage' => $perPage]);
        
        $query = $this->model->newQuery();
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->select($columns)->latest()->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findBySlug(string $slug, array $columns = ['*']): ?Questionnaire
    {
        Log::info('Finding questionnaire by slug', ['slug' => $slug]);
        
        return $this->model->select($columns)->where('slug', $slug)->first();
    }

    /**
     * @inheritDoc
     */
    public function getActive(array $columns = ['*']): Collection
    {
        Log::info('Fetching active questionnaires');
        
        $now = now();
        
        return $this->model->select($columns)
            ->where('status', 'published')
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->latest()
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByUserId(int $userId, array $columns = ['*']): Collection
    {
        Log::info('Fetching questionnaires by user', ['userId' => $userId]);
        
        return $this->model->select($columns)
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getTemplates(array $columns = ['*']): Collection
    {
        Log::info('Fetching template questionnaires');
        
        return $this->model->select($columns)
            ->where('is_template', true)
            ->latest()
            ->get();
    }

    /**
     * Create a new questionnaire with a unique slug
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        Log::info('Creating new questionnaire', ['title' => $data['title'] ?? null]);
        
        // Generate a slug if not provided
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }
        
        return parent::create($data);
    }

    /**
     * Update a questionnaire
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        Log::info('Updating questionnaire', ['id' => $id]);
        
        // Update slug if title changed and slug not provided
        if (isset($data['title']) && !isset($data['slug'])) {
            $questionnaire = $this->find($id);
            
            // Only update slug if title has changed
            if ($questionnaire && $questionnaire->title !== $data['title']) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], $id);
            }
        }
        
        return parent::update($id, $data);
    }

    /**
     * @inheritDoc
     */
    public function clone(int $id, array $attributes = []): ?Questionnaire
    {
        Log::info('Cloning questionnaire', ['id' => $id]);
        
        /** @var Questionnaire|null $source */
        $source = $this->model->with(['sections.questions.options', 'sections.questions.logic'])->find($id);
        
        if (!$source) {
            Log::warning('Failed to clone questionnaire: source not found', ['id' => $id]);
            return null;
        }
        
        // Begin transaction
        return DB::transaction(function () use ($source, $attributes) {
            // Create new questionnaire
            $cloneData = $source->toArray();
            
            // Remove id and timestamps
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at']);
            
            // Override with provided attributes
            $cloneData = array_merge($cloneData, $attributes);
            
            // Generate new title and slug if not provided
            if (!isset($attributes['title'])) {
                $cloneData['title'] = "Copy of {$source->title}";
            }
            
            if (!isset($attributes['slug'])) {
                $cloneData['slug'] = $this->generateUniqueSlug($cloneData['title']);
            }
            
            // Create the cloned questionnaire
            /** @var Questionnaire $clone */
            $clone = $this->create($cloneData);
            
            // Clone sections, questions, and options
            foreach ($source->sections as $section) {
                $sectionData = $section->toArray();
                unset($sectionData['id'], $sectionData['created_at'], $sectionData['updated_at']);
                $sectionData['questionnaire_id'] = $clone->id;
                
                /** @var \App\Models\Section $newSection */
                $newSection = $clone->sections()->create($sectionData);
                
                foreach ($section->questions as $question) {
                    $questionData = $question->toArray();
                    unset($questionData['id'], $questionData['created_at'], $questionData['updated_at']);
                    $questionData['section_id'] = $newSection->id;
                    
                    /** @var \App\Models\Question $newQuestion */
                    $newQuestion = $newSection->questions()->create($questionData);
                    
                    // Clone options
                    foreach ($question->options as $option) {
                        $optionData = $option->toArray();
                        unset($optionData['id'], $optionData['created_at'], $optionData['updated_at']);
                        $optionData['question_id'] = $newQuestion->id;
                        
                        $newQuestion->options()->create($optionData);
                    }
                    
                    // Clone logic
                    foreach ($question->logic as $logic) {
                        $logicData = $logic->toArray();
                        unset($logicData['id'], $logicData['created_at'], $logicData['updated_at']);
                        $logicData['question_id'] = $newQuestion->id;
                        
                        // Note: This is a simplified approach; in a real application, 
                        // you would need to map the action_target IDs to the new IDs
                        $newQuestion->logic()->create($logicData);
                    }
                }
            }
            
            Log::info('Questionnaire cloned successfully', [
                'source_id' => $source->id, 
                'clone_id' => $clone->id
            ]);
            
            return $clone;
        });
    }

    /**
     * Generate a unique slug for a questionnaire
     *
     * @param string $title
     * @param int|null $excludeId
     * @return string
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;
        
        $query = $this->model->where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // While a questionnaire with this slug exists, append a number and try again
        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count++;
            $query = $this->model->where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }
        
        return $slug;
    }

    /**
     * Get filtered questionnaires
     *
     * @param array $filters
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function getFiltered(
        array $filters,
        int $perPage = 10,
        array $columns = ['*'],
        array $relations = []
    ): LengthAwarePaginator {
        $query = $this->model->query();

        // Load relations if specified
        if (!empty($relations)) {
            $query->with($relations);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Apply period filter
        if (!empty($filters['period'])) {
            $today = now();
            
            if ($filters['period'] === 'active') {
                $query->where('start_date', '<=', $today)
                      ->where('end_date', '>=', $today);
            } elseif ($filters['period'] === 'upcoming') {
                $query->where('start_date', '>', $today);
            } elseif ($filters['period'] === 'expired') {
                $query->where('end_date', '<', $today);
            }
        }

        // Apply template filter
        if (isset($filters['is_template'])) {
            $query->where('is_template', $filters['is_template']);
        }

        // Apply sorting
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'title_desc':
                    $query->orderBy('title', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginate results
        return $query->paginate($perPage, $columns)->withQueryString();
    }
} 