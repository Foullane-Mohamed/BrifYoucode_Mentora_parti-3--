<?php

namespace App\Repositories\Interfaces;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Collection;

interface BadgeRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get badges by type.
     *
     * @param string $type
     * @return Collection
     */
    public function getByType(string $type): Collection;

    /**
     * Award badge to student.
     *
     * @param int $badgeId
     * @param int $studentId
     * @return bool
     */
    public function awardToStudent(int $badgeId, int $studentId): bool;

    /**
     * Award badge to mentor.
     *
     * @param int $badgeId
     * @param int $mentorId
     * @return bool
     */
    public function awardToMentor(int $badgeId, int $mentorId): bool;

    /**
     * Remove badge from student.
     *
     * @param int $badgeId
     * @param int $studentId
     * @return bool
     */
    public function removeFromStudent(int $badgeId, int $studentId): bool;

    /**
     * Remove badge from mentor.
     *
     * @param int $badgeId
     * @param int $mentorId
     * @return bool
     */
    public function removeFromMentor(int $badgeId, int $mentorId): bool;

    /**
     * Check if student has badge.
     *
     * @param int $badgeId
     * @param int $studentId
     * @return bool
     */
    public function studentHasBadge(int $badgeId, int $studentId): bool;

    /**
     * Check if mentor has badge.
     *
     * @param int $badgeId
     * @param int $mentorId
     * @return bool
     */
    public function mentorHasBadge(int $badgeId, int $mentorId): bool;
}