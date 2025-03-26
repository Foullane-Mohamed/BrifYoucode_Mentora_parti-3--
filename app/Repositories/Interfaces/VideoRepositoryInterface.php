<?php

namespace App\Repositories\Interfaces;

use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;

interface VideoRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get videos by course ID.
     *
     * @param int $courseId
     * @return Collection
     */
    public function getByCourseId(int $courseId): Collection;

    /**
     * Get free preview videos by course ID.
     *
     * @param int $courseId
     * @return Collection
     */
    public function getFreePreviewsByCourseId(int $courseId): Collection;

    /**
     * Reorder videos for a course.
     *
     * @param int $courseId
     * @param array $videoOrders
     * @return Collection
     */
    public function reorderVideos(int $courseId, array $videoOrders): Collection;
}