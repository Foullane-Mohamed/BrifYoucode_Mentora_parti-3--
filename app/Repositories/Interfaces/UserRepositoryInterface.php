<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get all users by role.
     *
     * @param string $role
     * @return Collection
     */
    public function findByRole(string $role): Collection;
}