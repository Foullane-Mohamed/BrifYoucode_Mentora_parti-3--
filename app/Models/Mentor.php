<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mentor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'speciality',
        'description',
        'experience_level',
        'skills',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'skills' => 'array',
    ];

    /**
     * Get the user that owns the mentor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the courses for the mentor.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function badges(): BelongsToMany
{
    return $this->belongsToMany(Badge::class)
        ->withPivot('earned_at')
        ->withTimestamps();
}
}