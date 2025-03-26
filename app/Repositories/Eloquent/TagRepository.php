<?php

namespace App\Repositories\Eloquent;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    /**
     * TagRepository constructor.
     *
     * @param Tag $model
     */
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findBySlug(string $slug): ?Tag
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    /**
     * @inheritDoc
     */
    public function findByNameLike(string $name): Collection
    {
        return $this->model->where('name', 'like', "%{$name}%")->get();
    }
}