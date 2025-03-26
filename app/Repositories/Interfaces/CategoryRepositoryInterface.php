<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get all categories with subcategories.
     *
     * @return Collection
     */
    public function getAllWithSubcategories(): Collection;

    /**
     * Get category by slug.
     *
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;
}