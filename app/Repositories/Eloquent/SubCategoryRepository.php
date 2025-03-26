<?php

namespace App\Repositories\Eloquent;

use App\Models\SubCategory;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SubCategoryRepository extends BaseRepository implements SubCategoryRepositoryInterface
{
    /**
     * SubCategoryRepository constructor.
     *
     * @param SubCategory $model
     */
    public function __construct(SubCategory $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByCategoryId(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    /**
     * @inheritDoc
     */
    public function findBySlug(string $slug): ?SubCategory
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }
}