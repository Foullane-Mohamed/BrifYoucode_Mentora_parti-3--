<?php

namespace App\Repositories\Interfaces;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get tag by slug.
     *
     * @param string $slug
     * @return Tag|null
     */
    public function findBySlug(string $slug): ?Tag;

    /**
     * Get tags by name like.
     *
     * @param string $name
     * @return Collection
     */
    public function findByNameLike(string $name): Collection;
}