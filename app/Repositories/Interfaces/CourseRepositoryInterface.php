<?php

namespace App\Repositories\Interfaces;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CourseRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get course by slug.
     *
     * @param string $slug
     * @return Course|null
     */
    public function findBySlug(string $slug): ?Course;

    /**
     * Get courses by mentor ID.
     *
     * @param int $mentorId
     * @return Collection
     */
    public function getByMentorId(int $mentorId): Collection;

    /**
     * Get courses by category ID.
     *
     * @param int $categoryId
     * @return Collection
     */
    public function getByCategoryId(int $categoryId): Collection;

    /**
     * Get courses by subcategory ID.
     *
     * @param int $subcategoryId
     * @return Collection
     */
    public function getBySubcategoryId(int $subcategoryId): Collection;

    /**
     * Get courses by difficulty level.
     *
     * @param string $difficulty
     * @return Collection
     */
    public function getByDifficulty(string $difficulty): Collection;

    /**
     * Get courses by status.
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get free courses.
     *
     * @return Collection
     */
    public function getFreeCourses(): Collection;

    /**
     * Search courses by title or description.
     *
     * @param string $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $query, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get courses with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function filter(array $filters, int $perPage = 10): LengthAwarePaginator;

    /**
     * Attach tags to a course.
     *
     * @param int $courseId
     * @param array $tagIds
     * @return Course
     */
    public function attachTags(int $courseId, array $tagIds): Course;

    /**
     * Detach tags from a course.
     *
     * @param int $courseId
     * @param array $tagIds
     * @return Course
     */
    public function detachTags(int $courseId, array $tagIds): Course;

    /**
     * Sync tags for a course.
     *
     * @param int $courseId
     * @param array $tagIds
     * @return Course
     */
    public function syncTags(int $courseId, array $tagIds): Course;
}