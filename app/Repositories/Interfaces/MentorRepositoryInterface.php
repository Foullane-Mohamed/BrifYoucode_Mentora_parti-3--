<?php

namespace App\Repositories\Interfaces;

use App\Models\Mentor;
use Illuminate\Database\Eloquent\Collection;

interface MentorRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get mentor by user ID.
     *
     * @param int $userId
     * @return Mentor|null
     */
    public function findByUserId(int $userId): ?Mentor;
    
    /**
     * Get mentors by speciality.
     *
     * @param string $speciality
     * @return Collection
     */
    public function findBySpeciality(string $speciality): Collection;
    
    /**
     * Get top mentors by number of students.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopMentors(int $limit = 10): Collection;
}