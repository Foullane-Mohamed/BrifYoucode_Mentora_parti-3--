<?php

namespace App\Repositories\Interfaces;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get student by user ID.
     *
     * @param int $userId
     * @return Student|null
     */
    public function findByUserId(int $userId): ?Student;
    
    /**
     * Get students by level.
     *
     * @param string $level
     * @return Collection
     */
    public function findByLevel(string $level): Collection;
    
    /**
     * Get students with most badges.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopStudents(int $limit = 10): Collection;
}