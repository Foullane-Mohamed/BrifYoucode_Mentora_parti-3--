<?php

namespace App\Repositories\Interfaces;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;

interface EnrollmentRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get enrollments by course ID.
     *
     * @param int $courseId
     * @return Collection
     */
    public function getByCourseId(int $courseId): Collection;

    /**
     * Get enrollments by student ID.
     *
     * @param int $studentId
     * @return Collection
     */
    public function getByStudentId(int $studentId): Collection;

    /**
     * Get enrollments by status.
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Check if a student is enrolled in a course.
     *
     * @param int $studentId
     * @param int $courseId
     * @return bool
     */
    public function isEnrolled(int $studentId, int $courseId): bool;

    /**
     * Update enrollment progress.
     *
     * @param int $enrollmentId
     * @param int $progress
     * @param int|null $lastWatchedVideoId
     * @return Enrollment
     */
    public function updateProgress(int $enrollmentId, int $progress, ?int $lastWatchedVideoId = null): Enrollment;

    /**
     * Complete an enrollment.
     *
     * @param int $enrollmentId
     * @return Enrollment
     */
    public function complete(int $enrollmentId): Enrollment;
}