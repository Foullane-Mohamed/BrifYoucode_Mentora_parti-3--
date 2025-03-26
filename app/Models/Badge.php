<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Badge extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image_path',
        'description',
        'type',
        'requirements',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requirements' => 'array',
    ];

    /**
     * The students that belong to the badge.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * The mentors that belong to the badge.
     */
    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(Mentor::class)
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}