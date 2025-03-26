<?php

namespace App\Repositories\Eloquent;

use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    /**
     * CourseRepository constructor.
     *
     * @param Course $model
     */
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findBySlug(string $slug): ?Course
    {
        return $this->model->where('slug', $slug)
            ->with(['mentor.user', 'category', 'subcategory', 'tags'])
            ->firstOrFail();
    }

    /**
     * @inheritDoc
     */
    public function getByMentorId(int $mentorId): Collection
    {
        return $this->model->where('mentor_id', $mentorId)
            ->with(['category', 'subcategory', 'tags'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByCategoryId(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)
            ->with(['mentor.user', 'subcategory', 'tags'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getBySubcategoryId(int $subcategoryId): Collection
    {
        return $this->model->where('sub_category_id', $subcategoryId)
            ->with(['mentor.user', 'category', 'tags'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByDifficulty(string $difficulty): Collection
    {
        return $this->model->where('difficulty', $difficulty)
            ->with(['mentor.user', 'category', 'subcategory', 'tags'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['mentor.user', 'category', 'subcategory', 'tags'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getFreeCourses(): Collection
    {
        return $this->model->where('is_free', true)
            ->with(['mentor.user', 'category', 'subcategory', 'tags'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function search(string $query, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with(['mentor.user', 'category', 'subcategory', 'tags'])
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function filter(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query();
        
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        
        if (isset($filters['sub_category_id'])) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }
        
        if (isset($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['is_free'])) {
            $query->where('is_free', $filters['is_free']);
        }
        
        if (isset($filters['mentor_id'])) {
            $query->where('mentor_id', $filters['mentor_id']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }
        
        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }
        
        return $query->with(['mentor.user', 'category', 'subcategory', 'tags'])
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function attachTags(int $courseId, array $tagIds): Course
    {
        $course = $this->findById($courseId);
        $course->tags()->attach($tagIds);
        
        return $course->fresh(['tags']);
    }

    /**
     * @inheritDoc
     */
    public function detachTags(int $courseId, array $tagIds): Course
    {
        $course = $this->findById($courseId);
        $course->tags()->detach($tagIds);
        
        return $course->fresh(['tags']);
    }

    /**
     * @inheritDoc
     */
    public function syncTags(int $courseId, array $tagIds): Course
    {
        $course = $this->findById($courseId);
        $course->tags()->sync($tagIds);
        
        return $course->fresh(['tags']);
    }
}