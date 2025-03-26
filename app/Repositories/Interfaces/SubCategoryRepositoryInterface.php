<?php

namespace App\Repositories\Interfaces;

use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Collection;

interface SubCategoryRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get subcategories by category ID.
     *
     * @param int $categoryId
     * @return Collection
     */
    public function getByCategoryId(int $categoryId): Collection;

    /**
     * Get subcategory by slug.
     *
     * @param string $slug
     * @return SubCategory|null
     */
    public function findBySlug(string $slug): ?SubCategory;
}