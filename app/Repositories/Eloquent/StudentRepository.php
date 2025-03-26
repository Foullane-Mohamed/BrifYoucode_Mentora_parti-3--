<?php

namespace App\Repositories\Eloquent;

use App\Models\Student;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository extends BaseRepository implements StudentRepositoryInterface
{
    /**
     * StudentRepository constructor.
     *
     * @param Student $model
     */
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }
    
    /**
     * @inheritDoc
     */
    public function findByUserId(int $userId): ?Student
    {
        return $this->model->where('user_id', $userId)->with('user')->first();
    }
    
    /**
     * @inheritDoc
     */
    public function findByLevel(string $level): Collection
    {
        return $this->model->where('level', $level)->with('user')->get();
    }
    
    /**
     * @inheritDoc
     */
    public function getTopStudents(int $limit = 10): Collection
    {
        return $this->model->orderBy('badge_count', 'desc')
            ->limit($limit)
            ->with('user')
            ->get();
    }
}