<?php

namespace App\Repositories\Eloquent;

use App\Models\Mentor;
use App\Repositories\Interfaces\MentorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MentorRepository extends BaseRepository implements MentorRepositoryInterface
{
    /**
     * MentorRepository constructor.
     *
     * @param Mentor $model
     */
    public function __construct(Mentor $model)
    {
        parent::__construct($model);
    }
    
    /**
     * @inheritDoc
     */
    public function findByUserId(int $userId): ?Mentor
    {
        return $this->model->where('user_id', $userId)->with('user')->first();
    }
    
    /**
     * @inheritDoc
     */
    public function findBySpeciality(string $speciality): Collection
    {
        return $this->model->where('speciality', $speciality)->with('user')->get();
    }
    
    /**
     * @inheritDoc
     */
    public function getTopMentors(int $limit = 10): Collection
    {
        return $this->model->withCount('courses')
            ->orderBy('courses_count', 'desc')
            ->limit($limit)
            ->with('user')
            ->get();
    }
}