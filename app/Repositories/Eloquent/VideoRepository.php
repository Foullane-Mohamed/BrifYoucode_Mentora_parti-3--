<?php

namespace App\Repositories\Eloquent;

use App\Models\Video;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VideoRepository extends BaseRepository implements VideoRepositoryInterface
{
    /**
     * VideoRepository constructor.
     *
     * @param Video $model
     */
    public function __construct(Video $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByCourseId(int $courseId): Collection
    {
        return $this->model->where('course_id', $courseId)
            ->orderBy('order')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getFreePreviewsByCourseId(int $courseId): Collection
    {
        return $this->model->where('course_id', $courseId)
            ->where('is_free_preview', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function reorderVideos(int $courseId, array $videoOrders): Collection
    {
        foreach ($videoOrders as $videoId => $order) {
            $this->model->where('id', $videoId)
                ->where('course_id', $courseId)
                ->update(['order' => $order]);
        }
        
        return $this->getByCourseId($courseId);
    }
}