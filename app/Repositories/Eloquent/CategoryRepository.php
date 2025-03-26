<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    /**
     * CategoryRepository constructor.
     *
     * @param Category $model
     */
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getAllWithSubcategories(): Collection
    {
        return $this->model->with('subcategories')->get();
    }

    /**
     * @inheritDoc
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }
}